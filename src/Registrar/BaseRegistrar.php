<?php declare(strict_types=1);

namespace Alfheim\Sanitizer\Registrar;

use InvalidArgumentException;

class BaseRegistrar implements RegistrarInterface
{
    /** @var array */
    protected $registrations = [];

    /**
     * {@inheritDoc}
     */
    public function register(string $name, $sanitizer): bool
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
    public function isRegistred(string $name): bool
    {
        return isset($this->registrations[$name]);
    }

    /**
     * {@inheritDoc}
     */
    public function resolve(string $name)
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
