<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menambahkan security header yang jadi temuan pemindaian HostedScan
 * (CSP dan HSTS belum terpasang).
 *
 * Header ini sengaja dikirim dari Laravel, bukan dari konfigurasi nginx,
 * karena nginx di server kampus dikelola admin dan tidak bisa kita ubah.
 * Konfigurasi di docker/nginx/default.conf tidak dipakai di produksi.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Sumber eksternal yang memang dipakai halaman:
        // - fonts.googleapis.com / fonts.gstatic.com : font Inter
        // - ui-avatars.com                           : avatar admin bawaan Filament
        //
        // 'unsafe-inline' & 'unsafe-eval' diperlukan karena Alpine.js (dipakai
        // Livewire/Filament) mengevaluasi ekspresi di atribut saat runtime.
        // Tanpa keduanya panel admin mati total.
        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval'",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
            "font-src 'self' data: https://fonts.gstatic.com",
            "img-src 'self' data: https://ui-avatars.com",
            "connect-src 'self'",
            "frame-ancestors 'self'",
            "base-uri 'self'",
            "form-action 'self'",
        ]);

        $response->headers->set('Content-Security-Policy', $csp);
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // HSTS hanya bermakna (dan hanya boleh) di koneksi HTTPS. Dikirim saat
        // request tidak aman bisa mengunci pengguna kalau situs sempat diakses
        // via http di lingkungan lokal.
        if ($request->secure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains'
            );
        }

        return $response;
    }
}
