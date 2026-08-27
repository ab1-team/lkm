<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class UpdateFitur extends Model
{
    protected $table = 'update_fitur';

    public $timestamps = false;

    protected $fillable = [
        'tanggal', 'judul', 'deskripsi', 'foto', 'jenis',
    ];

    protected $casts = [
        'tanggal' => 'datetime',
    ];

    public function scopeDalamMasaNotif(Builder $query): Builder
    {
        $hari = config('update_fitur.masa_berlaku_hari', 7);

        return $query->where('tanggal', '>=', now()->subDays($hari));
    }

    public function scopeTerbaruDulu(Builder $query): Builder
    {
        return $query->orderByDesc('tanggal');
    }
}