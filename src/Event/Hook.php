<?php

namespace Silk\Event;

use Silk\Support\Callback;

class Hook
{
    protected $handle;

    protected $callback;

    protected $callbackParamCount;

    protected $priority;

    protected $iterations;

    protected $maxIterations;


    /**
     * Create a new Hook instance
     *
     * @param  string $handle action or filter handle
     * @param  int    $priority
     *
     * @return static
     */
    public static function on($handle, $priority = 10)
    {
        return new static($handle, $priority);
    }

    /**
     * Create a new Hook instance
     *
     * @param  string $handle action or filter handle
     * @param  int    $priority
     */
    public function __construct($handle, $priority = 10)
    {
        $this->handle = $handle;
        $this->priority = $priority;
    }

    /**
     * Set the callback to be invoked by the action or filter
     *
     * @param callable $callback
     *
     * @return $this
     */
    public function setCallback(callable $callback)
    {
        $this->callback = new Callback($callback);
        $this->callbackParamCount = $this->callback->reflect()->getNumberOfParameters();

        return $this;
    }

    /**
     * Set the hook in WordPress
     *
     * Both actions and filters are registered as filters.
     *
     * @return $this
     */
    public function listen()
    {
        add_filter($this->handle, [$this, 'mediateCallback'], $this->priority, 100);

        return $this;
    }

    /**
     * Unset the hook in WordPress
     *
     * @return $this
     */
    public function remove()
    {
        remove_filter($this->handle, [$this, 'mediateCallback'], $this->priority);

        return $this;
    }

    /**
     * Control invocation of the callback
     *
     * @return mixed  callback returned value
     */
    public function mediateCallback($given = null)
    {
        if (! $this->shouldInvoke(func_get_args())) {
            return $given;
        }

        return $this->invokeCallback(func_get_args());
    }

    /**
     * Whether or not the callback should be invoked
     *
     * @param  array  $args  all arguments passed to the callback
     *
     * @return bool
     */
    public function shouldInvoke(array $args)
    {
        if ($this->hasExceededIterations()) {
            return false;
        }

        return true;
    }

    /**
     * Call the callback
     *
     * @param  array $arguments  the arguments expected by the callback
     *
     * @return mixed  returned output from the callback
     */
    protected function invokeCallback($arguments)
    {
        $arguments = array_slice($arguments, 0, $this->callbackParamCount ?: null);

        $this->iterations++;

        return $this->callback->callArray($arguments);
    }

    /**
     * Set the callback to only be invoked one time
     *
     * @return $this
     */
    public function once()
    {
        $this->onlyXtimes(1);

        return $this;
    }

    /**
     * Set the callback to only be invoked the given number of times
     *
     * @param  int $times  maimum iterations of invocations to allow
     *
     * @return $this
     */
    public function onlyXtimes($times)
    {
        $this->maxIterations = (int) $times;

        return $this;
    }

    /**
     * Prevent the callback from being triggered again
     *
     * @return $this
     */
    public function bypass()
    {
        $this->onlyXtimes(0);

        return $this;
    }

    /**
     * Set the priority the callback should be registered with
     *
     * @param  string|int $priority
     *
     * @return $this
     */
    public function withPriority($priority)
    {
        $this->remove();

        $this->priority = $priority;

        $this->listen();

        return $this;
    }

    /**
     * Whether or not the callback has reached the limit of allowed invocations
     *
     * @return boolean  true for limit reached, otherwise false
     */
    protected function hasExceededIterations()
    {
        return ($this->maxIterations > -1) && ($this->iterations >= $this->maxIterations);
    }
}
