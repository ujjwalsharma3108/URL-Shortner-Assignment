@extends('layouts.app')

@section('title', 'Short URLs')
@section('page-title', 'Short URLs')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">URL module</p>
            <h1 class="page-heading">{{ $user->isSuperAdmin() ? 'All short URLs' : 'My short URLs' }}</h1>
            <p class="page-description">{{ $user->isSuperAdmin() ? 'Review every user’s destination URL and consolidated click count.' : 'Create compact links and monitor their consolidated click count.' }}</p>
        </div>
        <span class="date-chip">Cache TTL · 1 day</span>
    </section>

    <section class="stats-grid compact" aria-label="URL statistics">
        <x-stat-card label="Total links" :value="$urlStats['links']" />
        <x-stat-card label="Total clicks" :value="$urlStats['hits']" tone="sky" />
    </section>

    @unless ($user->isSuperAdmin())
        <section class="panel">
            <header class="panel-header"><div><h2 class="panel-title">Create short URL</h2><p class="panel-subtitle">HTTP and HTTPS destinations are supported.</p></div></header>
            <div class="form-section">
                <div class="form-copy"><h3>Shorten a destination</h3><p>The new mapping is written to MySQL and cached immediately for fast redirects.</p></div>
                <form class="form-grid single" action="{{ route('short-urls.store') }}" method="POST">
                    @csrf
                    <div class="field"><label for="original-url">Long URL</label><input id="original-url" name="original_url" type="url" value="{{ old('original_url') }}" placeholder="https://example.com/your-long-url" maxlength="2048" required></div>
                    <div class="form-actions"><button class="button" type="submit">Create short URL</button></div>
                </form>
            </div>
        </section>
    @endunless

    <section class="panel">
        <header class="panel-header"><div><h2 class="panel-title">URL directory</h2><p class="panel-subtitle">Cache-first redirects with database fallback.</p></div><span class="count-chip">{{ $shortUrls->count() }} links</span></header>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Short URL</th><th>Destination</th>@if($user->isSuperAdmin())<th>Owner</th><th>Role</th>@endif<th>Clicks</th></tr></thead>
                <tbody>
                @forelse($shortUrls as $shortUrl)
                    <tr>
                        <td class="url-cell"><a class="short-link" href="{{ $shortUrl->shortUrl() }}" target="_blank" rel="noopener">{{ $shortUrl->shortUrl() }}</a></td>
                        <td class="url-cell"><a class="long-link" href="{{ $shortUrl->original_url }}" target="_blank" rel="noopener" title="{{ $shortUrl->original_url }}">{{ $shortUrl->original_url }}</a></td>
                        @if($user->isSuperAdmin())
                            <td><p class="person-name">{{ $shortUrl->user->name }}</p><p class="person-email">{{ $shortUrl->user->email }} · {{ $shortUrl->user->company?->name ?? 'No company' }}</p></td>
                            <td><span class="table-role {{ $shortUrl->user->isMember() ? 'member' : '' }}">{{ ucfirst($shortUrl->user->role->value) }}</span></td>
                        @endif
                        <td><span class="number">{{ number_format($shortUrl->display_hits) }}</span></td>
                    </tr>
                @empty
                    <tr><td class="empty-state" colspan="{{ $user->isSuperAdmin() ? 5 : 3 }}">No short URLs have been created yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
