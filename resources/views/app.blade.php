<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="Soedirman Technophoria — Ajang kompetisi teknologi tahunan bergengsi Universitas Jenderal Soedirman." />

    <!-- SEO / OG -->
    <meta property="og:title" content="Soedirman Technophoria 2026" />
    <meta property="og:description" content="Kompetisi teknologi, inovasi, dan kreativitas terbesar di Purwokerto." />
    <meta property="og:type" content="website" />

    <title inertia>Soedirman Technophoria</title>

    <!-- Fonts pre-connect -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />

    <!-- Inertia head -->
    @inertiaHead

    <!-- Vite assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-[#0a0a0a] text-white">
    @inertia
</body>
</html>
