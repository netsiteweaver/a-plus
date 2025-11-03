<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): JsonResponse
    {
        $request->authenticate();

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        $user = $request->user();

        $deviceName = $request->input('device_name');

        if (! is_string($deviceName) || $deviceName === '') {
            $deviceName = $request->userAgent() ?: 'web';
        }

        $deviceName = Str::limit($deviceName, 255, '');

        $user?->tokens()->where('name', $deviceName)->delete();

        $token = $user?->createToken($deviceName);

        return response()->json([
            'token' => $token?->plainTextToken,
            'token_type' => 'Bearer',
            'user' => $user !== null ? new UserResource($user) : null,
        ]);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): JsonResponse
    {
        $user = $request->user();

        $currentAccessToken = $user?->currentAccessToken();

        if ($currentAccessToken !== null && method_exists($currentAccessToken, 'delete')) {
            $currentAccessToken->delete();
        }

        if ($request->filled('device_name') && $user !== null) {
            $user->tokens()->where('name', $request->input('device_name'))->delete();
        }

        Auth::guard('web')->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();

            $request->session()->regenerateToken();
        }

        return response()->json(['status' => 'logged-out']);
    }
}
