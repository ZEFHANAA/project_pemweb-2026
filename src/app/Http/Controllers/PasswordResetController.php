<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PasswordResetController extends Controller
{
    /**
     * Tampilkan halaman form e-mail untuk minta link reset
     */
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Proses pengiriman e-mail berisi link reset
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ], [
            'email.exists' => 'Kami tidak menemukan pengguna dengan alamat e-mail tersebut.'
        ]);

        $token = Str::random(64);

        // Hapus token lama jika ada
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        // Simpan token baru
        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);

        $action_link = route('password.reset', ['token' => $token, 'email' => $request->email]);

        $user = User::where('email', $request->email)->first();

        // Kirim email
        try {
            Mail::send('auth.email-reset', ['action_link' => $action_link, 'user' => $user], function($message) use($request) {
                $message->to($request->email);
                $message->subject('Reset Password - Peta Wisata');
            });
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengirim e-mail. Error: ' . $e->getMessage());
        }

        return back()->with('success', 'Kami telah mengirimkan link reset password ke e-mail Anda!');
    }

    /**
     * Tampilkan halaman form ganti password
     */
    public function showResetForm(Request $request, $token)
    {
        return view('auth.reset-password')->with(
            ['token' => $token, 'email' => $request->email]
        );
    }

    /**
     * Proses penggantian password
     */
    public function reset(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:8|confirmed',
            'token' => 'required'
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.exists' => 'Email tidak terdaftar di sistem kami.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal harus 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok dengan password baru.',
            'token.required' => 'Token reset tidak valid atau sudah kadaluarsa.'
        ]);

        $check_token = DB::table('password_reset_tokens')->where([
            'email' => $request->email,
            'token' => $request->token,
        ])->first();

        if(!$check_token){
            return back()->withInput()->with('error', 'Token tidak valid atau sudah kedaluwarsa!');
        }

        // Cek kedaluwarsa (misal 60 menit)
        $token_creation = Carbon::parse($check_token->created_at);
        if ($token_creation->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return back()->withInput()->with('error', 'Token sudah kedaluwarsa. Silakan minta link baru.');
        }

        // Update password
        $user = User::where('email', $request->email)->first();
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        // Hapus semua sesi login lama di device lain
        DB::table('sessions')->where('user_id', $user->id)->delete();

        // Hapus token
        DB::table('password_reset_tokens')->where(['email'=> $request->email])->delete();

        return redirect()->route('login')->with('success', 'Password Anda telah berhasil diubah! Silakan masuk.');
    }
}
