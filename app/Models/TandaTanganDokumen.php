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
        ];
    }

    public static function daftarJenisDokumenPinjaman($lokasi = null): array
    {
        $lokasi = $lokasi ?? session('lokasi');
        $q = DokumenPinjaman::whereNotNull('file')
                            ->where('file', '!=', '')
                            ->orderBy('id');
        if (!is_null($lokasi)) {
            $q->visibleAt($lokasi);
        }
        return $q->pluck('title', 'file')->all();
    }

    // Accessor untuk kompatibilitas dengan tanda_tangan_laporan table
    public function getTandaTanganPelaporanAttribute()
    {
        return $this->tanda_tangan;
    }

    public function getTandaTanganSpkAttribute()
    {
        return $this->tanda_tangan;
    }
}
