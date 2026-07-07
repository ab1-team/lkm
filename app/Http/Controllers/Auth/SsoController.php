<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Kecamatan;
use App\Models\Menu;
use App\Models\MenuTombol;
use App\Models\User;
use App\Services\SsoTokenVerifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SsoController extends Controller
{
    public function __construct(private readonly SsoTokenVerifier $verifier)
    {
    }

    public function consume(Request $request)
    {
        $token = (string) $request->query('token', '');
        if ($token === '') {
            abort(400, 'Token SSO tidak ditemukan.');
        }

        // 1. Verify signature + expiry
        $payload = $this->verifier->decode($token);
        if ($payload === null) {
            Log::warning('SSO token invalid or expired', [
                'ip' => $request->ip(),
                'ua' => $request->userAgent(),
            ]);
            abort(401, 'Token SSO tidak valid atau sudah kedaluwarsa.');
        }

        // 2. Resolve Kecamatan by HOST (bukan payload)
        $host = $request->getHost();
        $kec = Kecamatan::where('web_kec', $host)
            ->orWhere('web_alternatif', $host)
            ->first();
        if (! $kec) {
            $serverName = (string) $request->server('SERVER_NAME');
            if (in_array($serverName, ['127.0.0.1', 'localhost'], true)
                || str_ends_with($serverName, '.test')) {
                $kec = Kecamatan::find(1);
            }
        }
        if (! $kec) {
            abort(404, 'Kecamatan untuk domain ini tidak ditemukan.');
        }

        // 3. Resolve LOCAL pivot admin: lokasi=kec.id, jabatan=1, level=1, status=1.
        //    Holding tidak memilih user mana yang login — domain + tabel lokal
        //    yang menentukan tenant binding. payload.email cuma konteks (audit).
        $pivot = User::where([
            ['lokasi', (string) $kec->id],
            ['jabatan', '1'],
            ['level', '1'],
            ['status', '1'],
        ])->first();
        if (! $pivot) {
            abort(403, 'Tidak ada admin aktif (jabatan=1, level=1) untuk kecamatan ini.');
        }

        // 4. Login sebagai admin lokal
        Auth::loginUsingId($pivot->id);
        $request->session()->regenerate();

        $lokasi = $pivot->lokasi;

        // 5. Build session keys — parity dengan AuthController::login()
        $hak_akses = explode(',', (string) $pivot->hak_akses);
        $angsuran = ! in_array('19', $hak_akses, true) && ! in_array('21', $hak_akses, true);

        $menu = Menu::where('parent_id', '0')
            ->whereNotIn('id', $hak_akses)
            ->where('aktif', 'Y')
            ->where(function ($query) use ($lokasi) {
                $query->where('lokasi', '0')
                    ->orWhere('lokasi', 'LIKE', '%#'.$lokasi.'#%');
            })
            ->where(function ($query) use ($lokasi) {
                $query->where('kecuali', '0')
                    ->orWhereNull('kecuali')
                    ->orWhere('kecuali', 'NOT LIKE', '%#'.$lokasi.'#%');
            })
            ->with([
                'child' => function ($query) use ($hak_akses, $lokasi) {
                    $query->whereNotIn('id', $hak_akses)
                        ->where(function ($query) use ($lokasi) {
                            $query->where('lokasi', '0')
                                ->orWhere('lokasi', 'LIKE', '%#'.$lokasi.'#%');
                        })
                        ->where(function ($query) use ($lokasi) {
                            $query->where('kecuali', '0')
                                ->orWhereNull('kecuali')
                                ->orWhere('kecuali', 'NOT LIKE', '%#'.$lokasi.'#%');
                        });
                },
                'child.child' => function ($query) use ($hak_akses, $lokasi) {
                    $query->whereNotIn('id', $hak_akses)
                        ->where(function ($query) use ($lokasi) {
                            $query->where('lokasi', '0')
                                ->orWhere('lokasi', 'LIKE', '%#'.$lokasi.'#%');
                        })
                        ->where(function ($query) use ($lokasi) {
                            $query->where('kecuali', '0')
                                ->orWhereNull('kecuali')
                                ->orWhere('kecuali', 'NOT LIKE', '%#'.$lokasi.'#%');
                        });
                },
            ])
            ->orderBy('sort', 'ASC')
            ->get();

        $AksesTombol = explode(',', (string) $pivot->akses_tombol);
        $MenuTombol = MenuTombol::whereNotIn('id', $AksesTombol)->pluck('akses')->toArray();

        $icon = $kec->logo
            ? '/storage/logo/'.$kec->logo
            : '/assets/img/icon/favicon.png';

        session([
            'lokasi' => $pivot->lokasi,
            'lokasi_user' => $pivot->lokasi,
            'nama' => $pivot->namadepan.' '.$pivot->namabelakang,
            'nama_lembaga' => str_replace('DBM ', '', (string) $kec->nama_lembaga_sort),
            'foto' => $pivot->foto,
            'logo' => $kec->logo,
            'icon' => $icon,
            'menu' => $menu,
            'tombol' => $MenuTombol,
            'angsuran' => $angsuran,
        ]);

        // 6. Audit log
        Log::info('SSO auto-login success', [
            'holding_email' => $payload['email'],
            'holding_uid' => $payload['uid'],
            'holding_role' => $payload['role'],
            'local_user_id' => $pivot->id,
            'kecamatan_id' => $kec->id,
            'ip' => $request->ip(),
        ]);

        return redirect('/dashboard');
    }
}