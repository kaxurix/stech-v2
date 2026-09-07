<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="Soedirman Technophoria — Ajang kompetisi teknologi tahunan bergengsi Universitas Jenderal Soedirman." />

    {{-- SEO / OG --}}
    <meta property="og:title" content="Soedirman Technophoria 2026" />
    <meta property="og:description" content="Kompetisi teknologi, inovasi, dan kreativitas terbesar di Purwokerto." />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="{{ url('/') }}" />
    <meta property="og:image" content="{{ asset('images/og-image.png') }}" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />
    <meta property="og:locale" content="id_ID" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="Soedirman Technophoria 2026" />
    <meta name="twitter:description" content="Kompetisi teknologi, inovasi, dan kreativitas terbesar di Purwokerto." />
    <meta name="twitter:image" content="{{ asset('images/og-image.png') }}" />

    <title inertia>Soedirman Technophoria</title>

    {{-- Favicon --}}
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}" />
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}" />
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}" />
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}" />
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" />

    {{-- Fonts: preconnect + non-blocking load (preload as style, swap to stylesheet on
         load). font-display=swap already lets text paint with a fallback font immediately;
         this also keeps the font CSS request itself from blocking first paint. --}}
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link rel="preload" as="style"
          href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"
          onload="this.onload=null;this.rel='stylesheet'" />
    <noscript>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" />
    </noscript>

    {{-- Inertia head --}}
    @inertiaHead

    {{-- Vite assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-blue-950 text-white">
    @inertia
</body>
</html>
