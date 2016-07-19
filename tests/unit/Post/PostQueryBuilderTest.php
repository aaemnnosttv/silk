<?php

use Silk\Post\Model;
use Silk\Post\QueryBuilder;
use Silk\WordPress\Post\Post;
use Illuminate\Support\Collection;

class PostQueryBuilderTest extends WP_UnitTestCase
{
    /**
     * @test
     */
    public function it_requires_a_wp_query_to_be_constructed()
    {
        $this->assertInstanceOf(QueryBuilder::class, new QueryBuilder(new WP_Query));
    }

    /**
     * @test
     */
    public function it_returns_the_results_as_a_collection()
    {
        $builder = new QueryBuilder(new WP_Query);

        $this->assertInstanceOf(Collection::class, $builder->results());
    }

    /**
     * @test
     */
    public function the_results_can_be_limited_to_the_integer_provided()
    {
        $this->factory->post->create_many(10);

        $builder = new QueryBuilder(new WP_Query);
        $builder->limit(5);

        $this->assertCount(5, $builder->results());
    }

    /**
     * @test
     */
    function it_has_getters_and_setters_for_holding_the_model_instance()
    {
        $model = new CustomCPT;
        $builder = new QueryBuilder(new WP_Query);

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

        $results = CustomCPT::query()->results();

        $this->assertInstanceOf(Collection::class, $results);

        $this->assertInstanceOf(CustomCPT::class, $results[0]);
    }

    /**
     * @test
     */
    function it_has_methods_for_setting_the_order_of_results()
    {
        $first_id = $this->factory->post->create();
        $this->factory->post->create_many(5);
        $last_id = $this->factory->post->create();

        $builder = new QueryBuilder(new WP_Query);
        $builder->setModel(new Post);

        $builder->order('asc');
        $resultsAsc = $builder->results();
        $this->assertSame($first_id, $resultsAsc->first()->id);
        $this->assertSame($last_id, $resultsAsc->last()->id);

        $builder->order('desc');
        $resultsAsc = $builder->results();
        $this->assertSame($first_id, $resultsAsc->last()->id);
        $this->assertSame($last_id, $resultsAsc->first()->id);
    }

    /**
     * @test
     */
    function it_can_query_by_status()
    {
        $this->factory->post->create_many(5, ['post_status' => 'doggie']);
        $builder = new QueryBuilder(new WP_Query);

        $doggies = $builder->whereStatus('doggie')->results();
        $this->assertCount(5, $doggies);
    }

    /**
     * @test
     */
    function it_can_query_by_slug()
    {
        $post_id = $this->factory->post->create(['post_name' => 'sluggy']);
        $builder = new QueryBuilder(new WP_Query);
        $builder->whereSlug('sluggy');

        $this->assertSame($post_id, $builder->results()->first()->ID);
    }


    /**
     * @test
     */
    function it_can_set_arbitrary_query_vars()
    {
        $query = new WP_Query('foo=bar');
        $this->assertSame('bar', $query->get('foo'));

        $builder = new QueryBuilder($query);
        $builder->set('foo', 'donut');

        $this->assertSame('donut', $query->get('foo'));
    }
}

class CustomCPT extends Model
{
    const POST_TYPE = 'custom';
}
