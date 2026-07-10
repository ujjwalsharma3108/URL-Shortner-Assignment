<x-mail::message>
# You have been invited

Hello {{ $invitation->user->name }},

{{ $invitation->inviter->name }} has invited you to join {{ $invitation->user->company->name }} as a {{ ucfirst($invitation->user->role->value) }}.

<x-mail::button :url="$invitationUrl">
Accept invitation
</x-mail::button>

This invitation expires on {{ $invitation->expires_at->format('d M Y \a\t h:i A T') }}. After accepting it, you will create your password and can sign in to {{ config('app.name') }} immediately.

If you were not expecting this invitation, you can safely ignore this email.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
