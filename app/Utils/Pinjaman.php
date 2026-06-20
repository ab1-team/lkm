<?php

namespace App\Utils;

use DateTime;
use DateTimeZone;

class Pinjaman
{
    static protected $data;
    static protected $replacer;

    public function __construct()
    {
        self::$data = [];
        self::$replacer = [];
    }

    public static function keyword($text = false, $data = [], $individu = false)
    {
        if ($text === false) {
            return [
                [
                    'key' => '{kepala_lembaga}',
                    'des' => 'Menampilkan Sebutan Kepala Lembaga',
                    'group' => 'lembaga',
                ],
                [
                    'key' => '{kabag_administrasi}',
                    'des' => 'Menampilkan Sebutan Kabag Administrasi',
                    'group' => 'lembaga',
                ],
                [
                    'key' => '{kabag_keuangan}',
                    'des' => 'Menampilkan Sebutan Kabag Keuangan',
                    'group' => 'lembaga',
                ],
                [
                    'key' => '{verifikator}',
                    'des' => 'Menampilkan Nama Sebutan Verifikator',
                    'group' => 'lembaga',
                ],
                [
                    'key' => '{pengawas}',
                    'des' => 'Menampilkan Nama Sebutan Pengawas',
                    'group' => 'lembaga',
                ],
                [
                    'key' => '{ketua}',
                    'des' => 'Menampilkan Nama Ketua Kelompok',
                    'group' => 'lembaga',
                ],
                [
                    'key' => '{sekretaris}',
                    'des' => 'Menampilkan Nama Sekretaris Kelompok',
                    'group' => 'lembaga',
                ],
                [
                    'key' => '{bendahara}',
                    'des' => 'Menampilkan Nama Bendahara Kelompok',
                    'group' => 'lembaga',
                ],
                [
                    'key' => '{kades}',
                    'des' => 'Menampilkan Nama Kepala Desa/Lurah',
                    'group' => 'non_lembaga',
                ],
                [
                    'key' => '{pangkat}',
                    'des' => 'Menampilkan Pangkat Kepala Desa/Lurah',
                    'group' => 'non_lembaga',
                ],
                [
                    'key' => '{nip}',
                    'des' => 'Menampilkan Nip Kepala Desa/Lurah',
                    'group' => 'non_lembaga',
                ],
                [
                    'key' => '{sekdes}',
                    'des' => 'Menampilkan Nama Sekdes',
                    'group' => 'non_lembaga',
                ],
                [
                    'key' => '{ked}',
                    'des' => 'Menampilkan Nama Kader Ekonomi Desa',
                    'group' => 'non_lembaga',
                ],
                [
                    'key' => '{desa}',
                    'des' => 'Menampilkan Nama Desa',
                    'group' => 'non_lembaga',
                ],
                [
                    'key' => '{sebutan_kades}',
                    'des' => 'Menampilkan Sebutan Kepala Desa/Lurah',
                    'group' => 'non_lembaga',
                ],
                [
                    'key' => '{penjamin}',
                    'des' => 'Menampilkan Nama penjamin',
                    'group' => 'non_lembaga',
                ],
                [
                    'key' => '{peminjam}',
                    'des' => 'Menampilkan Nama Peminjam',
                    'group' => 'non_lembaga',
                ],
                [
                    'key' => '{hubungan}',
                    'des' => 'Menampilkan Nama Hubungan Keluarga',
                    'group' => 'non_lembaga',
                ],
            ];
        } else {
            $kec = $data['kec'];
            $pinkel = $data['pinkel'];
            if ($individu) {
                $kel = $pinkel->anggota;
                $hub = $pinkel->anggota->keluarga;
                $desa = $pinkel->anggota->d;
            } else {
                $kel = $pinkel->kelompok;
                $desa = $pinkel->kelompok->d;
            }

            $ttd = strtr(json_decode($text, true), [
                '{kepala_lembaga}' => $kec->sebutan_level_1,
                '{kabag_administrasi}' => $kec->sebutan_level_2,
                '{kabag_keuangan}' => $kec->sebutan_level_3,
                '{verifikator}' => $kec->nama_tv_long,
                '{pengawas}' => $kec->nama_bp_long,
                '{ketua}' => (!$individu) ? $pinkel->kelompok->ketua : $pinkel->anggota->namadepan,
                '{sekretaris}' => (!$individu) ? $pinkel->kelompok->sekretaris : '',
                '{bendahara}' => (!$individu) ? $pinkel->kelompok->bendahara : '',
                '{kades}' => $desa->kades,
                '{nip}' => $desa->nip,
                '{sekdes}' => $desa->sekdes,
                '{ked}' => $desa->ked,
                '{desa}' => $desa->nama_desa,
                '{sebutan_kades}' => $desa->sebutan_desa->sebutan_kades,
                '{penjamin}' => $kel->penjamin,
                '{peminjam}' => $kel->namadepan,
                '{hubungan}' => $hub->kekeluargaan,
                '1' => '1',
                '0' => '0'
            ]);

            return $ttd;
        }
    }

