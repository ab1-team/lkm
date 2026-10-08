<?php

namespace App\Support\Ojk;

use App\Models\JenisProdukPinjaman;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Query + shaping laporan "Daftar Rincian Pinjaman yang Diberikan"
 * (SEOJK No. 1/SEOJK.06/2025 — Formulir 05.02).
 *
 * Dipakai oleh method `pinjaman_diberi` di PelaporanController, yang
 * terdaftar pada sub_laporan.id=114 dalam group "Laporan SEOJK Nomor
 * 1/SEOJK.06/2025" (jenis_laporan.id=34, file=21).
 *
 * Menghasilkan FLAT LIST baris kolom a-o, tanpa pengelompokan desa dan
 * tanpa pengelompokan produk pinjaman.
 *
 * Multi-tenant: seluruh query dan join diisolasi per lokasi (tenant). Di
 * skema legacy LKM, isolasi ini berupa sharding per nama tabel
 * (pinjaman_anggota_{lokasi}, anggota_{lokasi}, real_angsuran_i_{lokasi},
 * rencana_angsuran_i_{lokasi}) plus filter lokasi pada tabel global
 * jenis_produk_pinjaman.
 */
final class DrpPinjamanDiberikan
{
    /**
     * Status yang masih aktif pada tanggal laporan: A (aktif) plus
     * L/R/H yang tgl_lunas masih setelah tanggal laporan.
     */
    private const STATUS_LUNAS = ['L', 'R', 'H'];

    private int $lokasi;

    /** @var array<int, array{jenis:int, usaha:int, nama:string}>|null */
    private ?array $produkCache = null;

    public function __construct(int $lokasi)
    {
        $this->lokasi = $lokasi;
    }

    /**
     * @return array{rows: Collection<int, array<string, mixed>>, gap: array<string, mixed>, total: array<string, float|int>}
     */
    public function build(string $tglLaporan): array
    {
        $baris = $this->ambilPinjaman($tglLaporan);
        $sistemAngsuran = $this->petaSistemAngsuran();

        // Preatasan per-kredit dihitung dalam beberapa query, bukan satu query
        // per baris. Database LKM ini sharded per lokasi dan sering diakses
        // lewat jaringan, jadi N+1 di sini membuat laporan tidak usable.
        $cache = $this->bangunCacheAngsuran($baris, $tglLaporan);

        $rows = collect();
        $gapKolom = [];
        $nomor = 0;

        foreach ($baris as $p) {
            $nomor++;

            $row = $this->bangunBaris($p, $nomor, $sistemAngsuran, $tglLaporan, $cache);

            foreach ($row['gap'] as $kolom => $alasan) {
                $gapKolom[$kolom] = ($gapKolom[$kolom] ?? 0) + 1;
            }

            $rows->push($row);
        }

        return [
            'rows' => $rows,
            'gap' => $this->ringkasGap($gapKolom, $rows),
            'total' => [
                'nilai_pencairan' => (float) $rows->sum('nilai_pencairan'),
                'baki_debet' => (float) $rows->sum('baki_debet'),
                'tunggakan' => (float) $rows->sum('tunggakan'),
                'nilai_agunan' => (float) $rows->sum(fn ($r) => $r['nilai_agunan'] ?? 0),
                'jumlah_baris' => $rows->count(),
            ],
        ];
    }

