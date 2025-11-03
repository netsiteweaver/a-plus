<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->away($this->buildRedirectUrl('already-verified'));
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect()->away($this->buildRedirectUrl('email-verified'));
    }

    protected function buildRedirectUrl(string $status): string
    {
        $baseUrl = rtrim((string) config('app.frontend_url'), '/');
        $path = Str::start((string) config('app.frontend_auth_redirect_path', '/auth/callback'), '/');
        $fragment = http_build_query([
            'status' => $status,
        ], '', '&', PHP_QUERY_RFC3986);

        return $baseUrl.$path.'#'.$fragment;
    }
}
