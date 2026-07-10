@extends('layouts.app')

@section('title', 'My Team')
@section('page-title', 'My Team')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">Admin module</p>
            <h1 class="page-heading">Manage your team</h1>
            <p class="page-description">
                Create Admin or Member accounts for {{ $user->company?->name ?? 'your company' }}.
                Only users created by you are shown here.
            </p>
        </div>
        <span class="date-chip">
            {{ $managedUsers->count() }} {{ \Illuminate\Support\Str::plural('user', $managedUsers->count()) }}
        </span>
    </section>

    <section class="stats-grid three" aria-label="Team statistics">
        <x-stat-card label="Managed users" :value="$teamStats['users']" />
        <x-stat-card label="Admins" :value="$teamStats['admins']" tone="amber" />
        <x-stat-card label="Members" :value="$teamStats['members']" tone="sky" />
    </section>

    <section class="panel">
        <header class="panel-header">
            <div>
                <h2 class="panel-title">Create team user</h2>
                <p class="panel-subtitle">A queued invitation lets the new user set their password.</p>
            </div>
        </header>

        <div class="form-section">
            <div class="form-copy">
                <h3>Invite an Admin or Member</h3>
                <p>Both roles can create short URLs. Admins can also create users within their own scope.</p>
            </div>

            <form class="form-grid" action="{{ route('admin-invitations.store') }}" method="POST">
                @csrf
                <div class="field">
                    <label for="team-user-name">Full name</label>
                    <input
                        id="team-user-name"
                        name="name"
                        type="text"
                        value="{{ old('name') }}"
                        placeholder="Full name"
                        maxlength="100"
                        required
                    >
                </div>
                <div class="field">
                    <label for="team-user-email">Email address</label>
                    <input
                        id="team-user-email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        placeholder="user@example.com"
                        required
                    >
                </div>
                <div class="field">
                    <label for="team-user-role">Role</label>
                    <select id="team-user-role" name="role" required>
                        <option value="admin" @selected(old('role') === 'admin')>Admin</option>
                        <option value="member" @selected(old('role') === 'member')>Member</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button class="button" type="submit">Create user &amp; queue invite</button>
                </div>
            </form>
        </div>
    </section>

    <section class="panel">
        <header class="panel-header">
            <div>
                <h2 class="panel-title">Team directory</h2>
                <p class="panel-subtitle">Role and invitation status for users created by you.</p>
            </div>
            <span class="count-chip">{{ $managedUsers->count() }} users</span>
        </header>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                <tr>
                    <th>User</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th><span class="sr-only">Actions</span></th>
                </tr>
                </thead>
                <tbody>
                @forelse ($managedUsers as $managedUser)
                    @php($invitation = $managedUser->adminInvitation)
                    <tr>
                        <td>
                            <p class="person-name">{{ $managedUser->name }}</p>
                            <p class="person-email">{{ $managedUser->email }}</p>
                        </td>
                        <td>
                            <span class="role-badge">{{ ucfirst($managedUser->role->value) }}</span>
                        </td>
                        <td>
                            @if (! $invitation || $invitation->isAccepted())
                                <span class="status-pill active">Active</span>
                            @elseif ($invitation->isExpired())
                                <span class="status-pill expired">Expired</span>
                            @else
                                <span class="status-pill pending">Pending</span>
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
                        <td class="empty-state" colspan="4">You have not created any users yet.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
