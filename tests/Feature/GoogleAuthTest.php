<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VendorProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function mockGoogle(array $data = [], ?Provider $provider = null): void
    {
        $socialiteUser = (new SocialiteUser)->map(array_merge([
            'id' => 'google-123',
            'name' => 'مستخدم جوجل',
            'email' => 'google.user@test.local',
            'avatar' => null,
        ], $data));

        $provider ??= Mockery::mock(Provider::class);
        $provider->shouldReceive('user')->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }

    public function test_redirect_sends_user_to_google(): void
    {
        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('redirect')->once()->andReturn(redirect('https://accounts.google.com/o/oauth2/auth'));

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $this->get('/auth/google')->assertRedirect('https://accounts.google.com/o/oauth2/auth');
    }

    public function test_new_customer_is_created_from_google(): void
    {
        $this->mockGoogle();

        $this->get('/auth/google/callback?type=customer')->assertRedirect('/account');

        $user = User::where('email', 'google.user@test.local')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertTrue($user->hasRole('customer'));
        $this->assertSame('google', $user->provider);
        $this->assertSame('google-123', $user->provider_id);
    }

    public function test_vendor_google_signup_creates_pending_store(): void
    {
        $this->mockGoogle(['email' => 'google.vendor@test.local', 'name' => 'بائع جوجل']);

        // نوع الحساب يُحفظ في الجلسة عند الضغط على زر جوجل
        $this->withSession(['google_account_type' => 'vendor'])
            ->get('/auth/google/callback')
            ->assertRedirect('/account');

        $user = User::where('email', 'google.vendor@test.local')->firstOrFail();
        $this->assertTrue($user->hasRole('vendor'));

        $store = VendorProfile::where('user_id', $user->id)->firstOrFail();
        $this->assertFalse($store->is_active);
    }

    public function test_existing_account_is_linked_and_logged_in(): void
    {
        $existing = User::factory()->create(['email' => 'google.user@test.local']);

        $this->mockGoogle();

        $this->get('/auth/google/callback')->assertRedirect('/');

        $existing->refresh();
        $this->assertAuthenticatedAs($existing);
        $this->assertSame('google', $existing->provider);
        $this->assertSame(1, User::count());
    }

    public function test_google_avatar_is_downloaded_and_stored(): void
    {
        Storage::fake('public');
        Http::fake(['*' => Http::response('fake-image-bytes', 200)]);

        $this->mockGoogle(['email' => 'avatar.user@test.local', 'avatar' => 'https://lh3.googleusercontent.com/a/photo.jpg']);

        $this->get('/auth/google/callback')->assertRedirect('/account');

        $user = User::where('email', 'avatar.user@test.local')->firstOrFail();
        $this->assertNotNull($user->avatar);
        Storage::disk('public')->assertExists($user->avatar);
    }

    public function test_google_failure_redirects_to_login_with_error(): void
    {
        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')->andThrow(new \RuntimeException('invalid_grant'));

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $this->get('/auth/google/callback')
            ->assertRedirect('/login')
            ->assertSessionHas('error');
    }
}
