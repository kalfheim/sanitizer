<?php declare(strict_types=1);

namespace Alfheim\Sanitizer\Registrar;

interface RegistrarInterface
{
    /**
     * Register a sanitizer function with the registrar.
     *
     * @param  string  $name
     * @param  mixed   $sanitizer
     *
     * @return bool
     */
    public function register(string $name, $sanitizer): bool;

    /**
     * Check if a given name is registred.
     *
     * @param  string  $name
     *
     * @return bool
     */
    public function isRegistred(string $name): bool;

    /**
     * Resolve a callable or an object from the registrations.
     *
     * @param  string  $name
     *
     * @return callable|object
     *
     * @throws \InvalidArgumentException
     */
    public function resolve(string $name);
}
