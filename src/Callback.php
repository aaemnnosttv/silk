<?php

namespace Silk;

class Callback
{
    protected $target;

    protected $callbackParamCount;

    public function __construct(callable $target)
    {
        $this->target = $this->normalizeSyntax($target);
    }

    protected function normalizeSyntax($callback)
    {
        if (is_string($callback) && false !== strpos($callback, '::')) {
            $callback = explode('::', $callback);
        }

        return $callback;
    }

    public function call()
    {
        return $this->callArray(func_get_args());
    }

    public function callArray($arguments)
    {
        return call_user_func_array($this->target, $arguments);
    }

    public function get()
    {
        return $this->target;
    }

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
