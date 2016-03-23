<?php

namespace Alfheim\Sanitizer;

use Alfheim\Sanitizer\Registrar\LaravelRegistrar;
use Alfheim\Sanitizer\Registrar\RegistrarInterface;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Foundation\Application;

class SanitizerServiceProvider extends ServiceProvider
{
    /** {@inheritdoc} */
    protected $defer = true;

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function provides()
    {
        return [
            Sanitizer::class,
            RegistrarInterface::class,
        ];
    }
}
