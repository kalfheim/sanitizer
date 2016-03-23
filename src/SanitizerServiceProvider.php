<?php

namespace Alfheim\Sanitizer;

use Alfheim\Sanitizer\Sanitizer;
use Alfheim\Sanitizer\Registrar\LaravelRegistrar;
use Alfheim\Sanitizer\Registrar\RegistrarInterface;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Foundation\Application;

class SanitizerServiceProvider extends ServiceProvider
{
    /** {@inheritDoc} */
    protected $defer = true;

    /**
     * {@inheritDoc}
     */
    public function register()
    {
        $this->app->singleton(RegistrarInterface::class,
            function (Container $app) {
                return new LaravelRegistrar($app);
            }
        );

        $this->app->bind(Sanitizer::class,
            function (Application $app) {
                return (new Sanitizer)->setRegistrar(
                    $app->make(RegistrarInterface::class)
                );
            }
        );
    }

    /**
     * {@inheritDoc}
     */
    public function provides()
    {
        return [
            Sanitizer::class,
            RegistrarInterface::class,
        ];
    }
}
