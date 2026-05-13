<?php

namespace App\Utils;

class Calk
{
  public static function calk($text = false, $data)
  {
    if ($text === false) {
      return [
        [
          'key' => '{nama_lembaga}',
          'des' => 'Menampilkan Nama Lembaga Usaha',
        ],
        [
          'key' => '{nomor_badan_hukum}',
          'des' => 'Menampilkan Nomor Badan Hukum/Nomor AHU',
        ],
        [
          'key' => '{tanggal_laporan}',
          'des' => 'Menampilkan Tanggal Laporan Dibuat',
        ],
        [
          'key' => '{neraca}',
          'des' => 'Menampilkan Neraca CALK sampai level 4'
        ]
      ];
    }

    $kec = $data['kec'];
    $tgl_kondisi = $data['tgl_kondisi'] ?? date('Y-m-d');
    $tanggal_laporan = Tanggal::namaBulan($tgl_kondisi) . ' tahun ' . Tanggal::tahun($tgl_kondisi);
    $view_neraca = $data['view_neraca'];

    $decoded_text = json_decode($text);
    if ($decoded_text === null && json_last_error() !== JSON_ERROR_NONE) {
        $decoded_text = $text;
    }

    $calk = strtr((string) $decoded_text, [
      '{nama_lembaga}' => $kec->nama_lembaga_sort,
      '{nomor_badan_hukum}' => $kec->nomor_bh,
      '{tanggal_laporan}' => $tanggal_laporan,
      '{neraca}' => $view_neraca,
    ]);

    return $calk;
  }
}
