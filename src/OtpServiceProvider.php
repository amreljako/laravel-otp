<?php
namespace Amreljako\Otp;

use Illuminate\Support\ServiceProvider;
use Amreljako\Otp\Services\OtpManager;

class OtpServiceProvider extends ServiceProvider {
    public function register(): void {
        $this->mergeConfigFrom(__DIR__.'/../config/otp.php', 'otp');
        $this->app->singleton('otp.manager', fn() => new OtpManager());
    }
    public function boot(): void {
        $this->publishes([__DIR__.'/../config/otp.php' => config_path('otp.php')], 'otp-config');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
