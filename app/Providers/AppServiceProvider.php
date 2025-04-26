<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Carbon::setLocale('pt_BR');
    
        // Windows (localhost)
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            setlocale(LC_TIME, 'Portuguese_Brazil.1252');
        } else {
            // Linux (produção)
            setlocale(LC_TIME, 'pt_BR.utf8');
        }
    }
}
