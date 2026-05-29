<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    // ── Show landing (Welcome) ─────────────────────────────────────────────────

    public function welcome(): Response
    {
        return Inertia::render('Welcome', [
            'flash' => session('flash'),
        ]);
    }

    // ── Register ──────────────────────────────────────────────────────────────

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'         => ['required', 'string', 'max:100'],
            'email'        => ['required', 'email', 'unique:users,email'],
            'password'     => ['required', 'confirmed', Password::min(6)],
            'team_name'    => ['required', 'string', 'max:100'],
            'competition'  => ['required', 'in:web-development'],
            'member_count' => ['required', 'integer', 'min:2', 'max:3'],
            'institution'  => ['required', 'string', 'max:150'],
            'phone'        => ['required', 'string', 'max:20'],
            'category'     => ['required', 'in:sma,mahasiswa,umum'],
        ], [
            'email.unique'          => 'Email sudah terdaftar.',
            'password.confirmed'    => 'Konfirmasi password tidak cocok.',
            'password.min'          => 'Password minimal 6 karakter.',
            'team_name.required'    => 'Nama tim wajib diisi.',
            'institution.required'  => 'Asal sekolah/kampus wajib diisi.',
            'phone.required'        => 'Nomor HP wajib diisi.',
        ]);

        // Create user
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => 'peserta',
        ]);

        // Create registration
        Registration::create([
            'user_id'      => $user->id,
            'team_name'    => $data['team_name'],
            'competition'  => $data['competition'],
            'member_count' => $data['member_count'],
            'institution'  => $data['institution'],
            'phone'        => $data['phone'],
            'category'     => $data['category'],
            'status'       => 'pending_payment',
        ]);

        // Auto-login
        Auth::login($user);

        return redirect()->route('dashboard');
    }

    // ── Login ─────────────────────────────────────────────────────────────────

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Route admin to admin panel
            if ($user->isAdmin()) {
                return redirect()->route('admin.index');
            }

            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    // ── Logout ────────────────────────────────────────────────────────────────

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('welcome');
    }
}
