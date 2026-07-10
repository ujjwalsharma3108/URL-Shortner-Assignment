@extends('layouts.app')

@section('title', $pageTitle)
@section('page-title', $pageTitle)

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">Performance report</p>
            <h1 class="page-heading">{{ $pageTitle }}</h1>
            <p class="page-description">{{ $pageSubtitle }}</p>
        </div>
        <time class="date-chip" datetime="{{ now()->toDateString() }}">{{ now()->format('d M Y') }}</time>
    </section>

    @include('analytics.partials.filters')

    <section class="stats-grid" aria-label="Filtered statistics">
        <x-stat-card label="Visible users" :value="$stats['users']" />
        <x-stat-card label="Admins" :value="$stats['admins']" tone="amber" />
        <x-stat-card label="Members" :value="$stats['members']" tone="sky" />
        <x-stat-card label="Short URLs" :value="$stats['links']" tone="green" />
        <x-stat-card label="Total clicks" :value="$stats['clicks']" />
    </section>

    <div class="section-stack">
        @include('analytics.partials.users-table')
        @include('analytics.partials.urls-table')
    </div>
@endsection
