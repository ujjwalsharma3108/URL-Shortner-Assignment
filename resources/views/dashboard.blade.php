<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Dashboard · {{ config('app.name') }}</title>

    <style>
        :root {
            color-scheme: light;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: #182230;
            background: #f6f7fb;
        }

        * {
            box-sizing: border-box;
        }

        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        body {
            min-width: 320px;
            min-height: 100vh;
            margin: 0;
            background: #f6f7fb;
        }

        button {
            font: inherit;
        }

        .app-shell {
            display: grid;
            min-height: 100vh;
            grid-template-columns: 252px minmax(0, 1fr);
        }

        .sidebar {
            display: flex;
            min-height: 100vh;
            padding: 28px 18px 22px;
            border-right: 1px solid #e5e7eb;
            background: #fff;
            flex-direction: column;
        }

        .brand {
            display: block;
            width: 178px;
            margin: 0 8px 38px;
        }

        .brand img {
            display: block;
            width: 100%;
            height: auto;
        }

        .nav-label {
            margin: 0 12px 10px;
            color: #98a2b3;
            font-size: 11px;
            font-weight: 750;
            letter-spacing: .1em;
            text-transform: uppercase;
        }

        .nav {
            display: grid;
            gap: 6px;
        }

        .nav-item {
            display: flex;
            min-height: 46px;
            padding: 0 13px;
            border-radius: 10px;
            align-items: center;
            gap: 12px;
            color: #667085;
            font-size: 14px;
            font-weight: 650;
            text-decoration: none;
        }

        .nav-item svg {
            width: 20px;
            height: 20px;
            flex: 0 0 auto;
        }

        .nav-item.active {
            color: #4f46e5;
            background: #eef2ff;
        }

        .nav-item.muted {
            cursor: default;
            opacity: .55;
        }

        .sidebar-account {
            display: flex;
            margin-top: auto;
            padding: 18px 10px 0;
            border-top: 1px solid #eaecf0;
            align-items: center;
            gap: 11px;
        }

        .avatar {
            display: grid;
            width: 38px;
            height: 38px;
            border-radius: 11px;
            color: #fff;
            background: linear-gradient(135deg, #6366f1, #4338ca);
            flex: 0 0 auto;
            font-size: 14px;
            font-weight: 800;
            place-items: center;
        }

        .account-copy {
            min-width: 0;
        }

        .account-name,
        .account-email {
            overflow: hidden;
            margin: 0;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .account-name {
            color: #344054;
            font-size: 13px;
            font-weight: 750;
        }

        .account-email {
            margin-top: 2px;
            color: #98a2b3;
            font-size: 11px;
        }

        .main {
            min-width: 0;
        }

        .topbar {
            display: flex;
            min-height: 76px;
            padding: 0 36px;
            border-bottom: 1px solid #e7eaf0;
            background: rgba(255, 255, 255, .9);
            align-items: center;
            justify-content: space-between;
        }

        .page-title {
            margin: 0;
            font-size: 20px;
            letter-spacing: -.025em;
        }

        .top-actions {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .role-badge {
            padding: 7px 10px;
            border: 1px solid #d9d6fe;
            border-radius: 999px;
            color: #5925dc;
            background: #f4f3ff;
            font-size: 11px;
            font-weight: 750;
            text-transform: capitalize;
        }

        .logout-button {
            display: flex;
            height: 38px;
            padding: 0 14px;
            border: 1px solid #d0d5dd;
            border-radius: 9px;
            align-items: center;
            gap: 8px;
            color: #344054;
            background: #fff;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: border-color 150ms ease, color 150ms ease, background 150ms ease;
        }

        .logout-button:hover {
            border-color: #fda29b;
            color: #b42318;
            background: #fff7f6;
        }

        .logout-button svg {
            width: 17px;
            height: 17px;
        }

        .content {
            width: min(100%, 1320px);
            margin: 0 auto;
            padding: 36px;
        }

        .welcome {
            display: flex;
            margin-bottom: 28px;
            align-items: flex-end;
            justify-content: space-between;
            gap: 24px;
        }

        .welcome h1 {
            margin: 0;
            color: #101828;
            font-size: clamp(25px, 3vw, 34px);
            letter-spacing: -.04em;
        }

        .welcome p {
            margin: 8px 0 0;
            color: #667085;
            font-size: 14px;
        }

        .date {
            color: #98a2b3;
            font-size: 13px;
            white-space: nowrap;
        }

        .stats {
            display: grid;
            margin-bottom: 24px;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
        }

        .stat-card,
        .panel {
            border: 1px solid #e4e7ec;
            border-radius: 15px;
            background: #fff;
            box-shadow: 0 1px 2px rgba(16, 24, 40, .025);
        }

        .stat-card {
            display: flex;
            padding: 22px;
            align-items: center;
            gap: 16px;
        }

        .stat-icon {
            display: grid;
            width: 46px;
            height: 46px;
            border-radius: 12px;
            color: #4f46e5;
            background: #eef2ff;
            flex: 0 0 auto;
            place-items: center;
        }

        .stat-icon.sky {
            color: #0284c7;
            background: #f0f9ff;
        }

        .stat-icon.green {
            color: #039855;
            background: #ecfdf3;
        }

        .stat-icon svg {
            width: 22px;
            height: 22px;
        }

        .stat-value {
            margin: 0;
            color: #101828;
            font-size: 26px;
            font-weight: 780;
            letter-spacing: -.035em;
        }

        .stat-label {
            margin: 3px 0 0;
            color: #667085;
            font-size: 13px;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.65fr) minmax(260px, .75fr);
            gap: 18px;
        }

        .panel-header {
            display: flex;
            min-height: 66px;
            padding: 0 22px;
            border-bottom: 1px solid #eaecf0;
            align-items: center;
            justify-content: space-between;
        }

        .panel-title {
            margin: 0;
            color: #344054;
            font-size: 15px;
            font-weight: 750;
        }

        .panel-kicker {
            color: #98a2b3;
            font-size: 12px;
        }

        .empty-state {
            display: grid;
            min-height: 280px;
            padding: 36px 20px;
            text-align: center;
            place-items: center;
        }

        .empty-inner {
            max-width: 340px;
        }

        .empty-icon {
            display: grid;
            width: 56px;
            height: 56px;
            margin: 0 auto 17px;
            border: 7px solid #f5f3ff;
            border-radius: 50%;
            color: #6938ef;
            background: #ede9fe;
            place-items: center;
        }

        .empty-icon svg {
            width: 23px;
            height: 23px;
        }

        .empty-state h2 {
            margin: 0;
            color: #344054;
            font-size: 16px;
        }

        .empty-state p {
            margin: 8px 0 0;
            color: #667085;
            font-size: 13px;
            line-height: 1.6;
        }

        .profile-body {
            padding: 22px;
        }

        .profile-row {
            padding: 14px 0;
            border-bottom: 1px solid #f0f1f3;
        }

        .profile-row:first-child {
            padding-top: 0;
        }

        .profile-row:last-child {
            padding-bottom: 0;
            border-bottom: 0;
        }

        .profile-label {
            margin: 0 0 5px;
            color: #98a2b3;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .profile-value {
            overflow-wrap: anywhere;
            margin: 0;
            color: #344054;
            font-size: 13px;
            font-weight: 650;
        }

        .admin-management {
            margin-top: 18px;
            scroll-margin-top: 24px;
        }

        .admin-heading-copy {
            min-width: 0;
        }

        .admin-heading-copy p {
            margin: 4px 0 0;
            color: #667085;
            font-size: 12px;
        }

        .feedback {
            margin: 20px 22px 0;
            padding: 12px 14px;
            border-radius: 9px;
            font-size: 13px;
            line-height: 1.5;
        }

        .feedback.success {
            color: #067647;
            background: #ecfdf3;
        }

        .feedback.error {
            color: #b42318;
            background: #fef3f2;
        }

        .invite-section {
            display: grid;
            padding: 22px;
            border-bottom: 1px solid #eaecf0;
            grid-template-columns: minmax(210px, .65fr) minmax(0, 1.35fr);
            gap: 28px;
        }

        .invite-copy h3 {
            margin: 0;
            color: #344054;
            font-size: 14px;
        }

        .invite-copy p {
            margin: 7px 0 0;
            color: #667085;
            font-size: 12px;
            line-height: 1.6;
        }

        .invite-form {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .form-field label {
            display: block;
            margin-bottom: 7px;
            color: #344054;
            font-size: 12px;
            font-weight: 700;
        }

        .form-field input {
            width: 100%;
            height: 42px;
            padding: 0 12px;
            border: 1px solid #d0d5dd;
            border-radius: 9px;
            outline: none;
            color: #182230;
            background: #fff;
            font: inherit;
            font-size: 13px;
            transition: border-color 150ms ease, box-shadow 150ms ease;
        }

        .form-field input:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, .1);
        }

        .invite-submit {
            display: flex;
            grid-column: 1 / -1;
            justify-content: flex-end;
        }

        .primary-button,
        .table-action {
            border: 0;
            border-radius: 9px;
            font-weight: 700;
            cursor: pointer;
        }

        .primary-button {
            height: 42px;
            padding: 0 17px;
            color: #fff;
            background: #4f46e5;
            font-size: 13px;
        }

        .primary-button:hover {
            background: #4338ca;
        }

        .admin-table-wrap {
            overflow-x: auto;
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .admin-table th,
        .admin-table td {
            padding: 15px 22px;
            border-bottom: 1px solid #eaecf0;
            vertical-align: middle;
        }

        .admin-table th {
            color: #667085;
            background: #fcfcfd;
            font-size: 11px;
            font-weight: 750;
            letter-spacing: .03em;
            text-transform: uppercase;
        }

        .admin-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .admin-person-name,
        .admin-person-email {
            margin: 0;
        }

        .admin-person-name {
            color: #344054;
            font-size: 13px;
            font-weight: 750;
        }

        .admin-person-email,
        .invitation-date {
            margin-top: 3px;
            color: #98a2b3;
            font-size: 11px;
        }

        .status-pill {
            display: inline-flex;
            padding: 5px 9px;
            border-radius: 999px;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 750;
        }

        .status-pill::before {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
            content: '';
        }

        .status-pill.pending {
            color: #b54708;
            background: #fffaeb;
        }

        .status-pill.active {
            color: #067647;
            background: #ecfdf3;
        }

        .status-pill.expired {
            color: #b42318;
            background: #fef3f2;
        }

        .table-action {
            padding: 7px 10px;
            color: #4338ca;
            background: #eef2ff;
            font-size: 11px;
        }

        .table-action:hover {
            background: #e0e7ff;
        }

        .empty-admins {
            padding: 28px !important;
            color: #98a2b3;
            font-size: 13px;
            text-align: center;
        }

        @media (max-width: 980px) {
            .app-shell {
                grid-template-columns: 84px minmax(0, 1fr);
            }

            .sidebar {
                padding-inline: 13px;
            }

            .brand {
                width: 48px;
                margin-inline: 5px;
                overflow: hidden;
            }

            .brand img {
                width: 178px;
                max-width: none;
            }

            .nav-label,
            .nav-text,
            .account-copy {
                display: none;
            }

            .nav-item {
                justify-content: center;
            }

            .sidebar-account {
                justify-content: center;
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .invite-section {
                grid-template-columns: 1fr;
                gap: 18px;
            }
        }

        @media (max-width: 700px) {
            .app-shell {
                display: block;
            }

            .sidebar {
                display: flex;
                min-height: auto;
                padding: 12px 16px;
                border-right: 0;
                border-bottom: 1px solid #e5e7eb;
                align-items: center;
                flex-direction: row;
            }

            .brand {
                width: 43px;
                margin: 0 14px 0 0;
            }

            .brand img {
                width: 158px;
            }

            .nav {
                display: flex;
            }

            .nav-item {
                min-height: 42px;
            }

            .nav-item.muted,
            .sidebar-account {
                display: none;
            }

            .topbar {
                min-height: 66px;
                padding: 0 18px;
            }

            .role-badge {
                display: none;
            }

            .content {
                padding: 26px 18px;
            }

            .welcome {
                display: block;
            }

            .date {
                display: block;
                margin-top: 12px;
            }

            .stats {
                grid-template-columns: 1fr;
            }

            .invite-form {
                grid-template-columns: 1fr;
            }

            .admin-table th,
            .admin-table td {
                padding-inline: 16px;
            }

            .admin-table .invited-column {
                display: none;
            }
        }
    </style>
</head>
<body>
<div class="app-shell">
    <aside class="sidebar">
        <a class="brand" href="{{ route('dashboard') }}" aria-label="Sembark dashboard">
            <img src="{{ asset('images/sembark-logo.png') }}" alt="Sembark Travel Software">
        </a>

        <p class="nav-label">Workspace</p>
        <nav class="nav" aria-label="Main navigation">
            <a class="nav-item active" href="{{ route('dashboard') }}" aria-current="page">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path d="M3.5 10.5 12 3l8.5 7.5v9a1.5 1.5 0 0 1-1.5 1.5H5a1.5 1.5 0 0 1-1.5-1.5v-9Z"/>
                    <path d="M9 21v-6h6v6"/>
                </svg>
                <span class="nav-text">Dashboard</span>
            </a>
            @if ($user->isSuperAdmin())
                <a class="nav-item" href="#admin-management">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path d="M16 20v-1.5a4.5 4.5 0 0 0-4.5-4.5h-5A4.5 4.5 0 0 0 2 18.5V20"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M19 8v6m3-3h-6"/>
                    </svg>
                    <span class="nav-text">Admins</span>
                </a>
            @endif
            {{-- <span class="nav-item muted" aria-disabled="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path d="M9.5 14.5 14.5 9"/>
                    <path d="m7.2 17.8-1 .9a3.5 3.5 0 0 1-5-5l4-4a3.5 3.5 0 0 1 5 0"/>
                    <path d="m16.8 6.2 1-.9a3.5 3.5 0 0 1 5 5l-4 4a3.5 3.5 0 0 1-5 0"/>
                </svg>
                <span class="nav-text">My links</span>
            </span>
            <span class="nav-item muted" aria-disabled="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path d="M4 20V10m5 10V4m6 16v-7m5 7V7"/>
                </svg>
                <span class="nav-text">Analytics</span>
            </span> --}}
        </nav>

        <div class="sidebar-account">
            <div class="avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
            <div class="account-copy">
                <p class="account-name">{{ $user->name }}</p>
                <p class="account-email">{{ $user->email }}</p>
            </div>
        </div>
    </aside>

    <main class="main">
        <header class="topbar">
            <h2 class="page-title">Dashboard</h2>

            <div class="top-actions">
                <span class="role-badge">{{ str_replace('_', ' ', $user->role->value) }}</span>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button class="logout-button" type="submit">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path d="M10 5H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h5"/>
                            <path d="m16 17 5-5-5-5M21 12H9"/>
                        </svg>
                        Log out
                    </button>
                </form>
            </div>
        </header>

        <div class="content">
            <section class="welcome">
                <div>
                    <h1>Welcome back, {{ \Illuminate\Support\Str::before($user->name, ' ') }}!</h1>
                    <p>Here is an overview of your URL shortener workspace.</p>
                </div>
                <time class="date" datetime="{{ now()->toDateString() }}">{{ now()->format('l, d F Y') }}</time>
            </section>

            <section class="stats" aria-label="URL statistics">
                <article class="stat-card">
                    <div class="stat-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path d="M9.5 14.5 14.5 9"/>
                            <path d="m7.2 17.8-1 .9a3.5 3.5 0 0 1-5-5l4-4a3.5 3.5 0 0 1 5 0"/>
                            <path d="m16.8 6.2 1-.9a3.5 3.5 0 0 1 5 5l-4 4a3.5 3.5 0 0 1-5 0"/>
                        </svg>
                    </div>
                    <div>
                        <p class="stat-value">0</p>
                        <p class="stat-label">Total links</p>
                    </div>
                </article>

                <article class="stat-card">
                    <div class="stat-icon sky">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/>
                            <circle cx="12" cy="12" r="2.5"/>
                        </svg>
                    </div>
                    <div>
                        <p class="stat-value">0</p>
                        <p class="stat-label">Total clicks</p>
                    </div>
                </article>

                <article class="stat-card">
                    <div class="stat-icon green">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path d="M20 7 10 17l-5-5"/>
                        </svg>
                    </div>
                    <div>
                        <p class="stat-value">0</p>
                        <p class="stat-label">Active links</p>
                    </div>
                </article>
            </section>

            <div class="dashboard-grid">
                <section class="panel">
                    <header class="panel-header">
                        <h2 class="panel-title">Recent links</h2>
                        <span class="panel-kicker">Latest activity</span>
                    </header>
                    <div class="empty-state">
                        <div class="empty-inner">
                            <div class="empty-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path d="M9.5 14.5 14.5 9"/>
                                    <path d="m7.2 17.8-1 .9a3.5 3.5 0 0 1-5-5l4-4a3.5 3.5 0 0 1 5 0"/>
                                    <path d="m16.8 6.2 1-.9a3.5 3.5 0 0 1 5 5l-4 4a3.5 3.5 0 0 1-5 0"/>
                                </svg>
                            </div>
                            <h2>No shortened links yet</h2>
                            <p>Your recently created short URLs and their performance will appear here.</p>
                        </div>
                    </div>
                </section>

                <aside class="panel">
                    <header class="panel-header">
                        <h2 class="panel-title">Account details</h2>
                    </header>
                    <div class="profile-body">
                        <div class="profile-row">
                            <p class="profile-label">Name</p>
                            <p class="profile-value">{{ $user->name }}</p>
                        </div>
                        <div class="profile-row">
                            <p class="profile-label">Email</p>
                            <p class="profile-value">{{ $user->email }}</p>
                        </div>
                        <div class="profile-row">
                            <p class="profile-label">Role</p>
                            <p class="profile-value">{{ ucwords(str_replace('_', ' ', $user->role->value)) }}</p>
                        </div>
                    </div>
                </aside>
            </div>

            @if ($user->isSuperAdmin())
                <section class="panel admin-management" id="admin-management">
                    <header class="panel-header">
                        <div class="admin-heading-copy">
                            <h2 class="panel-title">Administrator access</h2>
                            <p>Create admin accounts and send secure email invitations.</p>
                        </div>
                        <span class="panel-kicker">{{ $admins->count() }} {{ \Illuminate\Support\Str::plural('admin', $admins->count()) }}</span>
                    </header>

                    @if (session('status'))
                        <div class="feedback success" role="status">{{ session('status') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="feedback error" role="alert">{{ $errors->first() }}</div>
                    @endif

                    <div class="invite-section">
                        <div class="invite-copy">
                            <h3>Invite a new admin</h3>
                            <p>The account is created in a pending state. An email with a secure password-setup link is added to the email queue.</p>
                        </div>

                        <form class="invite-form" action="{{ route('admin-invitations.store') }}" method="POST">
                            @csrf
                            <div class="form-field">
                                <label for="admin-name">Full name</label>
                                <input id="admin-name" name="name" type="text" value="{{ old('name') }}" placeholder="Admin name" maxlength="100" required>
                            </div>
                            <div class="form-field">
                                <label for="admin-email">Email address</label>
                                <input id="admin-email" name="email" type="email" value="{{ old('email') }}" placeholder="admin@example.com" required>
                            </div>
                            <div class="invite-submit">
                                <button class="primary-button" type="submit">Create admin &amp; queue invite</button>
                            </div>
                        </form>
                    </div>

                    <div class="admin-table-wrap">
                        <table class="admin-table">
                            <thead>
                            <tr>
                                <th>Administrator</th>
                                <th>Status</th>
                                <th class="invited-column">Invitation</th>
                                <th><span class="sr-only">Actions</span></th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse ($admins as $admin)
                                @php($invitation = $admin->adminInvitation)
                                <tr>
                                    <td>
                                        <p class="admin-person-name">{{ $admin->name }}</p>
                                        <p class="admin-person-email">{{ $admin->email }}</p>
                                    </td>
                                    <td>
                                        @if (! $invitation || $invitation->isAccepted())
                                            <span class="status-pill active">Active</span>
                                        @elseif ($invitation?->isExpired())
                                            <span class="status-pill expired">Expired</span>
                                        @else
                                            <span class="status-pill pending">Pending</span>
                                        @endif
                                    </td>
                                    <td class="invited-column">
                                        @if ($invitation?->isAccepted())
                                            <span class="invitation-date">Accepted {{ $invitation->accepted_at->diffForHumans() }}</span>
                                        @else
                                            <span class="invitation-date">Expires {{ $invitation?->expires_at?->diffForHumans() }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($invitation && ! $invitation->isAccepted())
                                            <form action="{{ route('admin-invitations.resend', $invitation) }}" method="POST">
                                                @csrf
                                                <button class="table-action" type="submit">Resend</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="empty-admins" colspan="4">No administrators have been invited yet.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            @endif
        </div>
    </main>
</div>
</body>
</html>