    /**
     * Query inti. Semua tabel borrower di-scope ke $this->lokasi lewat nama
     * tabel; tidak ada tabel global yang ikut di-join tanpa batas lokasi.
     */
    private function ambilPinjaman(string $tglLaporan): Collection
    {
        // Beberapa LKM belum punya tabel pinjaman sama sekali. Lokasi
        // seperti itu berarti "tidak ada pinjaman" — return kosong supaya
        // seluruh lokasi tetap bisa dirender tanpa try/catch di controller.
        foreach (['pinjaman_anggota_', 'anggota_', 'rencana_angsuran_i_', 'real_angsuran_i_'] as $prefix) {
            if (! Schema::hasTable($this->tabel($prefix))) {
                return collect();
            }
        }

        $tbPinj = $this->tabel('pinjaman_anggota_');
        $tbAgg = $this->tabel('anggota_');

        $produkId = array_keys($this->produk());

        if ($produkId === []) {
            return collect();
        }

        return DB::table($tbPinj)
            ->select([
                $tbPinj.'.id',
                // nia = nomor anggota, dipakai sebagai CIF pada kolom b.
                $tbPinj.'.nia',
                $tbPinj.'.jenis_pp',
                $tbPinj.'.status',
                $tbPinj.'.tgl_cair',
                $tbPinj.'.tgl_lunas',
                $tbPinj.'.alokasi',
                $tbPinj.'.pros_jasa',
                $tbPinj.'.jangka',
                $tbPinj.'.jaminan',
                $tbAgg.'.namadepan',
                $tbAgg.'.nik',
            ])
            ->join($tbAgg, $tbAgg.'.id', '=', $tbPinj.'.nia')
            ->whereIn($tbPinj.'.jenis_pp', $produkId)
            ->where($tbPinj.'.jenis_pinjaman', 'I')
            // Sudah dicairkan: tgl_cair terisi dan tidak di masa depan.
            ->whereNotNull($tbPinj.'.tgl_cair')
            ->where($tbPinj.'.tgl_cair', '<=', $tglLaporan)
            ->where(function ($query) use ($tbPinj, $tglLaporan) {
                $query->where($tbPinj.'.status', 'A')
                    ->orWhere(function ($q) use ($tbPinj, $tglLaporan) {
                        // Lunas / rescheduling / hapus tapi masih aktif di
                        // tanggal laporan (tgl_lunas di masa depan).
                        $q->whereIn($tbPinj.'.status', self::STATUS_LUNAS)
                            ->where($tbPinj.'.tgl_lunas', '>', $tglLaporan);
                    });
            })
            // Flat list: urut tanggal pencairan ascending, tie-breaker id.
            ->orderBy($tbPinj.'.tgl_cair', 'ASC')
            ->orderBy($tbPinj.'.id', 'ASC')
            ->get();
    }

