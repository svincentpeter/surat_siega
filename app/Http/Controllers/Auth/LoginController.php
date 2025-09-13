<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /**
     * Tujuan redirect setelah berhasil login.
     */
    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * Tampilkan form login (kalau Anda pakai view khusus).
     */
    public function showLoginForm()
    {
        return view('auth.login'); // sesuaikan jika nama blade berbeda
    }

    /**
     * Proses login manual.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $email = $request->input('email');
        $password = $request->input('password');

        // Ambil user berdasarkan email
        $user = \App\Models\User::where('email', $email)->first();
        if (! $user) {
            return back()
                ->withErrors(['email' => 'Email tidak ditemukan.'])
                ->withInput(['email' => $email]);
        }

        // Periksa password secara eksplisit, tangani exception kalau format hash aneh
        try {
            if (! Hash::check($password, $user->sandi_hash)) {
                return back()
                    ->withErrors(['password' => 'Password salah.'])
                    ->withInput(['email' => $email]);
            }
        } catch (\RuntimeException $e) {
            // Biasanya terjadi jika hash bukan format yang dikenali oleh hasher
            \Log::warning("LoginController: gagal verifikasi password untuk user {$email}: " . $e->getMessage());
            return back()
                ->withErrors(['password' => 'Terjadi masalah saat memverifikasi password.'])
                ->withInput(['email' => $email]);
        }

        // Rehash jika perlu (misal format hash lama atau konfigurasi berubah)
        try {
            if (Hash::needsRehash($user->sandi_hash)) {
                $user->sandi_hash = Hash::make($password);
                $user->save();
            }
        } catch (\Throwable $e) {
            // Jangan ganggu login, cuma log
            \Log::warning("LoginController: rehash gagal untuk user {$email}: " . $e->getMessage());
        }

        // Login user secara manual
        Auth::login($user, $request->filled('remember'));

        // Regenerate session untuk mencegah fixation
        $request->session()->regenerate();

        return redirect()->intended($this->redirectTo);
    }

    /**
     * Logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
