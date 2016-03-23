<?php

namespace Alfheim\Sanitizer\Registrar;

use Illuminate\Contracts\Container\Container;

class LaravelRegistrar extends BaseRegistrar
{
    /** @var \Illuminate\Contracts\Container\Container */
    private $container;

    /**
     * Create a new Laravel registrar instance.
     *
     * @param  \Illuminate\Contracts\Container\Container  $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve($name)
    {
        $value = $this->registrations[$name];

        if (is_string($value)) {
            // Check for `class@method` format.
            if (strpos($value, '@') >= 1) {
                $segments = explode('@', $value, 2);

                if ($this->container->bound($segments[0])) {
                    return [
                        $this->container->make($segments[0]), $segments[1]
                    ];
                }
            }

            if ($this->container->bound($name)) {
                return $this->container->make($name);
            }
        }

        return parent::resolve($name);
    }
}
