<?php

namespace Silk;

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
     * @return static         instance
     */
    public static function on($handle, $priority = 10)
    {
        return new static($handle, $priority);
    }

    /**
     * Create a new Hook instance
     * @param  string $handle action or filter handle
     * @param  int    $priority
     */
    public function __construct($handle, $priority = 10)
    {
        $this->handle = $handle;
        $this->priority = $priority;
    }

    /**
     * [setCallback description]
     * @param callable $callback [description]
     */
    public function setCallback(callable $callback)
    {
        $this->callback = new Callback($callback);
        $this->callbackParamCount = $this->callback->reflect()->getNumberOfParameters();

        return $this;
    }

    /**
     * Set the hook in WP
     * @return [type] [description]
     */
    public function listen()
    {
        add_filter($this->handle, [$this, 'mediateCallback'], $this->priority, 100);

        return $this;
    }

    /**
     * Unset the hook in WP
     * @return [type] [description]
     */
    public function remove()
    {
        remove_filter($this->handle, [$this, 'mediateCallback'], $this->priority);

        return $this;
    }

    /**
     * [mediateCallback description]
     * @return [type] [description]
     */
    public function mediateCallback($given = null)
    {
        if (! $this->shouldInvoke(func_get_args())) {
            return $given;
        }

        return $this->invokeCallback(func_get_args());
    }

    public function shouldInvoke(array $args)
    {
        if ($this->hasExceededIterations()) {
            return false;
        }

        return true;
    }

    /**
     * [invokeCallback description]
     * @param  [type] $arguments [description]
     * @return [type]            [description]
     */
    protected function invokeCallback($arguments)
    {
        $arguments = array_slice($arguments, 0, $this->callbackParamCount ?: null);

        $this->iterations++;

        return $this->callback->callArray($arguments);
    }

    /**
     * [once description]
     * @return [type] [description]
     */
    public function once()
    {
        $this->onlyXtimes(1);

        return $this;
    }

    /**
     * [onlyXtimes description]
     * @param  [type] $times [description]
     * @return [type]        [description]
     */
    public function onlyXtimes($times)
    {
        $this->maxIterations = (int) $times;

        return $this;
    }

    /**
     * Prevent the callback from being triggered again
     * @return [type] [description]
     */
    public function bypass()
    {
        $this->onlyXtimes(0);

        return $this;
    }

    /**
     * [withPriority description]
     * @param  [type] $priority [description]
     * @return [type]           [description]
     */
    public function withPriority($priority)
    {
        $this->remove();

        $this->priority = $priority;

        $this->listen();

        return $this;
    }

    /**
     * [hasExceededIterations description]
     * @return boolean [description]
     */
    protected function hasExceededIterations()
    {
        return ($this->maxIterations > -1) && ($this->iterations >= $this->maxIterations);
    }
}
