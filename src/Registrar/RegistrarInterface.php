<?php

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
    public function register($name, $sanitizer);

    /**
     * Check if a given name is registred.
     *
     * @param  string  $name
     *
     * @return bool
     */
    public function isRegistred($name);

    /**
     * Resolve a callable or an object from the registrations.
     *
     * @param  string  $name
     *
     * @return callable|object
     *
     * @throws \InvalidArgumentException
     */
    public function resolve($name);
}
