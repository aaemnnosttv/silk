<?php

namespace Silk;

class Hook
{
    protected $handle;

    protected $callback;

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
     * [listen description]
     * @return [type] [description]
     */
    public function listen()
    {
        add_filter($this->handle, $this->callback);

        return $this;
    }
}