    public static function spk($text = false, $data = [])
    {
        $keywordReplacer = self::keywordReplacer($data);
        if ($text === false) {
            return $keywordReplacer;
        }

        self::$replacer = $keywordReplacer;
        $text = json_decode($text, true);
        $textReplacer = self::keyReplacer($text);

        return self::replacement($text, $textReplacer);
    }

    private static function parsePlaceholder($input, $expression = false)
    {
        if (str_starts_with($input, '=')) {
            $expr = substr($input, 1);

            if (str_starts_with($expr, '(')) {
                return self::mathExpression($expr);
            }

            if (preg_match('/^([a-zA-Z0-9_]+)\((.*)\)$/', $expr, $matches)) {
                $functionName = __CLASS__ . '::' . $matches[1];
                $arg = $matches[2];

                return call_user_func($functionName, $arg);
            }
        }

        $input = '{' . $input . '}';
        $value = self::$replacer[$input]['value'];
        return (is_numeric($value) && strlen($value) > 3 && !$expression) ? number_format($value, 2) : $value;
    }

    private static function mathExpression($input)
    {
        $textReplacer = self::keyReplacer($input, true);
        $math = self::replacement($input, $textReplacer);

        return number_format(eval('return ' . $math . ';'), 2);
    }

    private static function terbilang($input)
    {
        $keuangan = new Keuangan;
        if (str_starts_with($input, '=')) {
            $input = self::parsePlaceholder($input);
            return self::terbilang($input);
        }

        $textReplacer = self::keyReplacer($input, true);
        $text = $keuangan->terbilang(self::replacement($input, $textReplacer));

        return str_replace('  ', ' ', $text);
    }

    private static function hari($text)
    {
        $textReplacer = self::keyReplacer($text, true);
        $text = self::replacement($text, $textReplacer);

        $date = Tanggal::parseTanggal($text);
        if ($date) {
            return Tanggal::namaHari($date);
        }

        return '';
    }

    private static function tanggal_latin($text)
    {
        $keuangan = new Keuangan;

        $textReplacer = self::keyReplacer($text, true);
        $text = self::replacement($text, $textReplacer);
        $date = Tanggal::parseTanggal($text);

        $tanggal_latin = $keuangan->terbilang(Tanggal::hari($date));
        $nama_bulan = Tanggal::namaBulan($date);
        $tahun_latin = $keuangan->terbilang(Tanggal::tahun($date));
        $tanggal_latin = $tanggal_latin . ' bulan ' . $nama_bulan . ' tahun ' . $tahun_latin;

        return $tanggal_latin;
    }

    private static function keyReplacer($text, $expression = false)
    {
        preg_match_all('/\{(?:[^{}]++|(?R))*\}/', $text, $matches);

        $textReplacer = [];
        $matches = array_unique($matches[0]);
        foreach ($matches as $match) {
            $textReplacer[$match] = self::parsePlaceholder(trim($match, '{}'), $expression);
        }

        return $textReplacer;
    }

    private static function replacement($text, $textReplacer)
    {
        $text = strtr($text, $textReplacer);
        return $text;
    }

    private function jaminan($id = null)
    {
        $jaminan = [
            [
                'id' => '1',
                'nama' => 'Surat Tanah',
            ],
            [
                'id' => '2',
                'nama' => 'BPKB',
            ],
            [
                'id' => '3',
                'nama' => 'SK. Pegawai',
            ],
            [
                'id' => '4',
                'nama' => 'Lain Lain',
            ],
            [
                'id' => '5',
                'nama' => 'Surat Tanah dan Bangunan (SHM)',
            ],
        ];

        if ($id) {
            return $jaminan[$id - 1]['nama'];
        }

        return $jaminan;
    }

