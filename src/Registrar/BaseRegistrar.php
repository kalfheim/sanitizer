<?php

namespace Alfheim\Sanitizer\Registrar;

use InvalidArgumentException;

class BaseRegistrar implements RegistrarInterface
{
    /** @var array */
    protected $registrations = [];

    /**
     * {@inheritDoc}
     */
    public function register($name, $sanitizer)
    {
        if ($this->isRegistred($name)) {
            return false;
        }

        $this->registrations[$name] = $sanitizer;

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function isRegistred($name)
    {
        return isset($this->registrations[$name]);
    }

    /**
     * {@inheritDoc}
     */
    public function resolve($name)
    {
        if (
            $this->isRegistred($name) &&
            is_callable($this->registrations[$name])
        ) {
            return $this->registrations[$name];
        }

        throw new InvalidArgumentException(sprintf(
            'Could not resolve [%s] from the registrar.', $name
        ));
    }
}
