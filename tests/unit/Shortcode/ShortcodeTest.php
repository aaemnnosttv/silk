<?php

use Silk\Support\Shortcode;
use Illuminate\Support\Collection;

class ShortcodeTest extends WP_UnitTestCase
{
    /**
     * @test
     */
    function it_can_register_many_shortcodes_for_the_same_class()
    {
        SomeShortcode::register('one');
        SomeShortcode::register('two');

        $this->assertSame(do_shortcode('[one]'), 'SomeShortcode::handler');
        $this->assertSame(do_shortcode('[two]'), 'SomeShortcode::handler');
    }

    /**
     * @test
     */
    function there_is_a_method_naming_convention_for_a_dedicated_handler_method()
    {
        SomeShortcode::register('foo');
        $this->assertSame(do_shortcode('[foo]'), 'bar');
    }

    /**
     * @test
     */
    function there_is_a_method_for_getting_the_attributes_as_a_collection()
    {
        $shortcode = new SomeShortcode(['test' => 'ok'], '', 'testing');

        $this->assertInstanceOf(Collection::class, $shortcode->attributes());
        $this->assertSame('ok', $shortcode->attributes()->get('test'));
        $this->assertCount(1, $shortcode->attributes());
    }

    /**
     * @test
     */
    function it_returns_an_emtpy_string_if_no_handler_is_implemented()
    {
        $shortcode = new TestShortcode([], '', 'test');
        $this->assertSame('', $shortcode->render());
    }

}

/**
 * The parent Shortcode class is abstract,
 * so we use TestShortcode to test the unchanged behavior.
 */
class TestShortcode extends Shortcode {}

class SomeShortcode extends Shortcode
{
    public function handler()
    {
        return __METHOD__;
    }

    public function foo_handler()
    {
        return 'bar';
    }
}
