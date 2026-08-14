<?php

namespace App\Http\Middleware;

use App\Models\AdminInvoice;
use App\Models\Usaha;
use App\Models\Kecamatan;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Session;
use Symfony\Component\HttpFoundation\Response;


class Aktif
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $today = Carbon::today()->toDateString();
        $invoice = AdminInvoice::where([
            ['status', 'UNPAID'],
            ['lokasi', Session::get('lokasi')]
        ])->where('tgl_lunas', '<=', $today)
            ->orderBy('tgl_lunas')
            ->first();

        $isOverdue = $invoice && Carbon::parse($invoice->tgl_lunas)->lt(Carbon::today());

        if ($isOverdue) {
            Session::put('invoice', $invoice);
        } else {
            Session::forget('invoice');
        }

        if ($invoice && $isOverdue) {
            if ($request->is('dashboard') || $request->is('pengaturan/*') || $request->is('pelaporan/*')) {
                return $next($request);
            } else {
                Session::flash('warning', 'Anda harus menyelesaikan invoice yang belum terbayar terlebih dahulu');
                return redirect('/dashboard');
            }
        }

        return $next($request);
    }
}
