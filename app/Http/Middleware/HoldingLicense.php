<?php

namespace App\Http\Middleware;

use App\Models\License;
use Closure;
use Illuminate\Http\Request;
use Session;
use Symfony\Component\HttpFoundation\Response;

class HoldingLicense
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('X-Holding-Token');
        $slug  = $request->header('X-Holding-Tenant');

        if (!$token || !$slug) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid.',
            ], 401);
        }

        $license = License::where('api_secret', $token)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expired_at')
                  ->orWhere('expired_at', '>', now());
            })
            ->whereHas('kecamatan', function ($q) use ($slug) {
                $q->where('web_kec', $slug)
                  ->orWhere('web_alternatif', $slug);
            })
            ->with('kecamatan')
            ->first();

        if (!$license) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid.',
            ], 401);
        }

        if ($license->expired_at && $license->expired_at->lt(now())) {
            return response()->json([
                'success' => false,
                'message' => 'Lisensi kedaluwarsa.',
            ], 403);
        }

        $kec = $license->kecamatan;

        // Set session lokasi agar model (Rekening, Saldo, Transaksi, ArusKas)
        // otomatis pakai tabel tenant yang benar
        Session::put('lokasi', $kec->id);

        $request->attributes->set('holding_kec', $kec);
        $request->attributes->set('holding_license', $license);

        return $next($request);
    }
}
