<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Kecamatan;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        error_reporting(E_ALL & ~E_DEPRECATED);

        if ($this->app->isLocal()) {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer(['layouts.base', 'layouts.sidebar'], function ($view) {
            $logo = '/assets/img/logo.jpeg'; // default

            $kec = Kecamatan::where('id', session('lokasi'))->first();
            if ($kec && $kec->logo) {
                $logo = '/storage/logo/' . $kec->logo;
            }

            $view->with('logo', $logo);
        });
    }
}
