<?php

namespace Tests\Feature;

use App\Support\Ojk\DrpPinjamanDiberikan;
use App\Utils\ExcelExporter;
use Illuminate\Support\Facades\Session;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Tests\TestCase;

/**
 * End-to-end laporan DRP (Formulir 05.02): service -> view -> ExcelExporter.
 *
 * Memverifikasi dua hal yang paling rawan rusak diam-diam:
 *   1. kolom d (Nomor Identitas) tetap string 16 digit di .xlsx;
 *   2. kolom nominal (j, k, l, o) tetap numeric dengan format 1,234,567.89
 *      supaya bisa dijumlahkan di Excel, bukan teks.
 */
class DrpExportTest extends TestCase
{
    private const LOKASI = 109;

    private const TGL = '2026-12-31';

    private ?Worksheet $sheet = null;

    private ?array $hasil = null;

    private function renderView(): string
    {
        $kec = \App\Models\Kecamatan::find(self::LOKASI);

        $hasil = $this->hasil();

        return view('pelaporan.view.ojk.daftar_rincian_pinjaman_diberikan', [
            'rows' => $hasil['rows'],
            'gap' => $hasil['gap'],
            'total' => $hasil['total'],
            'sub_judul' => 'Periode Desember 2026',
            'tgl' => '31 Desember 2026',
            'tanggal_kondisi' => 'Klirong, 31 Desember 2026',
            'kec' => $kec,
            'laporan' => 'Pinjaman Aktif',
            'lkm' => \App\Models\Lkm::where('lokasi', self::LOKASI)->first(),
            'logo' => $kec->logo,
            'nama_lembaga' => $kec->nama_lembaga_sort,
            'nama_kecamatan' => $kec->sebutan_kec.' '.$kec->nama_kec,
            'nama_kabupaten' => $kec->kabupaten->sebutan_kab.' '.$kec->kabupaten->nama_kab,
            'nomor_usaha' => 'SK Kemenkumham RI No.'.$kec->nomor_bh,
            'info' => $kec->alamat_kec.', Telp.'.$kec->telpon_kec,
            'email' => $kec->email_kec,
            'tahun' => 2026,
            'bulan' => 12,
            'hari' => 31,
            'tgl_kondisi' => self::TGL,
            'bulanan' => true,
            'harian' => true,
            'type' => 'excel',
        ])->render();
    }

    /**
     * Hasil build service, di-cache supaya tidak query ulang berkali-kali.
     */
    private function hasil(): array
    {
        if ($this->hasil === null) {
            Session::put('lokasi', self::LOKASI);
            $this->hasil = (new DrpPinjamanDiberikan(self::LOKASI))->build(self::TGL);
        }

        return $this->hasil;
    }

    private function sheet(): Worksheet
    {
        if ($this->sheet === null) {
            // Dibaca dari object PhpSpreadsheet, bukan dari file .xlsx yang
            // sudah disimpan: kolom bertwrap hanya mengubah tampilan, dan
            // memuat ulang file membuat label header tampak terpotong
            // ("Jenis Penggu- naan") sehingga pencocokan label gagal.
            $this->sheet = (new ExcelExporter)
                ->fromHtml($this->renderView())
                ->setShowGridlines(false)
                ->getSpreadsheet()
                ->getActiveSheet();
        }

        return $this->sheet;
    }

