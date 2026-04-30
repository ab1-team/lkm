<?php

namespace App\Http\Controllers\Rekap;

use App\Http\Controllers\Controller;
use App\Models\Rekap;
use App\Models\Kecamatan;
use App\Models\Wilayah;
use App\Utils\Keuangan;
use Auth;
use Illuminate\Http\Request;

class AuthController extends Controller
{ 
    public function index()
    {
        $url = request()->getHost();
        $rekap = Rekap::where('web_rekap', $url)->first();
            $nama_rekap = ' Rekap ' . ucwords(strtolower($rekap->nama_rekap));
        return view('rekap.auth.login')->with(compact('nama_rekap'));
    }

    public function login(Request $request)
    {
        $url = $request->getHost();
        $data = $request->only([
            'username', 'password'
        ]);

        $validate = $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        $rekap = Rekap::where('web_rekap', $url)->first();
        $login_rekap = Rekap::where('web_rekap', $url)
                            ->where('username', $data['username'])
                            ->first();

        if ($login_rekap && $login_rekap->password === $data['password']) {
            if (Auth::guard('rekap')->loginUsingId($login_rekap->id)) {
                $request->session()->regenerate();

                $lokasiIds = array_filter(explode(',', $login_rekap->lokasi));
                $kdKecList = Kecamatan::whereIn('id', $lokasiIds)->pluck('kd_kec');
                $kecamatan = Kecamatan::whereIn('kd_kec', $kdKecList)
                                    ->select('id', 'kd_kec as kode', 'nama_kec as nama')
                                    ->orderBy('nama_kec', 'ASC')
                                    ->get();

                session([
                    'nama_rekap' => ucwords(strtolower($login_rekap->nama_rekap)),
                    'kecamatan' => $kecamatan,
                    'kd_rekap' => "",
                    'kd_prov' => "",
                    'id_rekap' => $login_rekap->id,
                    'rekapan' => $login_rekap->lokasi,
                ]);

                return redirect('/rekap/dashboard')->with([
                    'pesan' => 'Login rekapitulasi ' . ucwords(strtolower($login_rekap->nama_rekap)) . ' Berhasil'
                ]);
            }
        }

        $error = 'Username atau Password Salah';
        return redirect()->back()->with('error', $error);
    }

    public function logout(Request $request)
    {
        $user = auth()->guard('rekap')->user()->nama_rekap;
        Auth::guard('rekap')->logout();

        return redirect('/rekap')->with('pesan', 'Terima Kasih');
    }
}
