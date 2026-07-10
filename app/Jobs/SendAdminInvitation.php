<?php

namespace App\Jobs;

use App\Mail\AdminInvitationMail;
use App\Models\AdminInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;

class SendAdminInvitation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [60, 300, 900];

    private string $encryptedToken;

    public function __construct(
        public AdminInvitation $invitation,
        string $token,
    ) {
        $this->encryptedToken = Crypt::encryptString($token);
        $this->onQueue('emails');
        $this->afterCommit();
    }

    public function handle(): void
    {
        $this->invitation->refresh()->loadMissing(['user', 'inviter']);
        $token = $this->token();

        if (! $this->invitation->isPending()
            || ! hash_equals($this->invitation->token_hash, hash('sha256', $token))) {
            return;
        }

        Mail::to($this->invitation->user->email)->send(
            new AdminInvitationMail($this->invitation, $this->invitationUrl()),
        );
    }

    public function invitationUrl(): string
    {
        return route('admin-invitations.accept', ['token' => $this->token()]);
    }

    private function token(): string
    {
        return Crypt::decryptString($this->encryptedToken);
    }
}