    /**
     * Struktur tabel a-o pada sheet.
     *
     * Mengembalikan posisi header (baris + indeks kolom tiap label) dan
     * baris data pertama. Offset dihitung dari posisi sebenarnya, bukan
     * dari asumsi jumlah baris — sub-header "Mulai / Jatuh Tempo"
     * menambah satu baris.
     *
     * @return array{row:int, kolom:array<string,int>, dataMulai:int}
     */
    private function struktur(): array
    {
        $sheet = $this->sheet();

        for ($r = 1; $r <= 15; $r++) {
            $ada = false;
            for ($c = 1; $c <= 20; $c++) {
                if (stripos((string) $sheet->getCellByColumnAndRow($c, $r)->getValue(), 'Nomor Identitas') !== false) {
                    $ada = true;
                    break;
                }
            }

            if (! $ada) {
                continue;
            }

            // Baris data pertama: baris setelah sub-header. Cari lewat kolom
            // "No" yang berisi angka urut pertama (1).
            $colNo = null;
            for ($c = 1; $c <= 20; $c++) {
                if (strcasecmp(trim((string) $sheet->getCellByColumnAndRow($c, $r)->getValue()), 'No') === 0) {
                    $colNo = $c;
                    break;
                }
            }
            $this->assertNotNull($colNo, 'kolom No tidak ditemukan');

            $dataMulai = null;
            for ($rr = $r + 1; $rr <= $r + 6; $rr++) {
                if ((float) $sheet->getCellByColumnAndRow($colNo, $rr)->getValue() === 1.0) {
                    $dataMulai = $rr;
                    break;
                }
            }
            $this->assertNotNull($dataMulai, 'baris data pertama tidak ditemukan');

            // Kumpulkan label header, termasuk sub-header di baris berikutnya.
            $kolom = [];
            for ($rr = $r; $rr < $dataMulai; $rr++) {
                for ($c = 1; $c <= 20; $c++) {
                    $v = trim((string) $sheet->getCellByColumnAndRow($c, $rr)->getValue());
                    if ($v !== '' && ! isset($kolom[$v])) {
                        $kolom[$v] = $c;
                    }
                }
            }

            return ['row' => $r, 'kolom' => $kolom, 'dataMulai' => $dataMulai];
        }

        $this->fail('header "Nomor Identitas Nasabah" tidak ditemukan di sheet');
    }

    private function kolom(string $label): int
    {
        $struktur = $this->struktur();
        $this->assertArrayHasKey($label, $struktur['kolom'], "kolom '$label' tidak ada di header");

        return $struktur['kolom'][$label];
    }

    /**
     * Baris Excel untuk baris data dengan nomor urut tertentu.
     */
    private function barisData(int $no): int
    {
        return $this->struktur()['dataMulai'] + $no - 1;
    }

    public function test_header_memuat_urutan_kolom_a_sampai_o(): void
    {
        $this->sheet();
        $header = $this->struktur();

        foreach ([
            'No',
            'Nama Nasabah Penerima / LOAN ID',
            'Jenis Nasabah',
            'Nomor Identitas Nasabah',
            'Jenis Penggunaan',
            'Sektor Usaha',
            'Periode Pembayaran',
            'Jangka Waktu',
            'Suku Bunga',
            'Nilai Pencairan',
            'Saldo Pinjaman (Baki Debet)',
            'Tunggakan',
            'Kualitas',
            'Jenis Agunan',
            'Nilai Agunan',
        ] as $label) {
            $this->assertArrayHasKey($label, $header['kolom'], "header '$label' wajib ada (Formulir 05.02)");
        }

        // Flat list: "Jangka Waktu" tetap satu header dengan dua sub-kolom
        // (Mulai / Jatuh Tempo) di baris kedua, jadi total kolom 16.
        $mulai = $this->kolom('Mulai');
        $jatuhTempo = $this->kolom('Jatuh Tempo');

        $this->assertLessThan($jatuhTempo, $mulai, 'kolom h: Mulai harus mendahului Jatuh Tempo');
    }

    public function test_nomor_identitas_tetap_string_16_digit(): void
    {
        $this->sheet();
        $colNik = $this->kolom('Nomor Identitas Nasabah');

        $hasil = $this->hasil();
        $rows = $hasil['rows'];

        $this->assertGreaterThan(0, $rows->count(), 'tidak ada baris untuk diuji');

        foreach ($rows as $row) {
            $cell = $this->sheet()->getCellByColumnAndRow($colNik, $this->barisData($row['no']));

            $this->assertSame(
                $row['nomor_identitas'],
                (string) $cell->getValue(),
                "kolom d baris {$row['no']} berubah saat diekspor"
            );
            $this->assertSame(
                DataType::TYPE_STRING,
                $cell->getDataType(),
                "kolom d baris {$row['no']} harus string, bukan angka"
            );
            $this->assertSame(
                '@',
                $cell->getStyle()->getNumberFormat()->getFormatCode(),
                "kolom d baris {$row['no']} harus berformat teks"
            );
        }
    }

