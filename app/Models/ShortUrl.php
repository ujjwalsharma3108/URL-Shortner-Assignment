<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShortUrl extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'code',
        'original_url',
    ];

    protected $casts = [
        'hits' => 'integer',
        'last_visited_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shortUrl(): string
    {
        return url('/s/'.$this->code);
    }
}
