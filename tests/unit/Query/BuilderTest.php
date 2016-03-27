<?php

use Illuminate\Support\Collection;
use Silk\Query\Builder;

class BuilderTest extends WP_UnitTestCase
{
    /**
     * @test
     */
    public function it_requires_a_wp_query_to_be_constructed()
    {
        try {
            new Builder();
        } catch (TypeError $exception) {}

        $this->assertInstanceOf('TypeError', $exception);

        $this->assertInstanceOf(Builder::class, new Builder(new WP_Query));
    }

    /**
     * @test
     */
    public function it_returns_the_results_as_a_collection()
    {
        $builder = new Builder(new WP_Query);

        $this->assertInstanceOf(Collection::class, $builder->results());
    }

    /**
     * @test
     */
    public function the_results_can_be_limited_to_the_integer_provided()
    {
        $this->factory->post->create_many(10);

        $builder = new Builder(new WP_Query);
        $builder->limit(5);

        $this->assertCount(5, $builder->results());
    }
}
