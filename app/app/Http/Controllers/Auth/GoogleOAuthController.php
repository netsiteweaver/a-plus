<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\Response;

class GoogleOAuthController extends Controller
{
    /**
     * Redirect the user to Google's OAuth consent screen.
     */
    public function redirect(Request $request): RedirectResponse
    {
        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->with(['prompt' => 'select_account'])
            ->stateless()
            ->redirect();
    }

    /**
     * Obtain the user information from Google.
     */
    public function callback(Request $request): RedirectResponse|JsonResponse
    {
        try {
            $googleUser = Socialite::driver('google')
                ->stateless()
                ->user();
        } catch (\Throwable $exception) {
            Log::warning('Google OAuth callback failed', [
                'exception' => $exception->getMessage(),
            ]);

            return $this->errorResponse($request, 'Unable to authenticate with Google. Please try again.');
        }

        $googleId = $googleUser->getId();
        $googleEmail = $googleUser->getEmail();

        if ($googleId === null || $googleEmail === null) {
            return $this->errorResponse($request, 'Google account is missing a required identifier or email.');
        }

        $user = User::query()
            ->where('provider', 'google')
            ->where('provider_id', $googleId)
            ->first();

        if ($user === null) {
            $user = User::query()
                ->where('email', $googleEmail)
                ->first();
        }

        $isNewUser = false;

        if ($user === null) {
            $user = User::create([
                'name' => $googleUser->getName() ?: $googleEmail,
                'email' => $googleEmail,
                'password' => Hash::make(Str::random(40)),
                'provider' => 'google',
                'provider_id' => $googleId,
                'avatar_url' => $googleUser->getAvatar(),
                'email_verified_at' => now(),
            ]);

            $user->assignRole('customer');

            event(new Registered($user));

            $isNewUser = true;
        } else {
            $updates = [
                'provider' => 'google',
                'provider_id' => $googleId,
                'avatar_url' => $googleUser->getAvatar(),
            ];

            if (! $user->hasVerifiedEmail()) {
                $updates['email_verified_at'] = now();
            }

            if (empty($user->name) && $googleUser->getName()) {
                $updates['name'] = $googleUser->getName();
            }

            $user->forceFill($updates)->save();
        }

        Auth::login($user);

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        $deviceName = $request->query('device_name');

        if (! is_string($deviceName) || $deviceName === '') {
            $deviceName = 'google';
        }

        $deviceName = Str::limit($deviceName, 255, '');

        $user->tokens()->where('name', $deviceName)->delete();

        $token = $user->createToken($deviceName);

        if ($request->expectsJson()) {
            return response()->json([
                'token' => $token->plainTextToken,
                'token_type' => 'Bearer',
                'status' => $isNewUser ? 'registered' : 'authenticated',
                'user' => new UserResource($user),
            ]);
        }

        return $this->successRedirect(
            token: $token->plainTextToken,
            status: $isNewUser ? 'registered' : 'authenticated'
        );
    }

    /**
     * Build a redirect response for successful authentication.
     */
    protected function successRedirect(string $token, string $status): RedirectResponse
    {
        $baseUrl = rtrim((string) config('app.frontend_url'), '/');
        $path = Str::start((string) config('app.frontend_auth_redirect_path', '/auth/callback'), '/');

        $fragment = http_build_query([
            'token' => $token,
            'token_type' => 'Bearer',
            'status' => $status,
        ], '', '&', PHP_QUERY_RFC3986);

        return redirect()->away($baseUrl.$path.'#'.$fragment);
    }

    /**
     * Build either a JSON or redirect response when authentication fails.
     */
    protected function errorResponse(Request $request, string $message): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $baseUrl = rtrim((string) config('app.frontend_url'), '/');
        $path = Str::start((string) config('app.frontend_auth_redirect_path', '/auth/callback'), '/');

        $fragment = http_build_query([
            'error' => 'oauth_failed',
            'message' => $message,
        ], '', '&', PHP_QUERY_RFC3986);

        return redirect()->away($baseUrl.$path.'#'.$fragment);
    }
}
