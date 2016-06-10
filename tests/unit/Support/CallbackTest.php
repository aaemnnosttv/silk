<?php

use Silk\Support\Callback;

class CallbackTest extends WP_UnitTestCase
{
    /**
     * @test
     */
    public function it_requires_a_callable_to_construct()
    {
        new Callback(function () {});
    }

    /**
     * @test
     */
    public function it_normalizes_string_static_syntax_to_array()
    {
        $callback = new Callback('CallStatic::method');

        $this->assertSame(['CallStatic', 'method'], $callback->get());
    }

    /**
     * @test
     */
    public function it_has_a_method_for_reflecting_the_wrapped_callback()
    {
        $callback = new Callback(function () {});

        $this->assertInstanceOf(ReflectionFunctionAbstract::class, $callback->reflect());
    }

    /**
     * @test
     */
    public function it_has_methods_to_call_the_wrapped_callback()
    {
        $callback = new Callback(function ($result = 'success') {
            return $result;
        });

        $this->assertEquals('success', $callback->call());
        $this->assertEquals('pass', $callback->call('pass'));

        $sumCallback = new Callback(function ($a, $b, $c) {
            return $a + $b + $c;
        });

        $this->assertEquals(12, $sumCallback->callArray([4, 10, -2]));
    }

    /**
     * @test
     */
    public function it_has_a_method_for_returning_the_number_of_parameters_accepted()
    {
        $this->assertEquals(0, (new Callback(function () {}))->parameterCount());
        $this->assertEquals(1, (new Callback(function ($first) {}))->parameterCount());
        $this->assertEquals(2, (new Callback(function ($first, $second) {}))->parameterCount());
    }
}

class CallStatic
{
    public static function method()
    {
    }
}
