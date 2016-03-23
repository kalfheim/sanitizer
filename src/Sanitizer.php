<?php

namespace Alfheim\Sanitizer;

use InvalidArgumentException;
use Alfheim\Sanitizer\Registrar\RegistrarInterface;

class Sanitizer
{
    /**
     * The value placeholder string. This allows the user to change the
     * position of the value to sanitize in the arguments array.
     *
     * @var string
     */
    const PLACEHOLDER_VALUE = '{{ VALUE }}';

    /**
     * The global keyword.
     *
     * @var string
     */
    const GLOBAL_KEY = '*';

    /** @var array */
    protected $rules = [];

    /** @var \Alfheim\Sanitizer\Registrar\RegistrarInteraface */
    protected $registrar;

    /**
     * Statically create a new Sanitizer instance with a set of rules.
     *
     * @param  string|array  $ruleset  The sanitation rules.
     *
     * @return \Alfheim\Sanitizer\Sanitizer
     *
     * @static
     */
    public static function make($ruleset)
    {
        return (new static)->rules($ruleset);
    }

    /**
     * Add rules to the sanitizer.
     *
     * @param  string|array  $ruleset  The sanitation rules.
     *
     * @return \Alfheim\Sanitizer\Sanitizer  $this
     */
    public function rules($ruleset)
    {
        if (!is_array($ruleset)) {
            $ruleset = [static::GLOBAL_KEY => $ruleset];
        }

        foreach ($ruleset as $key => $rules) {
            $this->addRule($key, $rules);
        }

        return $this;
    }

    /**
     * Sanitize some data.
     *
     * @param  mixed  $data
     *
     * @return mixed
     */
    public function sanitize($data)
    {
        if ($this->hasGlobals()) {
            if (!is_array($data)) {
                return $this->sanitizeValueFor(static::GLOBAL_KEY, $data);
            }

            foreach ($data as $key => $value) {
                $data[$key] = $this->sanitizeValueFor(static::GLOBAL_KEY, $value);
            }
        }

        foreach ($data as $key => $value) {
            if (!$this->shouldSanitize($key)) {
                continue;
            }

            $data[$key] = $this->sanitizeValueFor($key, $value);
        }

        return $data;
    }

    /**
     * Sanitize some data by reference.
     *
     * @param  mixed  &$data
     */
    public function sanitizeByRef(&$data)
    {
        $data = $this->sanitize($data);
    }

    /**
     * Set the registrar instance for the sanitizer.
     *
     * @param  \Alfheim\Sanitizer\Registrar\RegistrarInteraface  $registrar
     *
     * @return \Alfheim\Sanitizer\Sanitizer  $this
     */
    public function setRegistrar(RegistrarInterface $registrar)
    {
        $this->registrar = $registrar;

        return $this;
    }

    /**
     * Check if a registrar has been set.
     *
     * @return bool
     */
    protected function hasRegistrar()
    {
        return !is_null($this->registrar);
    }

    /**
     * Check if global rules have been registered.
     *
     * @return bool
     */
    protected function hasGlobals()
    {
        return isset($this->rules[static::GLOBAL_KEY]);
    }

    /**
     * Check if a given key should be sanitized.
     *
     * @param  string  $key
     *
     * @return bool
     */
    protected function shouldSanitize($key)
    {
        return isset($this->rules[$key]);
    }

    /**
     * Sanitize a single value for a given key.
     *
     * @param  string  $key
     * @param  mixed   $value
     *
     * @return mixed
     */
    protected function sanitizeValueFor($key, $value)
    {
        foreach ($this->rules[$key] as $rule) {
            $value = call_user_func_array(
                $this->getCallable($rule[0], $key),
                $this->buildArguments($value, isset($rule[1]) ? $rule[1] : null)
            );
        }

        return $value;
    }

    /**
     * Resolve the callable for a given rule.
     *
     * @param  mixed   $value
     * @param  string  $key
     *
     * @return callable
     *
     * @throws \InvalidArgumentException
     */
    protected function getCallable($value, $key)
    {
        // If the value is a string, a registrar is set and the value is
        // registred with the registrar, resolve it there.
        if (
            is_string($value) &&
            $this->hasRegistrar() &&
            $this->registrar->isRegistred($value)
        ) {
            $value = $this->registrar->resolve($value);
        }

        // If the value is now a callable, go ahead and return it...
        if (is_callable($value)) {
            return $value;
        }

        // However, if we've got an object and a filter method for the key
        // exists, we'll return that as a callable.
        if (
            is_object($value) &&
            method_exists($value, $method = $this->getFilterMethodName($key))
        ) {
            return [$value, $method];
        }

        throw new InvalidArgumentException(sprintf(
            'Could not resolve callable for [%s]', $value
        ));
    }

    /**
     * Build the arguments for a callback.
     *
     * @param  mixed       $value
     * @param  array|null  $args
     *
     * @return array
     */
    protected function buildArguments($value, array $args = null)
    {
        if (!$args) {
            return (array)$value;
        }

        $valuePosition = array_search(static::PLACEHOLDER_VALUE, $args, true);

        if ($valuePosition === false) {
            return array_merge((array)$value, $args);
        } else {
            $args[$valuePosition] = $value;
        }

        return $args;
    }

    /**
     * Add a rule to the sanitizer factory.
     *
     * @param  string  $key
     * @param  string|array|\Closure  $rules
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    protected function addRule($key, $rules)
    {
        if ($this->shouldSanitize($key)) {
            throw new InvalidArgumentException(sprintf(
                'Sanitation rules are already defined for field [%s]', $key
            ));
        }

        $this->rules[$key] = $this->buildRules($rules);
    }

    /**
     * Build a valid set of rules.
     *
     * @param  string|array|\Closure  $rules
     *
     * @return array
     */
    protected function buildRules($rules)
    {
        if (is_string($rules)) {
            $rules = explode('|', $rules);
        } elseif (is_object($rules)) {
            $rules = [$rules];
        }

        $built = [];

        foreach ((array)$rules as $rule) {
            if (is_string($rule) && strpos($rule, ':') !== false) {
                $args = explode(':', $rule);
                $rule = array_shift($args);

                $built[] = [$rule, $args];
            } else {
                $built[] = [$rule];
            }
        }

        return $built;
    }

    /**
     * Get the filter method name which will be called on an object.
     *
     * @param  string  $value
     *
     * @return string
     */
    protected function getFilterMethodName($value)
    {
        return sprintf('filter%s', lcfirst(
            str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $value)))
        ));
    }
}
