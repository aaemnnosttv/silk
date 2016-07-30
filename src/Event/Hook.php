<?php

namespace Silk\Event;

use Silk\Support\Callback;
use Illuminate\Support\Collection;

class Hook
{
    /**
     * The action or filter handle attached to.
     * @var string
     */
    protected $handle;

    /**
     * The callback object holding the target callable.
     * @var Callback
     */
    protected $callback;

    /**
     * The number of parameters defined in the callback's signature.
     * @var int
     */
    protected $callbackParamCount;

    /**
     * The action or filter priority the callback is registered on.
     * @var mixed
     */
    protected $priority;

    /**
     * The number of times the callback has been invoked.
     * @var int
     */
    protected $iterations;
    
    /**
     * The maximum number of iterations allowed for the callback to be invoked.
     * @var int
     */
    protected $maxIterations;

    /**
     * A collection of conditions which control the the invocation of the callback.
     * @var Collection
     */
    protected $conditions;


    /**
     * Create a new Hook instance.
     *
     * @param  string $handle   Action or filter handle
     * @param  int    $priority
     *
     * @return static
     */
    public static function on($handle, $priority = 10)
    {
        return new static($handle, $priority);
    }

    /**
     * Create a new Hook instance.
     *
     * @param  string $handle   Action or filter handle
     * @param  int    $priority
     */
    public function __construct($handle, $priority = 10)
    {
        $this->handle = $handle;
        $this->priority = $priority;
    }

    /**
     * Set the callback to be invoked by the action or filter.
     *
     * @param callable $callback    The callback to be invoked
     *
     * @return $this
     */
    public function setCallback(callable $callback)
    {
        $this->callback = new Callback($callback);
        $this->callbackParamCount = $this->callback->parameterCount();

        return $this;
    }

    /**
     * Set the hook in WordPress.
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
     * Unset the hook in WordPress.
     *
     * @return $this
     */
    public function remove()
    {
        remove_filter($this->handle, [$this, 'mediateCallback'], $this->priority);

        return $this;
    }

    /**
     * Control invocation of the callback.
     *
     * @param mixed $given  The first argument passed to the callback.
     *                      Needed to return for filters.
     *
     * @return mixed        Returned value from Callback
     */
    public function mediateCallback($given = null)
    {
        $arguments = func_get_args();

        if (! $this->shouldInvoke($arguments)) {
            return $given;
        }

        if (is_null($returned = $this->invokeCallback($arguments))) {
            return $given;
        }

        return $returned;
    }

    /**
     * Whether or not the callback should be invoked.
     *
     * @param  array  $arguments  All arguments passed to the callback
     *
     * @return bool
     */
    public function shouldInvoke(array $arguments)
    {
        if ($this->hasExceededIterations()) {
            return false;
        }

        /**
         * Check if any of the conditions returns false,
         * if so, do not invoke.
         */
        return ! $this->conditions()->contains(function ($key, $callback) use ($arguments) {
            return false === $callback->callArray($arguments);
        });
    }

    /**
     * Call the callback.
     *
     * @param  array $arguments  All arguments passed to the callback
     *
     * @return mixed  The value returned from the callback
     */
    protected function invokeCallback($arguments)
    {
        $returned = $this->callback->callArray(
            array_slice($arguments, 0, $this->callbackParamCount ?: null)
        );

        $this->iterations++;

        return $returned;
    }

    /**
     * Set the callback to only be invokable one time.
     *
     * @return $this
     */
    public function once()
    {
        $this->onlyXtimes(1);

        return $this;
    }

    /**
     * Set the maximum number of callback invocations to allow.
     *
     * @param  int $times  The maximum iterations of invocations to allow
     *
     * @return $this
     */
    public function onlyXtimes($times)
    {
        $this->maxIterations = (int) $times;

        return $this;
    }

    /**
     * Prevent the callback from being triggered again.
     *
     * @return $this
     */
    public function bypass()
    {
        $this->onlyXtimes(0);

        return $this;
    }

    /**
     * Set the priority the callback should be registered with.
     *
     * @param  mixed $priority  The callback priority
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
     * Add a condition to control the invocation of the callback.
     *
     * @param  callable $condition  A function to evaluate a condition before the
     *                              hook's callback is invoked.
     *                              If the function returns false, the callback
     *                              will not be invoked.
     *
     * @return $this
     */
    public function onlyIf(callable $condition)
    {
        $this->conditions()->push(new Callback($condition));

        return $this;
    }

    /**
     * Get the collection of callback invocation conditions.
     *
     * @return Collection
     */
    protected function conditions()
    {
        if (is_null($this->conditions)) {
            $this->conditions = new Collection;
        }

        return $this->conditions;
    }

    /**
     * Whether or not the callback has reached the limit of allowed invocations.
     *
     * @return boolean  true for limit reached/exceeded, otherwise false
     */
    protected function hasExceededIterations()
    {
        return ($this->maxIterations > -1) && ($this->iterations >= $this->maxIterations);
    }
}
