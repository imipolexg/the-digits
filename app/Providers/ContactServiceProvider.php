<?php

namespace App\Providers;

use App\ContactRepository;
use App\ContactRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class ContactServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            ContactRepositoryInterface::class,
            function () { return new ContactRepository(); }
        );
    }
}
