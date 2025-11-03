<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_receive_token_when_logging_in(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
            'device_name' => 'phpunit',
        ]);

        $this->assertAuthenticated();

        $response
            ->assertOk()
            ->assertJsonStructure([
                'token',
                'token_type',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'roles',
                    'permissions',
                ],
            ]);

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => User::class,
            'name' => 'phpunit',
        ]);
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();

        $response->assertStatus(422)->assertJsonValidationErrors('email');
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $token = $user->createToken('phpunit-logout');

        $response = $this
            ->withHeader('Authorization', 'Bearer '.$token->plainTextToken)
            ->postJson('/api/logout', [
                'device_name' => 'phpunit-logout',
            ]);

        $response->assertOk()->assertJson(['status' => 'logged-out']);

        $this->assertNull(
            PersonalAccessToken::find(optional($token->accessToken)->id)
        );
    }
}
