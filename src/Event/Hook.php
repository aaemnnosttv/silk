<?php

namespace Silk\Event;

use Silk\Support\Callback;

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
        $this->callbackParamCount = $this->callback->reflect()->getNumberOfParameters();

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
     * @param $given  The first argument passed to the callback.
     *                Needed to return for filters.
     *
     * @return mixed  Returned value from Callback
     */
    public function mediateCallback($given = null)
    {
        if (! $this->shouldInvoke(func_get_args())) {
            return $given;
        }

        return $this->invokeCallback(func_get_args());
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

        return true;
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
        $arguments = array_slice($arguments, 0, $this->callbackParamCount ?: null);

        $this->iterations++;

        return $this->callback->callArray($arguments);
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
     * Whether or not the callback has reached the limit of allowed invocations.
     *
     * @return boolean  true for limit reached/exceeded, otherwise false
     */
    protected function hasExceededIterations()
    {
        return ($this->maxIterations > -1) && ($this->iterations >= $this->maxIterations);
    }
}