    private static function keywordReplacer($data = [])
    {
        // $nama_jaminan = '';
        // $keterangan_jaminan = '';
        // $nilai_jaminan = '';
        // $jenis_jaminan = '';
        // if (isset($data['pinkel'])) {
        //     $jaminan = json_decode($data['pinkel']->jaminan, true);
        //     if ($jaminan) {
        //         $nama_jaminan = $jaminan['nama_jaminan'];
        //         $keterangan_jaminan = $jaminan['keterangan'];
        //         $nilai_jaminan = $jaminan['nilai_jaminan'];
        //         $jenis_jaminan = $jaminan['jenis_jaminan'];
        //     }
        // }

        $keywordReplacer = [
            '{nomor_spk}' => [
                'desc' => 'Menampilkan Nomor SPK',
                'value' => (isset($data['pinkel'])) ? $data['pinkel']->spk_no : '',
            ],
            '{nama_lembaga}' => [
                'desc' => 'Menampilkan Nama Lembaga Usaha',
                'value' => (isset($data['kec'])) ? ucwords($data['kec']->nama_lembaga_sort) : '',
            ],
            '{nama_kecamatan}' => [
                'desc' => 'Menampilkan Nama Kecamatan',
                'value' => (isset($data['kec'])) ? ucwords($data['kec']->nama_kec) : '',
            ],
            '{sebutan_kepala_lembaga}' => [
                'desc' => 'Menampilkan Sebutan Kepala Lembaga',
                'value' => (isset($data['kec'])) ? ucwords($data['kec']->sebutan_level_1) : '',
            ],
            '{nama_nasabah}' => [
                'desc' => 'Menampilkan Nama Nasabah',
                'value' => (isset($data['pinkel'])) ? ucwords($data['pinkel']->anggota->namadepan) : '',
            ],
            '{jenis_kelamin}' => [
                'desc' => 'Menampilkan Jenis Kelamin (L/P) Nasabah',
                'value' => (isset($data['pinkel'])) ? ucwords($data['pinkel']->anggota->jk) : '',
            ],
            '{tempat_lahir}' => [
                'desc' => 'Menampilkan Tempat Lahir Nasabah',
                'value' => (isset($data['pinkel'])) ? ucwords($data['pinkel']->anggota->tempat_lahir) : '',
            ],
            '{tanggal_lahir}' => [
                'desc' => 'Menampilkan Tanggal Lahir Nasabah',
                'value' => (isset($data['pinkel'])) ? ucwords(Tanggal::tglLatin($data['pinkel']->anggota->tgl_lahir)) : '',
            ],
            '{nik_nasabah}' => [
                'desc' => 'Menampilkan NIK Nasabah',
                'value' => (isset($data['pinkel'])) ? $data['pinkel']->anggota->nik : '',
            ],
            '{alamat_nasabah}' => [
                'desc' => 'Menampilkan Alamat Nasabah',
                'value' => (isset($data['pinkel'])) ? $data['pinkel']->anggota->alamat : '',
            ],
            // '{nama_jaminan}' => [
            //     'desc' => 'Menampilkan Nama Jaminan Nasabah',
            //     'value' => $nama_jaminan,
            // ],
            // '{keterangan_jaminan}' => [
            //     'desc' => 'Menampilkan Keterangan Jaminan',
            //     'value' => $keterangan_jaminan,
            // ],
            // '{nilai_jaminan}' => [
            //     'desc' => 'Menampilkan Nilai Jual Jaminan',
            //     'value' => $nilai_jaminan,
            // ],
            // '{nilai_jaminan}' => [
            //     'desc' => 'Menampilkan Nilai Jual Jaminan',
            //     'value' => $nilai_jaminan,
            // ],
            // '{jenis_jaminan}' => [
            //     'desc' => 'Menampilkan Jenis Jaminan Nasabah',
            //     'value' => $jenis_jaminan,
            // ],
            '{jenis_pinjaman}' => [
                'desc' => 'Menampilkan Jenis Produk Pinjaman',
                'value' => (isset($data['pinkel'])) ? $data['pinkel']->jpp->deskripsi_jpp . ' (' . $data['pinkel']->jpp->nama_jpp . ')' : '',
            ],
            '{tanggal_proposal}' => [
                'desc' => 'Menampilkan Tanggal Proposal/Pengajuan Pinjaman',
                'value' => (isset($data['pinkel'])) ? ucwords(Tanggal::tglLatin($data['pinkel']->tgl_proposal)) : '',
            ],
            '{tanggal_verifikasi}' => [
                'desc' => 'Menampilkan Tanggal Verifikasi Pinjaman',
                'value' => (isset($data['pinkel'])) ? ucwords(Tanggal::tglLatin($data['pinkel']->tgl_verifikasi)) : '',
            ],
            '{tanggal_waiting}' => [
                'desc' => 'Menampilkan Tanggal Waiting/Pendanaan Pinjaman',
                'value' => (isset($data['pinkel'])) ? ucwords(Tanggal::tglLatin($data['pinkel']->tgl_tunggu)) : '',
            ],
            '{tanggal_cair}' => [
                'desc' => 'Menampilkan Tanggal Cair Pinjaman',
                'value' => (isset($data['pinkel'])) ? ucwords(Tanggal::tglLatin($data['pinkel']->tgl_cair)) : '',
            ],
            '{proposal}' => [
                'desc' => 'Menampilkan Proposal/Pengajuan Pinjaman (10,000,000.00)',
                'value' => (isset($data['pinkel'])) ? $data['pinkel']->proposal : '',
            ],
            '{verifikasi}' => [
                'desc' => 'Menampilkan Rekom Verifikator (10,000,000.00)',
                'value' => (isset($data['pinkel'])) ? $data['pinkel']->verifikasi : '',
            ],
            '{alokasi}' => [
                'desc' => 'Menampilkan Alokasi Pinjaman (10,000,000.00)',
                'value' => (isset($data['pinkel'])) ? $data['pinkel']->alokasi : '',
            ],
            '{jangka}' => [
                'desc' => 'Menampilkan Jangka/Tempo Pinjaman (bulan)',
                'value' => (isset($data['pinkel'])) ? $data['pinkel']->jangka : '',
            ],
            '{today}' => [
                'desc' => 'Menampilkan Tanggal Hari Ini',
                'value' => ucwords(Tanggal::tglLatin(date('Y-m-d'))),
            ],
        ];

        return $keywordReplacer;
    }

