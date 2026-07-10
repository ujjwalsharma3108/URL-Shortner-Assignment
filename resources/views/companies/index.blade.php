@extends('layouts.app')

@section('title', 'Companies')
@section('page-title', 'Companies')

@section('content')
    <section class="page-intro">
        <div><p class="eyebrow">Super Admin module</p><h1 class="page-heading">Company directory</h1><p class="page-description">Create companies, assign their first Admin, add more administrators, and review invitation status.</p></div>
        <span class="date-chip">{{ $companies->count() }} {{ \Illuminate\Support\Str::plural('company', $companies->count()) }}</span>
    </section>

    <section class="stats-grid three" aria-label="Company statistics">
        <x-stat-card label="Companies" :value="$companies->count()" />
        <x-stat-card label="Admins" :value="$companies->sum('admins_count')" tone="amber" />
        <x-stat-card label="Members" :value="$companies->sum('members_count')" tone="sky" />
    </section>

    <section class="panel">
        <header class="panel-header"><div><h2 class="panel-title">Company setup</h2><p class="panel-subtitle">Invitation emails are sent through the configured queue.</p></div></header>
        @include('companies.partials.forms')
    </section>

    @include('companies.partials.directory')
@endsection
