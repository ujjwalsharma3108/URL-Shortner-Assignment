@extends('layouts.auth')

@section('title', 'Sign in')

@section('content')
    <h1>Welcome back</h1>
    <p class="subtitle">Sign in to manage your shortened links.</p>

    <form
        action="{{ route('login.store') }}"
        method="POST"
        data-auth-form
        data-redirect="{{ route('dashboard') }}"
        data-success="Login successful. Redirecting..."
    >
        @csrf
        <div class="message" data-message role="alert"></div>

        <div class="field">
            <label for="email">Email address</label>
            <input
                id="email"
                name="email"
                type="email"
                placeholder="you@example.com"
                autocomplete="email"
                required
                autofocus
            >
        </div>

        <div class="field">
            <label for="password">Password</label>
            <input
                id="password"
                name="password"
                type="password"
                placeholder="Enter your password"
                autocomplete="current-password"
                required
            >
        </div>

        <button class="button" type="submit">Sign in</button>
    </form>
@endsection
