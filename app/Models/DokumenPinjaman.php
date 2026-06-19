<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DokumenPinjaman extends Model
{
    protected $table = 'dokumen_pinjaman';
    public $timestamps = false;

    protected $fillable = ['file', 'title', 'excel', 'jenis_dokumen', 'lokasi'];

    protected $casts = [
        'excel'  => 'boolean',
        'lokasi' => 'integer',
    ];

    public function ttdDokumen()
    {
        return $this->hasOne(TandaTanganDokumen::class, 'jenis', 'file')
                    ->where('lokasi', session('lokasi'));
    }

    public function scopeVisibleAt($query, $lokasi)
    {
        return $query->where(function ($q) use ($lokasi) {
            $q->where('lokasi', 0)->orWhere('lokasi', $lokasi);
        });
    }

    public function scopeForKategori($query, string $kategori, string $jenisPinjaman = 'individu')
    {
        return $query->whereNotNull('jenis_dokumen')
                     ->where('jenis_dokumen', '!=', '')
                     ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(jenis_dokumen, '$.\"$jenisPinjaman\"')) = ?", [$kategori])
                     ->orderBy('id');
    }
}
