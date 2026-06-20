<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser as Master;
use Auth;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function index()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'gmail'    => 'required|email',
            'password' => 'required'
        ]);

        $user = Master::where('gmail', $request->gmail)
                      ->where('password', $request->password)
                      ->first();

        if ($user) {
            Auth::guard('master')->login($user);
            $request->session()->regenerate();

            session(['admin' => $user->nama_lengkap]);

            return redirect()->intended('/master')
                ->with('pesan', 'Selamat Datang ' . $user->nama_lengkap);
        }

        return back()->with('error', 'Email atau Password Salah.');
    }

    public function logout(Request $request)
    {
        $user = auth()->guard('master')->user()->nama_lengkap;

        Auth::guard('master')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/master')->with('pesan', 'Terima Kasih ' . $user);
    }
}