    public function test_nik_dengan_digit_ke_enam_belas_nonzero_tidak_dibulatkan(): void
    {
        $hasil = $this->hasil();

        // Ambil NIK yang digit terakhirnya bukan 0 — kasus yang paling
        // jelas salah kalau dipaksa jadi float.
        $kandidat = $hasil['rows']->filter(function ($row) {
            $nik = (string) $row['nomor_identitas'];

            return strlen($nik) === 16 && substr($nik, 15, 1) !== '0';
        });

        if ($kandidat->isEmpty()) {
            $this->markTestSkipped('tidak ada NIK 16 digit dengan digit terakhir non-zero');
        }

        $this->sheet();
        $colNik = $this->kolom('Nomor Identitas Nasabah');

        foreach ($kandidat as $row) {
            $nilai = (string) $this->sheet()
                ->getCellByColumnAndRow($colNik, $this->barisData($row['no']))
                ->getValue();

            $this->assertSame((string) $row['nomor_identitas'], $nilai);

            // Reproduksi pembulatan IEEE 754: 16 digit yang ditulis sebagai
            // float kehilangan digit terakhirnya. Ini yang harus dicegah.
            $sebagaiFloat = (string) (float) $nilai;

            $this->assertNotSame(
                $sebagaiFloat,
                $nilai,
                "NIK baris {$row['no']} terbulatkan menjadi float"
            );
            $this->assertSame(
                (string) $row['nomor_identitas'],
                $nilai,
                "NIK baris {$row['no']} kehilangan digit ke-16"
            );
            $this->assertSame(
                substr((string) $row['nomor_identitas'], 15, 1),
                substr($nilai, 15, 1),
                'digit ke-16 harus utuh, bukan 0'
            );
        }
    }

    public function test_kolom_nominal_tetap_numeric_dengan_format_uang(): void
    {
        $this->sheet();

        $pasangan = [
            'Nilai Pencairan' => 'nilai_pencairan',
            'Saldo Pinjaman (Baki Debet)' => 'baki_debet',
            'Tunggakan' => 'tunggakan',
        ];

        $hasil = $this->hasil();
        $rows = $hasil['rows'];

        $dihitung = 0;

        foreach ($pasangan as $label => $key) {
            $col = $this->kolom($label);

            foreach ($rows as $row) {
                $cell = $this->sheet()->getCellByColumnAndRow($col, $this->barisData($row['no']));

                if ($row[$key] === null || $row[$key] === 0.0) {
                    continue;
                }

                $this->assertSame(
                    DataType::TYPE_NUMERIC,
                    $cell->getDataType(),
                    "kolom '$label' baris {$row['no']} harus numeric supaya bisa dijumlahkan"
                );
                $this->assertSame(
                    '#,##0.00',
                    $cell->getStyle()->getNumberFormat()->getFormatCode(),
                    "kolom '$label' baris {$row['no']} harus format 1,234,567.89"
                );
                $this->assertEqualsWithDelta(
                    $row[$key],
                    (float) $cell->getValue(),
                    0.01,
                    "kolom '$label' baris {$row['no']} nilainya berbeda"
                );
                $dihitung++;
            }
        }

        $this->assertGreaterThan(
            0,
            $dihitung,
            'tidak ada sel nominal yang diperiksa'
        );
    }

    public function test_kolom_nilai_agunan_kosong_tidak_menjadi_nol(): void
    {
        $this->sheet();
        $col = $this->kolom('Nilai Agunan');

        $hasil = $this->hasil();

        foreach ($hasil['rows'] as $row) {
            if ($row['nilai_agunan'] !== null) {
                continue;
            }

            $cell = $this->sheet()->getCellByColumnAndRow($col, $this->barisData($row['no']));

            $this->assertNotSame(
                DataType::TYPE_NUMERIC,
                $cell->getDataType(),
                "kolom o baris {$row['no']} tidak boleh jadi angka 0 saat agunan kosong"
            );
            $this->assertSame(
                '',
                (string) $cell->getValue(),
                "kolom o baris {$row['no']} harus kosong"
            );
        }
    }