    /**
     * Kumpulkan seluruh data angsuran yang dibutuhkan laporan dalam beberapa
     * query, dikelompokkan per loan_id.
     *
     * Tiga nilai per pinjaman:
     *   - saldo_pokok   : baris real_angsuran terakhir s/d tanggal laporan (kolom k)
     *   - target_pokok  : rencana angsuran TERAKHIR yang jatuh temponya sudah lewat (kolom l)
     *   - dpd           : turunan jumlah jatuh tempo vs jumlah angsuran terbayar (kolom m)
     *
     * @return array{
     *     saldo: array<string, float>,
     *     terbayarPokok: array<string, float>,
     *     target: array<string, float>,
     *     angsuranLewat: array<string, int>,
     *     angsuranTerbayar: array<string, int>,
     *     jatuhTempoTerakhir: array<string, string>
     * }
     */
    private function bangunCacheAngsuran(Collection $baris, string $tglLaporan): array
    {
        $kosong = [
            'saldo' => [],
            'terbayarPokok' => [],
            'target' => [],
            'angsuranLewat' => [],
            'angsuranTerbayar' => [],
            'jatuhTempoTerakhir' => [],
        ];

        if ($baris->isEmpty()) {
            return $kosong;
        }

        $idSudahLunas = [];

        foreach ($baris as $p) {
            if ($this->sudahLunas($p, $tglLaporan)) {
                $idSudahLunas[(string) $p->id] = true;
            }
        }

        $idAktif = $baris
            ->reject(fn ($p) => isset($idSudahLunas[(string) $p->id]))
            ->pluck('id')
            ->map(fn ($v) => (string) $v)
            ->all();

        if ($idAktif === []) {
            return $kosong;
        }

        // --- rencana angsuran: hanya baris yang jatuh temponya sudah lewat ---
        $rencana = DB::table($this->tabel('rencana_angsuran_i_'))
            ->select('loan_id', 'jatuh_tempo', 'angsuran_ke', 'target_pokok')
            ->whereIn('loan_id', $idAktif)
            ->where('jatuh_tempo', '<=', $tglLaporan)
            ->get();

        $target = [];
        $angsuranLewat = [];
        $jatuhTempoTerakhir = [];

        foreach ($rencana as $r) {
            $loanId = (string) $r->loan_id;

            // Jumlah angsuran yang sudah jatuh tempo.
            $angsuranLewat[$loanId] = ($angsuranLewat[$loanId] ?? 0) + 1;

            // target_pokok kumulatif: baris TERAKHIR yang menang, bukan dijumlahkan.
            $kunci = $r->jatuh_tempo.'|'.str_pad((string) $r->angsuran_ke, 4, '0', STR_PAD_LEFT);

            if (! isset($target[$loanId]) || $kunci > $target[$loanId]['kunci']) {
                $target[$loanId] = [
                    'kunci' => $kunci,
                    'nilai' => (float) $r->target_pokok,
                    'jatuh_tempo' => (string) $r->jatuh_tempo,
                ];
            }
        }

        $targetNilai = [];
        $jatuhTempo = [];

        foreach ($target as $loanId => $data) {
            $targetNilai[$loanId] = $data['nilai'];
            $jatuhTempo[$loanId] = $data['jatuh_tempo'];
        }

        // --- real angsuran ---
        // Dua hal dibutuhkan sekaligus:
        //   saldo_pokok : nilai transaksi TERAKHIR (baki debet kolom k)
        //   sum_pokok   : kumulasi pokok terbayar pada transaksi terakhir (kolom l)
        // Keduanya diambil dari baris yang sama agar konsisten.
        $real = DB::table($this->tabel('real_angsuran_i_'))
            ->select('loan_id', 'tgl_transaksi', 'saldo_pokok', 'sum_pokok', 'id')
            ->whereIn('loan_id', $idAktif)
            ->where('tgl_transaksi', '<=', $tglLaporan)
            ->orderBy('tgl_transaksi', 'DESC')
            ->orderBy('id', 'DESC')
            ->get();

        $saldo = [];
        $terbayarPokok = [];
        $terbayarTgl = [];
        $terbayar = [];

        foreach ($real as $r) {
            $loanId = (string) $r->loan_id;

            // Baris pertama per loan = transaksi terakhir.
            if (! isset($saldo[$loanId])) {
                $saldo[$loanId] = (float) $r->saldo_pokok;
                $terbayarPokok[$loanId] = (float) $r->sum_pokok;
            }

            // Pembayaran pokok dihitung per TANGGAL transaksi, bukan per baris:
            // satu angsuran bisa punya beberapa baris (pokok + jasa).
            $tglKey = $loanId.'|'.$r->tgl_transaksi;

            if (! isset($terbayarTgl[$tglKey]) && (float) $r->sum_pokok > 0) {
                $terbayarTgl[$tglKey] = true;
                $terbayar[$loanId] = ($terbayar[$loanId] ?? 0) + 1;
            }
        }

        return [
            'saldo' => $saldo,
            'terbayarPokok' => $terbayarPokok,
            'target' => $targetNilai,
            'angsuranLewat' => $angsuranLewat,
            'angsuranTerbayar' => $terbayar,
            'jatuhTempoTerakhir' => $jatuhTempo,
        ];
    }

    /**
     * @return array<int, object>
     */
    private function petaSistemAngsuran(): array
    {
        return \App\Models\SistemAngsuran::query()
            ->get(['id', 'jenis', 'sistem'])
            ->mapWithKeys(fn ($s) => [(int) $s->id => $s])
            ->all();
    }

