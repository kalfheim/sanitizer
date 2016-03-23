<?php declare(strict_types=1);

namespace Alfheim\Sanitizer;

use Alfheim\Sanitizer\{
    Sanitizer,
    Registrar\LaravelRegistrar,
    Registrar\RegistrarInterface
};

use Illuminate\{
    Support\ServiceProvider,
    Contracts\Container\Container,
    Contracts\Foundation\Application
};

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
            function (Container $app): RegistrarInterface {
                return new LaravelRegistrar($app);
            }
        );

        $this->app->bind(Sanitizer::class,
            function (Application $app): Sanitizer {
                return (new Sanitizer)->setRegistrar(
                    $app->make(RegistrarInterface::class)
                );
            }
        );
    }

    /**
     * {@inheritDoc}
     */
    public function provides(): array
    {
        return [
            Sanitizer::class,
            RegistrarInterface::class,
        ];
    }
}
