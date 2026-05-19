<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;

class TandaTanganDokumen extends Model
{
    protected $table = 'tanda_tangan_dokumen';
    public $timestamps = false;

    protected $fillable = ['lokasi', 'jenis', 'tanda_tangan'];

    public static function ambil(string $jenis): ?self
    {
        return static::where('lokasi', Session::get('lokasi'))
                     ->where('jenis', $jenis)
                     ->first();
    }

    public static function daftarJenis(): array
    {
        return [
            'laporan'              => 'Laporan Periodik',
            'spk'                  => 'SPK & Dokumen Pencairan',
            'tanda_terima_jaminan' => 'Tanda Terima Jaminan',
            'pengambilan_jaminan'  => 'Pengambilan Jaminan',
            'terima_jaminan'       => 'Terima Jaminan',
        ];
    }
}
