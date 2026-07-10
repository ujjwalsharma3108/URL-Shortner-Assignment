<?php

namespace App\Services;

use App\Models\ShortUrl;
use Illuminate\Support\Facades\Cache;

class ShortUrlCache
{
    /**
     * @return array{id: int, original_url: string}|null
     */
    public function resolve(string $code): ?array
    {
        $key = $this->urlKey($code);
        $cached = Cache::get($key);

        if (is_array($cached)) {
            return $cached;
        }

        $shortUrl = ShortUrl::query()
            ->where('code', $code)
            ->first(['id', 'code', 'original_url', 'hits']);

        if (! $shortUrl) {
            return null;
        }

        $this->put($shortUrl);

        return [
            'id' => $shortUrl->id,
            'original_url' => $shortUrl->original_url,
        ];
    }

    public function put(ShortUrl $shortUrl): void
    {
        Cache::put($this->urlKey($shortUrl->code), [
            'id' => $shortUrl->id,
            'original_url' => $shortUrl->original_url,
        ], $this->ttl());

        Cache::put($this->hitsKey($shortUrl->id), (int) $shortUrl->hits, $this->ttl());
    }

    public function recordHit(int $shortUrlId): int
    {
        return Cache::lock('short_url:hit_lock:'.$shortUrlId, 10)
            ->block(5, function () use ($shortUrlId) {
                ShortUrl::query()
                    ->whereKey($shortUrlId)
                    ->increment('hits', 1, ['last_visited_at' => now()]);

                $hits = (int) ShortUrl::query()
                    ->whereKey($shortUrlId)
                    ->value('hits');

                Cache::put($this->hitsKey($shortUrlId), $hits, $this->ttl());

                return $hits;
            });
    }

    public function hits(ShortUrl $shortUrl): int
    {
        return (int) Cache::remember(
            $this->hitsKey($shortUrl->id),
            $this->ttl(),
            fn () => (int) $shortUrl->hits,
        );
    }

    public function urlKey(string $code): string
    {
        return 'short_url:url:'.$code;
    }

    public function hitsKey(int $id): string
    {
        return 'short_url:hits:'.$id;
    }

    private function ttl(): int
    {
        return max(1, (int) config('shortener.cache_ttl', 86400));
    }
}
