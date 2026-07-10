@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">Workspace overview</p>
            <h1 class="page-heading">Welcome back, {{ \Illuminate\Support\Str::before($user->name, ' ') }}!</h1>
            <p class="page-description">Your account summary and recent URL activity. Use the navigation to manage each module on its own page.</p>
        </div>
        <time class="date-chip" datetime="{{ now()->toDateString() }}">{{ now()->format('l, d M Y') }}</time>
    </section>

    <section class="stats-grid {{ $user->isMember() ? 'compact' : '' }}" aria-label="Workspace statistics">
        <x-stat-card label="Short URLs" :value="$urlStats['links']" note="Visible to your account" />
        <x-stat-card label="Total clicks" :value="$urlStats['hits']" tone="sky" note="One consolidated count" />

        @if ($user->isSuperAdmin())
            <x-stat-card label="Companies" :value="$directoryStats['companies']" tone="green" />
            <x-stat-card label="Admins" :value="$directoryStats['admins']" tone="amber" />
            <x-stat-card label="Members" :value="$directoryStats['members']" />
        @elseif ($user->isAdmin())
            <x-stat-card label="Managed users" :value="$directoryStats['team_users']" tone="green" />
            <x-stat-card label="Team admins" :value="$directoryStats['admins']" tone="amber" />
            <x-stat-card label="Team members" :value="$directoryStats['members']" />
        @endif
    </section>

    <section class="quick-grid" aria-label="Quick actions">
        <a class="quick-card" href="{{ route('short-urls.index') }}">
            <span class="quick-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9.5 14.5 14.5 9"/><path d="m7.2 17.8-1 .9a3.5 3.5 0 0 1-5-5l4-4a3.5 3.5 0 0 1 5 0"/><path d="m16.8 6.2 1-.9a3.5 3.5 0 0 1 5 5l-4 4a3.5 3.5 0 0 1-5 0"/></svg></span>
            <span><p class="quick-title">{{ $user->isSuperAdmin() ? 'Browse all URLs' : 'Manage short URLs' }}</p><p class="quick-copy">Open the dedicated URL directory and review click totals.</p></span>
        </a>

        @if ($user->isSuperAdmin())
            <a class="quick-card" href="{{ route('companies.index') }}">
                <span class="quick-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 21V7l8-4 8 4v14"/><path d="M8 10h2m4 0h2m-8 4h2m4 0h2M2 21h20"/></svg></span>
                <span><p class="quick-title">Manage companies</p><p class="quick-copy">Create companies and invite or review their administrators.</p></span>
            </a>
            <a class="quick-card" href="{{ route('super-admin.analytics') }}">
                <span class="quick-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 20V10m5 10V4m6 16v-7m5 7V7"/></svg></span>
                <span><p class="quick-title">Open analytics</p><p class="quick-copy">Filter companies, users, URLs and clicks in one report.</p></span>
            </a>
        @elseif ($user->isAdmin())
            <a class="quick-card" href="{{ route('team.index') }}">
                <span class="quick-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M16 20v-1.5a4.5 4.5 0 0 0-4.5-4.5h-5A4.5 4.5 0 0 0 2 18.5V20"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6m3-3h-6"/></svg></span>
                <span><p class="quick-title">Manage my team</p><p class="quick-copy">Create Admin or Member accounts and resend invitations.</p></span>
            </a>
            <a class="quick-card" href="{{ route('admin.analytics') }}">
                <span class="quick-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 20V10m5 10V4m6 16v-7m5 7V7"/></svg></span>
                <span><p class="quick-title">Open analytics</p><p class="quick-copy">Compare your URLs with users created by you.</p></span>
            </a>
        @endif
    </section>

    <div class="overview-grid">
        <section class="panel">
            <header class="panel-header">
                <div><h2 class="panel-title">Recent short URLs</h2><p class="panel-subtitle">The latest five links in your current scope.</p></div>
                <a class="button secondary" href="{{ route('short-urls.index') }}">View all URLs</a>
            </header>
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>Short URL</th><th>Destination</th>@if($user->isSuperAdmin())<th>Owner</th>@endif<th>Clicks</th></tr></thead>
                    <tbody>
                    @forelse ($recentShortUrls as $shortUrl)
                        <tr>
                            <td class="url-cell"><a class="short-link" href="{{ $shortUrl->shortUrl() }}" target="_blank" rel="noopener">{{ $shortUrl->shortUrl() }}</a></td>
                            <td class="url-cell"><a class="long-link" href="{{ $shortUrl->original_url }}" target="_blank" rel="noopener">{{ $shortUrl->original_url }}</a></td>
                            @if ($user->isSuperAdmin())<td><p class="person-name">{{ $shortUrl->user->name }}</p><p class="person-email">{{ $shortUrl->user->company?->name ?? 'No company' }}</p></td>@endif
                            <td><span class="number">{{ number_format($shortUrl->display_hits) }}</span></td>
                        </tr>
                    @empty
                        <tr><td class="empty-state" colspan="{{ $user->isSuperAdmin() ? 4 : 3 }}">No short URLs have been created yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <aside class="panel">
            <header class="panel-header"><div><h2 class="panel-title">Account details</h2><p class="panel-subtitle">Your current workspace identity.</p></div></header>
            <div class="panel-body">
                <dl class="profile-list">
                    <div class="profile-row"><dt class="profile-label">Name</dt><dd class="profile-value">{{ $user->name }}</dd></div>
                    <div class="profile-row"><dt class="profile-label">Email</dt><dd class="profile-value">{{ $user->email }}</dd></div>
                    <div class="profile-row"><dt class="profile-label">Role</dt><dd class="profile-value">{{ ucwords(str_replace('_', ' ', $user->role->value)) }}</dd></div>
                    @if ($user->company)<div class="profile-row"><dt class="profile-label">Company</dt><dd class="profile-value">{{ $user->company->name }}</dd></div>@endif
                </dl>
            </div>
        </aside>
    </div>
@endsection