    public function test_baris_tidak_mengelompokkan_desa_maupun_produk(): void
    {
        // Flat list: tidak ada baris judul desa/produk di antara baris data.
        // Semua baris data adalah anggota dengan nama dan identitas.
        $this->sheet();
        $struktur = $this->struktur();
        $colNama = $this->kolom('Nama Nasabah Penerima / LOAN ID');

        $hasil = $this->hasil();
        $jumlahBaris = $hasil['rows']->count();

        foreach ($hasil['rows'] as $row) {
            $sel = trim((string) $this->sheet()
                ->getCellByColumnAndRow($colNama, $this->barisData($row['no']))
                ->getValue());

            // Kolom b = "NAMA - ID" (dua bagian, CIF tidak ikut).
            $this->assertSame(
                $row['nama'].' - '.$row['loan_id'],
                $sel,
                "kolom b baris {$row['no']} tidak sesuai"
            );
            $this->assertStringContainsString($row['nama'], $sel);
            $this->assertStringContainsString($row['loan_id'], $sel);
            $this->assertSame(
                mb_strtoupper($row['nama']),
                $row['nama'],
                'kolom b harus KAPITAL'
            );
        }
    }

    /**
     * Loan ID & CIF berada di dalam kolom b, sehingga kolom itu WAJIB teks —
     * kalau dibiarkan numeric, nilai seperti 1666 atau NIK-like bisa
     * kehilangan digitnya.
     */
    public function test_kolom_nama_loan_id_cif_tetap_teks(): void
    {
        $this->sheet();
        $colNama = $this->kolom('Nama Nasabah Penerima / LOAN ID');
        $hasil = $this->hasil();

        foreach ($hasil['rows'] as $row) {
            $cell = $this->sheet()->getCellByColumnAndRow($colNama, $this->barisData($row['no']));

            $this->assertSame(
                DataType::TYPE_STRING,
                $cell->getDataType(),
                "kolom b baris {$row['no']} harus string"
            );
            $this->assertSame(
                '@',
                $cell->getStyle()->getNumberFormat()->getFormatCode(),
                "kolom b baris {$row['no']} harus berformat teks"
            );
        }
    }

    public function test_total_sejumlah_dengan_baris_data(): void
    {
        $this->sheet();
        $struktur = $this->struktur();

        $hasil = $this->hasil();

        // Baris total = tepat setelah baris data terakhir.
        $rTotal = $struktur['dataMulai'] + $hasil['rows']->count();

        // ExcelExporter membaca sel sebagai teks apa adanya, jadi total
        // berbenturan dengan format kolom. Bandingkan setelah normalisasi.
        $sel = (string) $this->sheet()->getCellByColumnAndRow(
            $this->kolom('Saldo Pinjaman (Baki Debet)'),
            $rTotal
        )->getValue();

        $angka = (float) str_replace(',', '', $sel);

        $this->assertGreaterThan(
            0,
            $angka,
            "baris total kosong — cek posisi baris ($rTotal)"
        );
        $this->assertEqualsWithDelta(
            $hasil['total']['baki_debet'],
            $angka,
            0.01,
            'baris total harus sama dengan jumlah baki debet'
        );
    }

    public function test_data_gap_warning_tertampil_bila_ada_kolom_kosong(): void
    {
        $html = $this->renderView();
        $hasil = $this->hasil();

        if ($hasil['gap']['total'] === 0) {
            $this->assertStringNotContainsString('DATA GAP WARNING', $html);
            $this->markTestSkipped('tidak ada data gap pada laporan ini');
        }

        $this->assertStringContainsString('DATA GAP WARNING', $html);
        $this->assertStringContainsString((string) $hasil['gap']['total'], $html);
    }
}
