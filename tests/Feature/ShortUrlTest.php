<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Company;
use App\Models\ShortUrl;
use App\Models\User;
use App\Services\ShortUrlCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Tests\TestCase;

class ShortUrlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::clear();
    }

    public function test_admins_and_members_can_create_short_urls(): void
    {
        $company = $this->company();

        foreach ([UserRole::Admin, UserRole::Member] as $role) {
            $user = User::factory()->create([
                'role' => $role,
                'company_id' => $company->id,
            ]);
            $this->authenticateAs($user);

            $this->post(route('short-urls.store'), [
                'original_url' => 'https://example.com/'.$role->value.'/long-path',
            ])->assertRedirect(route('short-urls.index'));

            $shortUrl = ShortUrl::where('user_id', $user->id)->firstOrFail();
            $this->assertSame('https://example.com/'.$role->value.'/long-path', $shortUrl->original_url);
            $this->assertTrue(Cache::has(app(ShortUrlCache::class)->urlKey($shortUrl->code)));
            $this->assertSame(0, Cache::get(app(ShortUrlCache::class)->hitsKey($shortUrl->id)));
        }
    }

    public function test_super_admin_cannot_create_short_urls(): void
    {
        $superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $this->authenticateAs($superAdmin);

        $this->post(route('short-urls.store'), [
            'original_url' => 'https://example.com/not-allowed',
        ])->assertForbidden();

        $this->assertDatabaseCount('short_urls', 0);
    }

    public function test_redirect_falls_back_to_database_and_tracks_hits_in_database_and_cache(): void
    {
        $user = User::factory()->create();
        $shortUrl = ShortUrl::create([
            'user_id' => $user->id,
            'code' => 'fallback',
            'original_url' => 'https://example.com/fallback-target',
        ]);
        $cache = app(ShortUrlCache::class);

        Cache::forget($cache->urlKey($shortUrl->code));
        Cache::forget($cache->hitsKey($shortUrl->id));

        $this->get(route('short-urls.redirect', $shortUrl->code))
            ->assertRedirect('https://example.com/fallback-target');

        $this->assertTrue(Cache::has($cache->urlKey($shortUrl->code)));
        $this->assertSame(1, $shortUrl->fresh()->hits);
        $this->assertSame(1, Cache::get($cache->hitsKey($shortUrl->id)));

        $this->get(route('short-urls.redirect', $shortUrl->code))
            ->assertRedirect('https://example.com/fallback-target');

        $this->assertSame(2, $shortUrl->fresh()->hits);
        $this->assertSame(2, Cache::get($cache->hitsKey($shortUrl->id)));
        $this->assertNotNull($shortUrl->fresh()->last_visited_at);
    }

    public function test_short_url_cache_expires_after_one_day(): void
    {
        config(['shortener.cache_ttl' => 86400]);
        $shortUrl = ShortUrl::create([
            'user_id' => User::factory()->create()->id,
            'code' => 'one-day',
            'original_url' => 'https://example.com/one-day',
        ]);
        $cache = app(ShortUrlCache::class);
        $cache->put($shortUrl);

        $this->assertTrue(Cache::has($cache->urlKey($shortUrl->code)));

        $this->travel(86401)->seconds();

        $this->assertFalse(Cache::has($cache->urlKey($shortUrl->code)));
        $this->assertFalse(Cache::has($cache->hitsKey($shortUrl->id)));
    }

    public function test_redirect_uses_cached_url_mapping_when_present(): void
    {
        $shortUrl = ShortUrl::create([
            'user_id' => User::factory()->create()->id,
            'code' => 'cached1',
            'original_url' => 'https://example.com/database-target',
        ]);
        $cache = app(ShortUrlCache::class);

        Cache::put($cache->urlKey($shortUrl->code), [
            'id' => $shortUrl->id,
            'original_url' => 'https://example.com/cached-target',
        ], 86400);

        $this->get(route('short-urls.redirect', $shortUrl->code))
            ->assertRedirect('https://example.com/cached-target');

        $this->assertSame(1, $shortUrl->fresh()->hits);
    }

    public function test_super_admin_sees_every_users_short_and_long_urls(): void
    {
        $company = $this->company();
        $superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $admin = User::factory()->create([
            'name' => 'URL Admin',
            'role' => UserRole::Admin,
            'company_id' => $company->id,
        ]);
        $member = User::factory()->create([
            'name' => 'URL Member',
            'role' => UserRole::Member,
            'company_id' => $company->id,
        ]);
        $first = $this->shortUrl($admin, 'admin01', 'https://example.com/admin-long-url', 4);
        $second = $this->shortUrl($member, 'member1', 'https://example.com/member-long-url', 7);
        app(ShortUrlCache::class)->put($first);
        app(ShortUrlCache::class)->put($second);
        $this->authenticateAs($superAdmin);

        $this->get(route('short-urls.index'))
            ->assertOk()
            ->assertSee($first->shortUrl())
            ->assertSee($first->original_url)
            ->assertSee($second->shortUrl())
            ->assertSee($second->original_url)
            ->assertSeeText('URL Admin')
            ->assertSeeText('URL Member')
            ->assertSeeText('Clicks')
            ->assertDontSeeText('DB hits')
            ->assertDontSeeText('Cache hits')
            ->assertDontSee('Create short URL');
    }

    public function test_regular_user_sees_only_their_own_urls(): void
    {
        $company = $this->company();
        $firstUser = User::factory()->create(['company_id' => $company->id]);
        $secondUser = User::factory()->create(['company_id' => $company->id]);
        $ownUrl = $this->shortUrl($firstUser, 'visible', 'https://example.com/visible');
        $otherUrl = $this->shortUrl($secondUser, 'hidden1', 'https://example.com/hidden');
        $this->authenticateAs($firstUser);

        $this->get(route('short-urls.index'))
            ->assertOk()
            ->assertSee($ownUrl->original_url)
            ->assertDontSee($otherUrl->original_url)
            ->assertSee('Create short URL');
    }

    private function company(): Company
    {
        return Company::create([
            'name' => 'URL Company '.Str::random(6),
            'slug' => 'url-company-'.Str::lower(Str::random(6)),
        ]);
    }

    private function shortUrl(User $user, string $code, string $url, int $hits = 0): ShortUrl
    {
        $shortUrl = ShortUrl::create([
            'user_id' => $user->id,
            'code' => $code,
            'original_url' => $url,
        ]);

        if ($hits > 0) {
            $shortUrl->forceFill(['hits' => $hits])->save();
        }

        return $shortUrl->fresh();
    }

    private function authenticateAs(User $user): void
    {
        $token = Auth::guard('api')->login($user);

        $this->withUnencryptedCookie('token', $token);
    }
}
