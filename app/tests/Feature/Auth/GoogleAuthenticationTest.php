<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;

class GoogleAuthenticationTest extends TestCase
{
    use RefreshDatabase;
    use MockeryPHPUnitIntegration;

    public function test_google_callback_creates_user_and_redirects_with_token(): void
    {
        Config::set('app.frontend_url', 'http://frontend.test');
        Config::set('app.frontend_auth_redirect_path', '/auth/callback');

        $socialiteUser = (new SocialiteUser())
            ->setRaw([
                'sub' => 'google-id',
                'email' => 'new-user@example.com',
                'name' => 'Google User',
                'picture' => 'http://avatar.test/avatar.png',
            ])
            ->map([
                'id' => 'google-id',
                'email' => 'new-user@example.com',
                'name' => 'Google User',
                'avatar' => 'http://avatar.test/avatar.png',
            ]);

        $providerMock = Mockery::mock(Provider::class);
        $providerMock->shouldReceive('stateless')->andReturnSelf();
        $providerMock->shouldReceive('user')->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturn($providerMock);

        $response = $this->get('/api/auth/google/callback');

        $response->assertRedirect();

        $location = $response->headers->get('Location');
        $this->assertNotNull($location);
        $this->assertStringStartsWith('http://frontend.test/auth/callback#', $location);

        parse_str(parse_url($location, PHP_URL_FRAGMENT) ?? '', $payload);

        $this->assertSame('Bearer', $payload['token_type'] ?? null);
        $this->assertSame('registered', $payload['status'] ?? null);
        $this->assertArrayHasKey('token', $payload);

        $user = User::where('email', 'new-user@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('customer'));
        $this->assertSame('google', $user->provider);
        $this->assertSame('google-id', $user->provider_id);

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => User::class,
            'name' => 'google',
        ]);
    }

    public function test_google_callback_can_return_json_response(): void
    {
        $existingUser = User::factory()->create([
            'email' => 'existing@example.com',
        ]);

        $socialiteUser = (new SocialiteUser())
            ->setRaw([
                'sub' => 'existing-google-id',
                'email' => 'existing@example.com',
                'name' => 'Existing Google User',
                'picture' => 'http://avatar.test/google.png',
            ])
            ->map([
                'id' => 'existing-google-id',
                'email' => 'existing@example.com',
                'name' => 'Existing Google User',
                'avatar' => 'http://avatar.test/google.png',
            ]);

        $providerMock = Mockery::mock(Provider::class);
        $providerMock->shouldReceive('stateless')->andReturnSelf();
        $providerMock->shouldReceive('user')->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturn($providerMock);

        $response = $this
            ->withHeader('Accept', 'application/json')
            ->get('/api/auth/google/callback?device_name=spa-browser');

        $response
            ->assertOk()
            ->assertJson([
                'token_type' => 'Bearer',
                'status' => 'authenticated',
            ])
            ->assertJsonStructure([
                'token',
                'token_type',
                'status',
                'user' => [
                    'id',
                    'email',
                ],
            ]);

        $existingUser->refresh();

        $this->assertSame('google', $existingUser->provider);
        $this->assertSame('existing-google-id', $existingUser->provider_id);
        $this->assertEquals('http://avatar.test/google.png', $existingUser->avatar_url);

        $token = PersonalAccessToken::where('tokenable_id', $existingUser->id)
            ->where('name', 'spa-browser')
            ->first();

        $this->assertNotNull($token);
    }
}
