<?php

namespace Silk\Support;

class Callback
{
    /**
     * The normalized callable.
     * @var mixed
     */
    protected $target;

    /**
     * Create a new Callback instance
     *
     * @param callable $target The callback to wrap
     */
    public function __construct(callable $target)
    {
        $this->target = static::normalizeSyntax($target);
    }

    /**
     * Normalize the callable syntax
     *
     * Converts string static class method to standard callable array.
     *
     * @param  mixed $callback  Target callback
     *
     * @return mixed Closure    Anonymous function
     *               array      Class method
     *               string     Function
     */
    public static function normalizeSyntax(callable $callback)
    {
        if (is_string($callback) && false !== strpos($callback, '::')) {
            $callback = explode('::', $callback);
        }

        return $callback;
    }

    /**
     * Call the target callable
     *
     * @return mixed  Returns the return value of the callback, or FALSE on error.
     */
    public function call()
    {
        return $this->callArray(func_get_args());
    }

    /**
     * Call the target callable, with an array of arguments
     *
     * @param  array $arguments  The parameters to be passed to the callback, as an indexed array.
     * @return mixed             Returns the return value of the callback, or FALSE on error.
     */
    public function callArray(array $arguments = [])
    {
        return call_user_func_array($this->target, $arguments);
    }

    /**
     * Get the target callable
     *
     * @return mixed  The normalized callable
     */
    public function get()
    {
        return $this->target;
    }

    /**
     * Get the number of parameters from the callback's signature
     *
     * @return int
     */
    public function parameterCount()
    {
        return $this->reflect()->getNumberOfParameters();
    }

    /**
     * Get the corresponding Reflection instance for the target callable
     *
     * @return \ReflectionFunctionAbstract
     */
    public function reflect()
    {
        if ($this->target instanceof \Closure
            || (is_string($this->target) && function_exists($this->target))
            ) {
            return new \ReflectionFunction($this->target);
        }

        list($class, $method) = $this->target;

        return new \ReflectionMethod($class, $method);
    }
}
