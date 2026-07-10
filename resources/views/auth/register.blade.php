@extends('layouts.auth')

@section('title', 'Create account')

@section('content')
    <h1>Create your account</h1>
    <p class="subtitle">Start creating and managing short links in minutes.</p>

    <form action="{{ route('register.store') }}" method="POST" data-auth-form data-success="Account created. You are now signed in.">
        <div class="message" data-message role="alert"></div>

        <div class="field">
            <label for="name">Full name</label>
            <input id="name" name="name" type="text" placeholder="Your name" autocomplete="name" required autofocus>
        </div>

        <div class="field">
            <label for="email">Email address</label>
            <input id="email" name="email" type="email" placeholder="you@example.com" autocomplete="email" required>
        </div>

        <div class="field">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" placeholder="At least 8 characters" autocomplete="new-password" minlength="8" required>
        </div>

        <div class="field">
            <label for="password_confirmation">Confirm password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Repeat your password" autocomplete="new-password" minlength="8" required>
        </div>

        <button class="button" type="submit">Create account</button>
    </form>

    <p class="switch">
        Already have an account? <a href="{{ route('login') }}">Sign in</a>
    </p>
@endsection
