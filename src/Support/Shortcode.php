<?php

namespace Silk\Support;

use Illuminate\Support\Collection;

abstract class Shortcode
{
    /**
     * The attributes passed to the shortcode
     * @var array
     */
    protected $attributes;

    /**
     * The enclosed content within the shortcode
     * @var string
     */
    protected $content;
    
    /**
     * The shortcode tag that was called
     * @var string
     */
    protected $tag;

    /**
     * Shortcode Constructor.
     *
     * @param array $atts      Shortcode attributes
     * @param string $content  The inner (enclosed) content
     * @param string $tag      The called shortcode tag
     */
    public function __construct($atts, $content, $tag)
    {
        $this->attributes = $atts;
        $this->content = $content;
        $this->tag = $tag;
    }

    /**
     * Register a tag for this shortcode.
     *
     * @param mixed $tag  The tag to register with this shortcode class
     */
    public static function register($tag)
    {
        add_shortcode((string) $tag, [static::class, 'controller']);
    }

    /**
     * WordPress Shortcode Callback
     *
     * @param  mixed $atts      Shortcode attributes
     * @param  string $content  The inner (enclosed) content
     * @param  string $tag      The called shortcode tag
     *
     * @return static
     */
    public static function controller($atts, $content, $tag)
    {
        return (new static((array) $atts, $content, $tag))->render();
    }

    /**
     * Call the shortcode's handler and return the output.
     *
     * @return mixed  Rendered shortcode output
     */
    public function render()
    {
        $dedicated_method = "{$this->tag}_handler";

        if (method_exists($this, $dedicated_method)) {
            return $this->$dedicated_method();
        }

        return $this->handler();
    }

    /**
     * Catch-all render method.
     *
     * @return string
     */
    protected function handler()
    {
        return '';  // Override this in a sub-class
    }

    /**
     * Get all attributes as a collection.
     *
     * @return Collection
     */
    public function attributes()
    {
        return new Collection($this->attributes);
    }
}
