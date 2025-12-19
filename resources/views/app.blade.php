<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="midtrans-client-key" content="{{ config('services.midtrans.client_key') }}">
    <title>{{ config('app.name', 'Tour App') }}</title>
    <link rel="icon" type="image/webp" href="{{ asset('images/logo.webp') }}">
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/main.jsx'])
</head>
<body class="antialiased bg-slate-50 text-slate-900">
    <div id="root"></div>
</body>
</html>
