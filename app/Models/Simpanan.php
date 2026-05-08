<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Session;

class Simpanan extends Model
{
    use HasFactory;
    protected $table;
    public $timestamps = false;

    protected $guarded = ['id'];

    public function __construct()
    {
        $this->table = 'simpanan_anggota_' . Session::get('lokasi');
    }

    public function js()
    {
        return $this->belongsTo(JenisSimpanan::class, 'jenis_simpanan');
    }

    public function jasa()
    {
        return $this->belongsTo(JenisJasa::class, 'jenis_jasa');
    }
    
    public function realSimpananTerbesar()
    {
        return $this->hasOne(RealSimpanan::class, 'cif', 'id')->orderBy('id', 'desc');
    }

    public function anggota()
    {
        return $this->belongsTo(Anggota::class, 'nia', 'id');
    }


    public function trx()
    {
        return $this->hasMany(Transaksi::class, 'id_simp', 'id')->whereNull('deleted_at')->orderBy('tgl_transaksi', 'ASC')->orderBy('idtp', 'ASC');
    }


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function real_s()
    {
        return $this->hasMany(RealSimpanan::class, 'cif', 'id')->orderBy('tgl_transaksi', 'ASC')->orderBy('id', 'ASC');
    }


    public function getTableStructure()
    {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }
}
