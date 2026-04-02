<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class GoogleController extends Controller
{
    // Mengarahkan pengguna ke halaman login Google
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    // Menangani callback dari Google setelah login sukses
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Cari user berdasarkan google_id
            $user = User::where('google_id', $googleUser->id)->first();

            if ($user) {
                // Jika user sudah ada, langsung login
                Auth::login($user);
            } else {
                // Jika user belum ada, daftarkan akun baru
                $user = User::updateOrCreate(['email' => $googleUser->email],[
                    'name' => $googleUser->name,
                    'image' => $googleUser->avatar,
                    'google_id' => $googleUser->id
                ]);

                Auth::login($user);
            }
            // MEMBUAT TEKS TOKEN SANCTUM
            $namaPerangkat = request()->userAgent() ?? 'Unknown Device';
            $apiToken = $user->createToken($namaPerangkat)->plainTextToken;

            // Cetak token ini ke layar (atau kirim sebagai respon JSON jika API)
            // dd($apiToken); // Bentuk aslinya seperti: "1|QWertyUiOp..."

            return response()->json([
                'message' => 'Login via Google berhasil',
                'user' => $user,
                'token' => $apiToken
            ]); 

        } catch (\Exception $e) {
            return redirect('/')->with('error', 'Login via Google gagal. ' . $e->getMessage());
        }
    }

    public function logout(Request $request)
    {
        // 1. Jika request ini datang dari API/Postman menggunakan Token Sanctum, hapus tokennya!
        if ($request->bearerToken()) {
            $request->user()->currentAccessToken()->delete();
        }

        // 2. Jika request ini datang dari Web Browser (ada User di Session Web), matikan sessionnya!
        if (Auth::guard('web')->check()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json([
            'message' => 'Logout diproses dengan sukses'
        ]);
    }
}
