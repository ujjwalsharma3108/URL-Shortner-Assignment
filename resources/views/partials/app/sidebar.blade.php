<aside class="sidebar">
    <a class="brand" href="{{ route('dashboard') }}" aria-label="Sembark dashboard">
        <img src="{{ asset('images/sembark-logo.png') }}" alt="Sembark Travel Software">
    </a>

    <div>
        <p class="nav-label">Workspace</p>
        <nav class="nav" aria-label="Main navigation">
            <a class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path d="M3.5 10.5 12 3l8.5 7.5v9a1.5 1.5 0 0 1-1.5 1.5H5a1.5 1.5 0 0 1-1.5-1.5v-9Z" />
                    <path d="M9 21v-6h6v6" />
                </svg>
                <span class="nav-text">Dashboard</span>
            </a>

            <a class="nav-item {{ request()->routeIs('short-urls.*') ? 'active' : '' }}" href="{{ route('short-urls.index') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path d="M9.5 14.5 14.5 9" />
                    <path d="m7.2 17.8-1 .9a3.5 3.5 0 0 1-5-5l4-4a3.5 3.5 0 0 1 5 0" />
                    <path d="m16.8 6.2 1-.9a3.5 3.5 0 0 1 5 5l-4 4a3.5 3.5 0 0 1-5 0" />
                </svg>
                <span class="nav-text">Short URLs</span>
            </a>

            @if ($user->isSuperAdmin() || $user->isAdmin())
                <a
                    class="nav-item {{ request()->routeIs('super-admin.analytics', 'admin.analytics') ? 'active' : '' }}"
                    href="{{ route($user->isSuperAdmin() ? 'super-admin.analytics' : 'admin.analytics') }}"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path d="M4 20V10m5 10V4m6 16v-7m5 7V7" />
                    </svg>
                    <span class="nav-text">Analytics</span>
                </a>
            @endif

            @if ($user->isSuperAdmin())
                <a class="nav-item {{ request()->routeIs('companies.*') ? 'active' : '' }}" href="{{ route('companies.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path d="M4 21V7l8-4 8 4v14" />
                        <path d="M8 10h2m4 0h2m-8 4h2m4 0h2m-5 7v-3h2v3M2 21h20" />
                    </svg>
                    <span class="nav-text">Companies</span>
                </a>
            @elseif ($user->isAdmin())
                <a class="nav-item {{ request()->routeIs('team.*') ? 'active' : '' }}" href="{{ route('team.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path d="M16 20v-1.5a4.5 4.5 0 0 0-4.5-4.5h-5A4.5 4.5 0 0 0 2 18.5V20" />
                        <circle cx="9" cy="7" r="4" />
                        <path d="M19 8v6m3-3h-6" />
                    </svg>
                    <span class="nav-text">My Team</span>
                </a>
            @endif
        </nav>
    </div>

    <div class="sidebar-account">
        <div class="avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
        <div class="account-copy">
            <p class="account-name">{{ $user->name }}</p>
            <p class="account-email">{{ $user->email }}</p>
        </div>
    </div>
</aside>
