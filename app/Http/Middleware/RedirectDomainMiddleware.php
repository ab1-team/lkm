<?php

namespace App\Http\Middleware;

use App\Models\Kabupaten;
use App\Models\Kecamatan;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RedirectDomainMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost();

        if (Str::endsWith($host, '.siupk.net') && !Str::endsWith($host, '.test')) {
            $kec = Kecamatan::where('web_kec', $host)
                ->whereNotNull('web_alternatif')
                ->where('web_alternatif', '!=', '')
                ->first();

            if ($kec) {
                return redirect()->to('https://' . $kec->web_alternatif . $request->getRequestUri());
            }

            $kab = Kabupaten::where('web_kab', $host)
                ->whereNotNull('web_kab_alternatif')
                ->where('web_kab_alternatif', '!=', '')
                ->first();

            if ($kab) {
                return redirect()->to('https://' . $kab->web_kab_alternatif . $request->getRequestUri());
            }
        }

        return $next($request);
    }
}
