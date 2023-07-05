<?php

namespace Caimari\FManager;

use Illuminate\Support\ServiceProvider;

class FManagerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Registrar la lógica de tu paquete aquí
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/resources/views', 'fmanager');
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
    }
}

