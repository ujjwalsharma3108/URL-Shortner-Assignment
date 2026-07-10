<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') · {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>
<body>
<div class="app-shell">
    @include('partials.app.sidebar')

    <main class="main">
        @include('partials.app.topbar')

        <div class="content">
            @include('partials.app.feedback')
            @yield('content')
        </div>
    </main>
</div>
@stack('scripts')
</body>
</html>
