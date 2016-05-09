<?php

use Silk\Models\Post;
use Silk\Query\Builder;
use Illuminate\Support\Collection;

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

    /**
     * @test
     */
    function it_has_getters_and_setters_for_holding_the_model_instance()
    {
        $model = new CustomCPT;
        $builder = new Builder(new WP_Query);

        $builder->setModel($model);

        $this->assertSame($model, $builder->getModel());
    }

    /**
    * @test
    */
    function it_returns_results_as_a_collection_of_models()
    {
        register_post_type(CustomCPT::POST_TYPE);
        CustomCPT::create(['post_title' => 'check one']);

        $results = CustomCPT::all()->results();

        $this->assertInstanceOf(Collection::class, $results);
        // $this->assertCount(1, $results); // need to move ::all to Model so we can setModel

        $this->assertInstanceOf(CustomCPT::class, $results[0]);
    }

}

class CustomCPT extends Post
{
    const POST_TYPE = 'custom';

}
