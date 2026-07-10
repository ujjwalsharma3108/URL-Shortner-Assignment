<header class="topbar">
    <h2 class="page-title">@yield('page-title', 'Dashboard')</h2>

    <div class="top-actions">
        <span class="role-badge header-role">{{ str_replace('_', ' ', $user->role->value) }}</span>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button class="logout-button" type="submit">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path d="M10 5H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h5" />
                    <path d="m16 17 5-5-5-5M21 12H9" />
                </svg>
                <span>Log out</span>
            </button>
        </form>
    </div>
</header>
