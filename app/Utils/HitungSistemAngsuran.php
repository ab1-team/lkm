<?php

namespace App\Utils;

use App\Models\SistemAngsuran;

class HitungSistemAngsuran
{
    /**
     * Hitung tempo & mulai_angsuran berdasarkan SistemAngsuran dan jangka.
     * Untuk menjaga konsistensi dengan method sistem() lama di PinjamanKelompokController.
     *
     * @return array{tempo:int, mulai_angsuran:int, sistem:int}
     */
    public static function hitung(SistemAngsuran $sa, int $jangka): array
    {
        $sistem = (int) ($sa->sistem ?: 1);

        if ($sa->isBulananDitunda() && ! is_null($sa->tunda_bulan)) {
            $tempo = (int) ($jangka - ($sa->tunda_bulan / $sistem));
            $mulai_angsuran = $jangka - $tempo;

            return [
                'tempo'          => $tempo,
                'sistem'         => $sistem,
                'mulai_angsuran' => $mulai_angsuran,
            ];
        }

        return [
            'tempo'          => (int) floor($jangka / $sistem),
            'sistem'         => $sistem,
            'mulai_angsuran' => 0,
        ];
    }
}