    /**
     * Bentuk satu baris kolom a-o beserta daftar gap-nya.
     *
     * @return array<string, mixed>
     */
    private function bangunBaris(object $p, int $nomor, array $sistemAngsuran, string $tglLaporan, array $cache): array
    {
        $gap = [];

        // c — Jenis Nasabah: laporan ini hanya memuat jenis_pinjaman = 'I'.
        $jenisNasabah = OjkSandi::jenisNasabah(true);

        // d — Nomor identitas. WAJIB string supaya digit ke-16 tidak
        // dibulatkan Excel jadi 0 (lihat ExcelExporter::TEXT_COLUMNS).
        $identitas = $p->nik === null ? '' : trim((string) $p->nik);
        if ($identitas === '') {
            $gap['d_nomor_identitas'] = 'Nomor identitas nasabah kosong';
        }

        // e — Jenis penggunaan: nilai langsung dari jenis_produk_pinjaman.jenis.
        $jenisPenggunaan = $this->produkJenis($p->jenis_pp);

        // f — Sektor usaha: nilai langsung dari jenis_produk_pinjaman.usaha.
        $sektorUsaha = $this->produkUsaha($p->jenis_pp);

        if ($jenisPenggunaan === null || $sektorUsaha === null) {
            $gap['e_f_kode_produk'] = 'jenis_produk_pinjaman belum punya jenis/usaha';
        }

        // g — Periode pembayaran (hanya angka, tanpa label).
        $sa = $sistemAngsuran[(int) ($p->sistem_angsuran ?? 0)] ?? null;
        $periode = OjkSandi::periodePembayaran($sa->jenis ?? null, $sa->sistem ?? null);

        // h — Jangka waktu (mulai s/d jatuh tempo akhir).
        $tglCair = $p->tgl_cair ? (string) $p->tgl_cair : null;
        $jangkaWaktu = $this->hitungJatuhTempoAkhir($tglCair, (int) ($p->jangka ?? 0));
        if ($tglCair === null) {
            $gap['h_jangka_waktu'] = 'Tanggal pencairan kosong';
        }

        // i — Tingkat suku bunga (flat per bulan).
        $sukuBunga = $this->hitungSukuBunga($p->pros_jasa, (int) ($p->jangka ?? 0));

        // j / k / l — nominal.
        $nilaiPencairan = (float) $p->alokasi;
        $bakiDebet = $this->hitungBakiDebet($p, $tglLaporan, $cache);
        $tunggakan = $this->hitungTunggakan($p, $tglLaporan, $cache);

        // m — Kolektibilitas dari DPD.
        $dpd = $this->hitungDpd($p, $tglLaporan, $cache);
        $sandiKolek = OjkSandi::sandiKolektibilitas($dpd);
        if ($sandiKolek === null) {
            $gap['m_kolektibilitas'] = 'Sandi kolektibilitas belum ditetapkan';
        }

        // n & o — agunan. Kode diambil apa adanya dari JSON jaminan,
        // tanpa pemetaan ke tabel sandi OJK.
        $jaminan = json_decode((string) $p->jaminan, true);
        $jaminan = is_array($jaminan) ? $jaminan : [];

        $sandiAgunan = $this->kodeAgunan($jaminan);
        $nilaiAgunan = OjkSandi::nilaiAgunan((string) $p->jaminan);

        if ($sandiAgunan === null) {
            $gap['n_jenis_agunan'] = 'Data agunan kosong';
        }
        if ($nilaiAgunan === null) {
            $gap['o_nilai_agunan'] = 'Nilai agunan tidak tersimpan';
        }

        return [
            // a
            'no' => $nomor,
            // b — Nama Nasabah Penerima. Format 2 bagian: NAMA - ID.
            // ID = simpanan.id bila baris simpanan, pinjaman.id bila pinjaman.
            'nama' => mb_strtoupper((string) ($p->namadepan ?? '')),
            'nama_lengkap' => mb_strtoupper((string) ($p->namadepan ?? ''))
                .' - '.(string) $p->id,
            // c — hanya angka (1 = Individu, 2 = Kelompok).
            'jenis_nasabah' => $jenisNasabah,
            // d — string, bukan angka
            'nomor_identitas' => $identitas,
            // e
            'jenis_penggunaan' => $jenisPenggunaan,
            // f
            'sektor_usaha' => $sektorUsaha,
            // g — hanya angka sandi periode pembayaran.
            'periode_pembayaran' => $periode,
            // h
            'tgl_mulai' => $tglCair,
            'tgl_jatuh_tempo' => $jangkaWaktu,
            // i
            'suku_bunga' => $sukuBunga,
            // j
            'nilai_pencairan' => $nilaiPencairan,
            // k
            'baki_debet' => $bakiDebet,
            // l
            'tunggakan' => $tunggakan,
            'dpd' => $dpd,
            // m
            'kolektibilitas' => $sandiKolek,
            'kolektibilitas_label' => OjkSandi::labelKolektibilitas($dpd),
            // n
            'jenis_agunan' => $sandiAgunan,
            // o
            'nilai_agunan' => $nilaiAgunan,

            // internal, tidak dirender sebagai kolom
            'loan_id' => (string) $p->id,
            'nama_produk' => $this->namaProduk($p->jenis_pp),
            'gap' => $gap,
        ];
    }

