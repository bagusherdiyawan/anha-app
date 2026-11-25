<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Factory;

class FirebaseServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Factory::class, function ($app) {
            return (new Factory)->withServiceAccount('./storage/app/private/kopma-akprind-firebase-adminsdk-fbsvc-896a00b94c.json');
        });
    }
}