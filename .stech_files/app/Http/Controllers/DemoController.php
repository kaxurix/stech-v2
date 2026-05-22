<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * DemoController
 *
 * Handles the S-Tech prototype flow without real authentication or database
 * interactions. A simple session flag (`demo_logged_in`) acts as the auth gate
 * so stakeholders can experience the full UX during the presentation.
 */
class DemoController extends Controller
{
    // ── Landing Page ──────────────────────────────────────────────────────────

    public function welcome(): Response
    {
        return Inertia::render('Welcome');
    }

    // ── Dummy Login ───────────────────────────────────────────────────────────

    /**
     * Accept any credentials, stamp the session, redirect to dashboard.
     * No real validation against a database is performed.
     */
    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'min:1'],
        ]);

        // Simulate "authenticated" state with session data
        session([
            'demo_logged_in' => true,
            'demo_user' => [
                'name'   => $this->inferNameFromEmail($request->email),
                'email'  => $request->email,
                'team'   => 'Tim Alpha',
                'competition' => 'Web Design',
                'status' => 'pending_payment',  // pending_payment | verified
            ],
        ]);

        return redirect()->route('dashboard');
    }

    // ── Dummy Logout ──────────────────────────────────────────────────────────

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget(['demo_logged_in', 'demo_user']);

        return redirect()->route('welcome');
    }

    // ── Protected Dashboard ───────────────────────────────────────────────────

    /**
     * Pass the demo user data as Inertia props so the Vue component can
     * render personalised content without any API calls.
     */
    public function dashboard(): Response
    {
        $user = session('demo_user', [
            'name'        => 'Peserta Demo',
            'email'       => 'demo@stech.id',
            'team'        => 'Tim Alpha',
            'competition' => 'Web Design',
            'status'      => 'pending_payment',
        ]);

        return Inertia::render('Dashboard', [
            'user' => $user,
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function inferNameFromEmail(string $email): string
    {
        $local = explode('@', $email)[0];
        return ucwords(str_replace(['.', '_', '-'], ' ', $local));
    }
}