    /**
     * Baki debet (kolom k) pada tanggal laporan.
     *
     * Sumber: baris terakhir real_angsuran_i_{lokasi} dengan
     * tgl_transaksi <= tanggal laporan, kolom saldo_pokok. Bila belum ada
     * transaksi sama sekali, baki debet = nilai pencairan.
     */
    private function hitungBakiDebet(object $p, string $tglLaporan, array $cache): float
    {
        if ($this->sudahLunas($p, $tglLaporan)) {
            return 0.0;
        }

        $loanId = (string) $p->id;

        if (! array_key_exists($loanId, $cache['saldo'])) {
            // Belum ada transaksi = belum ada pembayaran = masih penuh.
            return (float) $p->alokasi;
        }

        return max(0.0, (float) $cache['saldo'][$loanId]);
    }

    /**
     * Tunggakan pokok (kolom l) pada tanggal laporan.
     *
     * (target angsuran terakhir yang sudah jatuh tempo) dikurangi
     * (pokok yang sudah terbayar s/d tanggal laporan), positif.
     */
    private function hitungTunggakan(object $p, string $tglLaporan, array $cache): float
    {
        if ($this->sudahLunas($p, $tglLaporan)) {
            return 0.0;
        }

        $loanId = (string) $p->id;

        // target_pokok bersifat kumulatif per angsuran (angsuran ke-12 =
        // seluruh pokok s/d angsuran 12), jadi yang dipakai adalah nilai
        // baris TERAKHIR yang jatuh temponya sudah lewat — bukan penjumlahan
        // seluruh baris, yang akan menghitung pokok berkali-kali.
        $target = $cache['target'][$loanId] ?? null;

        if ($target === null) {
            return 0.0;
        }

        $terbayar = (float) ($cache['terbayarPokok'][$loanId] ?? 0);

        return max(0.0, $target - $terbayar);
    }

    /**
     * Days past due (hari) untuk kolom m.
     *
     * Mengikuti view seojk/kolekbilitas_pinjaman_41:
     *   DPD = (tgl_laporan - jatuh tempo angsuran terakhir yang lewat)
     *         + (jumlah angsuran tertunggak - 1) * 30
     */
    private function hitungDpd(object $p, string $tglLaporan, array $cache): int
    {
        if ($this->sudahLunas($p, $tglLaporan)) {
            return 0;
        }

        $loanId = (string) $p->id;
        $terlambat = $cache['jatuhTempoTerakhir'][$loanId] ?? null;

        if ($terlambat === null) {
            return 0;
        }

        // Berapa angsuran yang sudah jatuh tempo tapi belum dibayar.
        $jatuhTempo = (int) ($cache['angsuranLewat'][$loanId] ?? 0);
        $terbayar = (int) ($cache['angsuranTerbayar'][$loanId] ?? 0);
        $nunggak = max(0, $jatuhTempo - $terbayar);

        if ($nunggak <= 0) {
            return 0;
        }

        $selisihHari = (int) round(
            (strtotime($tglLaporan) - strtotime($terlambat)) / 86400
        );

        return max(0, $selisihHari + ($nunggak - 1) * 30);
    }

    /**
     * Nama tabel milik tenant ini. Semua akses tabel borrower/angsuran WAJIB
     * lewat sini supaya tidak ada yang lupa menambahkan lokasi.
     */
    private function tabel(string $prefix): string
    {
        return $prefix.$this->lokasi;
    }

    /**
     * Pinjaman sudah selesai sebelum/sama tanggal laporan sehingga baki
     * debet, tunggakan, dan DPD-nya semua nol.
     */
    private function sudahLunas(object $p, string $tglLaporan): bool
    {
        if ($p->status === 'A') {
            return false;
        }

        return $p->tgl_lunas !== null && (string) $p->tgl_lunas <= $tglLaporan;
    }

