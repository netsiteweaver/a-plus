<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $frontendUrl = rtrim((string) config('app.frontend_url'), '/');

        ResetPassword::createUrlUsing(function (object $notifiable, string $token) use ($frontendUrl) {
            return $frontendUrl."/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });

        $this->registerConsoleCommands();
    }

    protected function registerConsoleCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Console\Commands\ImportWooCommerceProducts::class,
            ]);
        }
    }
}
