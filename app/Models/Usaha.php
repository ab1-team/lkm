<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Usaha extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $table = 'usaha';
    public $timestamps = false;

    public function d()
    {
        return $this->belongsTo(Desa::class, 'kd_desa', 'kode_desa');
    }

    public function ttd()
    {
        return $this->hasOne(TandaTanganDokumen::class, 'lokasi', 'id')
                    ->where('jenis', 'laporan');
    }

    public function ttdSpk()
    {
        return $this->hasOne(TandaTanganDokumen::class, 'lokasi', 'id')
                    ->where('jenis', 'spk');
    }
}