    public static function fungsi()
    {
        $keuangan = new Keuangan;

        $today = date('Y-m-d');
        $tanggal = date('d/m/Y');
        $nama_hari = Tanggal::namaHari($today);

        $tanggal_latin = $keuangan->terbilang(Tanggal::hari($today));
        $nama_bulan = Tanggal::namaBulan($today);
        $tahun_latin = $keuangan->terbilang(Tanggal::tahun($today));
        $tanggal_latin = $tanggal_latin . ' bulan ' . $nama_bulan . ' tahun ' . $tahun_latin;

        $fungsi = [
            'Terbilang' => [
                'fungsi' => '{=terbilang(...)}',
                'desc' => 'Merubah angka menjadi teks. Contoh : <code>{=terbilang(1000000)}</code> akan menghasilkan teks <code>Satu Juta</code>',
            ],
            'Tanggal' => [
                'fungsi' => '{=tanggal_latin(...)}',
                'desc' => 'Menampilkan tanggal dalam format teks. Contoh : <code>{=tanggal_latin(' . $tanggal . ')}</code> akan menghasilkan teks <code>' . $tanggal_latin . '</code>',
            ],
            'Hari' => [
                'fungsi' => '{=hari(...)}',
                'desc' => 'Menampilkan nama hari pada suatu tanggal. Contoh : <code>{=hari(' . $tanggal . ')}</code> akan menghasilkan teks <code>' . $nama_hari . '</code>',
            ],
            'Aritmatika' => [
                'fungsi' => '{=(...)}',
                'desc' => 'Melakukan operasi hitung aritmatika (+, -, *, /). Contoh : <code>{=(10000*(10/100))}</code> akan menghasilkan angka <code>1000</code>',
            ]
        ];

        return $fungsi;
    }
}
