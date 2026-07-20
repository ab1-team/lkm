<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Whatsapp extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'whatsapp';
    public $timestamps = false;

    protected $fillable = [
        'lokasi',
        'nama',
        'instance_name',
        'status',
        'deletedAt',
    ];

    protected $dates = ['deletedAt'];
    const DELETED_AT = 'deletedAt';

    public function kecamatan()
    {
        return $this->belongsTo(Kecamatan::class, 'lokasi');
    }
}