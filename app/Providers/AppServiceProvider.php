<?php

namespace App\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Chiave temporanea usata quando APP_KEY è vuoto, così Laravel può avviarsi
     * e mostrare l'installer. Il middleware EnsureNotInstalled considera ancora
     * l'app "non installata" se la chiave è questa.
     */
    public const INSTALL_PLACEHOLDER_KEY = 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=';

    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (empty(Config::get('app.key'))) {
            Config::set('app.key', self::INSTALL_PLACEHOLDER_KEY);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (Config::get('app.key') === self::INSTALL_PLACEHOLDER_KEY || empty(Config::get('app.key'))) {
            Config::set('session.driver', 'file');
        }
    }
}
