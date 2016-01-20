<?php

namespace Silk;

class Hook
{
    protected $handle;

    protected $callback;

    protected $priority = 10;

    protected $iterations;

    protected $maxIterations;


    /**
     * [make description]
     * @param  [type] $handle [description]
     * @return [type]         [description]
     */
    public static function make($handle)
    {
        return new static($handle);
    }

    /**
     * [__construct description]
     * @param [type] $handle [description]
     */
    public function __construct($handle)
    {
        $this->handle = $handle;
    }

    /**
     * [setCallback description]
     * @param callable $callback [description]
     */
    public function setCallback(callable $callback)
    {
        $this->callback = $callback;

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
        if ($this->hasExceededIterations()) {
            return $given;
        }

        $this->iterations++;

        return $this->invokeCallback(func_get_args());
    }

    /**
     * [invokeCallback description]
     * @param  [type] $arguments [description]
     * @return [type]            [description]
     */
    protected function invokeCallback($arguments)
    {
        $arguments = array_slice($arguments, 0, $this->getCallbackParameterCount());

        return call_user_func_array($this->callback, $arguments);
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
        $this->priority = $priority;

        return $this;
    }

    /**
     * [getCallbackParameterCount description]
     * @return [type] [description]
     */
    protected function getCallbackParameterCount()
    {
        return (new \ReflectionFunction($this->callback))
            ->getNumberOfParameters();
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