    /**
     * Kode jenis agunan dari JSON `jaminan`.
     *
     * Data agunan tidak seragam antar Forms. Yang dipakai berurutan:
     * jenis_jaminan, id_jaminan, id_agunan, jenis_agunan, id_jenis_jaminan.
     *
     * Nilai dikembalikan apa adanya sebagai angka — TIDAK dipetakan ke sandi
     * OJK, karena tabel sandinya belum tersedia.
     */
    private function kodeAgunan(array $jaminan): ?int
    {
        foreach ([
            'jenis_jaminan', 'id_jaminan', 'id_agunan',
            'jenis_agunan', 'id_jenis_jaminan',
        ] as $kunci) {
            if (! isset($jaminan[$kunci])) {
                continue;
            }

            $nilai = $jaminan[$kunci];

            if (is_numeric($nilai)) {
                return (int) $nilai;
            }
        }

        return null;
    }

    /**
     * Jatuh tempo akhir = tgl_cair + jangka (bulan).
     */
    private function hitungJatuhTempoAkhir(?string $tglCair, int $jangka): ?string
    {
        if ($tglCair === null || $jangka <= 0) {
            return $tglCair;
        }

        return date('Y-m-d', strtotime('+'.$jangka.' month', strtotime($tglCair)));
    }

    /**
     * Suku bunga flat per bulan dalam persen.
     *
     * Kolom pros_jasa pada tabel pinjaman menyimpan bunga flat total
     * selama jangka (contoh: pros_jasa=40, jangka=24 -> 1,67%/bulan).
     * Views lama membaginya dengan jangka — lihat view
     * pelaporan/view/ojk/pinjaman_diberi.blade.php versi lama.
     * jangka = 0 menghasilkan 0, bukan DivisionByZeroError.
     */
    private function hitungSukuBunga($prosJasa, int $jangka): float
    {
        $prosJasa = (float) $prosJasa;

        if ($jangka <= 0) {
            return 0.0;
        }

        return round($prosJasa / $jangka, 2);
    }

    /*
    |--------------------------------------------------------------------------
    | Produk pinjaman (jenis / usaha / nama) untuk kolom e, f, dan label
    |--------------------------------------------------------------------------
    */

    /**
     * @return array<int, array{jenis:int, usaha:int, nama:string}>
     */
    private function produk(): array
    {
        if ($this->produkCache !== null) {
            return $this->produkCache;
        }

        $this->produkCache = JenisProdukPinjaman::query()
            ->where(function ($query) {
                $query->where('lokasi', '0')
                    ->where('kecuali', 'NOT LIKE', '%#'.$this->lokasi.'#%');
            })
            ->orWhere(function ($query) {
                $query->where('lokasi', (string) $this->lokasi)
                    ->where('kecuali', 'NOT LIKE', '%#'.$this->lokasi.'#%');
            })
            ->get(['id', 'jenis', 'usaha', 'nama_jpp'])
            ->mapWithKeys(fn ($p) => [(int) $p->id => [
                'jenis' => (int) $p->jenis,
                'usaha' => (int) $p->usaha,
                'nama' => (string) $p->nama_jpp,
            ]])
            ->all();

        return $this->produkCache;
    }

    private function produkJenis($jenisPp): ?int
    {
        return $this->produk()[(int) $jenisPp]['jenis'] ?? null;
    }

    private function produkUsaha($jenisPp): ?int
    {
        return $this->produk()[(int) $jenisPp]['usaha'] ?? null;
    }

    private function namaProduk($jenisPp): string
    {
        return $this->produk()[(int) $jenisPp]['nama'] ?? '';
    }

    /*
    |--------------------------------------------------------------------------
    | Data Gap Warning
    |--------------------------------------------------------------------------
    */

    /**
     * @return array{total: int, per_kolom: array<string, int>, baris: array<int, array<string, mixed>>}
     */
    private function ringkasGap(array $perKolom, Collection $rows): array
    {
        $barisGap = $rows
            ->filter(fn ($r) => $r['gap'] !== [])
            ->map(fn ($r) => [
                'no' => $r['no'],
                'loan_id' => $r['loan_id'],
                'nama' => $r['nama'],
                'kolom' => array_keys($r['gap']),
                'alasan' => array_values($r['gap']),
            ])
            ->values()
            ->all();

        return [
            'total' => count($barisGap),
            'per_kolom' => $perKolom,
            'baris' => $barisGap,
        ];
    }
}
