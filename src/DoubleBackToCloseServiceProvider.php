<?php

namespace Codingwithrk\DoubleBackToClose;

use Illuminate\Support\ServiceProvider;
use Codingwithrk\DoubleBackToClose\Commands\CopyAssetsCommand;

class DoubleBackToCloseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DoubleBackToClose::class, function () {
            return new DoubleBackToClose();
        });
    }

    public function boot(): void
    {
        // Register plugin hook commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                CopyAssetsCommand::class,
            ]);
        }
    }
}