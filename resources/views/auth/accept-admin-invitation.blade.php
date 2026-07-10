@extends('layouts.auth')

@section('title', 'Accept admin invitation')

@section('content')
    <h1>Set up your admin account</h1>
    <p class="subtitle">You have been invited to join as an administrator. Choose a password to activate your account.</p>

    @if ($errors->any())
        <div class="message error" role="alert">
            {{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('admin-invitations.complete', ['token' => $token]) }}" method="POST">
        @csrf

        <div class="field">
            <label for="name">Full name</label>
            <input id="name" type="text" value="{{ $invitation->user->name }}" readonly>
        </div>

        <div class="field">
            <label for="email">Email address</label>
            <input id="email" type="email" value="{{ $invitation->user->email }}" readonly>
        </div>

        <div class="field">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" placeholder="At least 8 characters" autocomplete="new-password" minlength="8" required autofocus>
        </div>

        <div class="field">
            <label for="password_confirmation">Confirm password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Repeat your password" autocomplete="new-password" minlength="8" required>
        </div>

        <button class="button" type="submit">Activate admin account</button>
    </form>

    <p class="footnote">This invitation expires {{ $invitation->expires_at->diffForHumans() }}.</p>
@endsection
