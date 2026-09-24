<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SistemAngsuran extends Model
{
    use HasFactory;
    protected $table = 'sistem_angsuran';
    public $timestamps = false;

    public const JENIS_BULANAN         = 'bulanan';
    public const JENIS_HARIAN          = 'harian';
    public const JENIS_BULANAN_DITUNDA = 'bulanan_ditunda';

    public function isHarian(): bool
    {
        return $this->jenis === self::JENIS_HARIAN;
    }

    public function isBulanan(): bool
    {
        return $this->jenis === self::JENIS_BULANAN;
    }

    public function isBulananDitunda(): bool
    {
        return $this->jenis === self::JENIS_BULANAN_DITUNDA;
    }

    public function labelSatuan(): string
    {
        return $this->isHarian() ? 'Hari' : 'Bulan';
    }

    public function labelSatuanSingkat(): string
    {
        return $this->isHarian() ? 'mgg' : 'bln';
    }

    /**
     * Daftar id SistemAngsuran berdasarkan jenis (cached per-request).
     *
     * @return array<int>
     */
    public static function idListByJenis(string $jenis): array
    {
        static $cache = [];
        if (! isset($cache[$jenis])) {
            $cache[$jenis] = self::where('jenis', $jenis)->pluck('id')->map(fn ($v) => (int) $v)->all();
        }
        return $cache[$jenis];
    }

    /**
     * Hitung tempo (jumlah angsuran yang dibayar penuh) untuk sistem angsuran.
     * - bulanan: floor(jangka / sistem)
     * - bulanan_ditunda: jangk - tunda_bulan / sistem  (grace)
     * - harian: mengikuti pola bulanan (tempo tetap sama, jatuh tempo berbasis addDays)
     */
    public function hitungTempo(int $jangka): int
    {
        $sistem = (int) ($this->sistem ?: 1);

        if ($this->isBulananDitunda() && $this->tunda_bulan) {
            return (int) ($jangka - ($this->tunda_bulan / $sistem));
        }

        return (int) floor($jangka / $sistem);
    }

    /**
     * Scope: urutkan berdasarkan kolom `urutan` (id yang paling sering dipakai muncul duluan).
     * id dengan urutan NULL akan tampil di akhir, diurutkan by id ASC.
     */
    public function scopeOrderByUsage($query)
    {
        return $query->orderByRaw('urutan IS NULL, urutan ASC, id ASC');
    }
}