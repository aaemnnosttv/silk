<?php

use Silk\Post\Model;
use Silk\Post\QueryBuilder;
use Silk\WordPress\Post\Post;
use Silk\Support\Collection;

class PostQueryBuilderTest extends WP_UnitTestCase
{
    /** @test */
    function it_requires_a_wp_query_to_be_constructed()
    {
        $this->assertInstanceOf(QueryBuilder::class, new QueryBuilder(new WP_Query));
    }

    /** @test */
    function it_returns_the_results_as_a_collection()
    {
        $builder = new QueryBuilder(new WP_Query);

        $this->assertInstanceOf(Collection::class, $builder->results());
    }

    /** @test */
    function the_results_can_be_limited_to_the_integer_provided()
    {
        $this->factory()->post->create_many(10);

        $builder = new QueryBuilder(new WP_Query);
        $builder->limit(5);

        $this->assertCount(5, $builder->results());
    }

    /** @test */
    function it_has_getters_and_setters_for_holding_the_model_instance()
    {
        $model = new CustomCPT;
        $builder = new QueryBuilder(new WP_Query);

        $builder->setModel($model);

        $this->assertSame($model, $builder->getModel());
    }

    /** @test */
    function it_returns_results_as_a_collection_of_models()
    {
        register_post_type(CustomCPT::POST_TYPE);
        CustomCPT::create(['post_title' => 'check one']);

        $results = CustomCPT::query()->results();

        $this->assertInstanceOf(Collection::class, $results);

        $this->assertInstanceOf(CustomCPT::class, $results[0]);
    }

    /** @test */
    function it_has_methods_for_setting_the_order_of_results()
    {
        $first_id = $this->factory()->post->create();
        $this->factory()->post->create_many(5);
        $last_id = $this->factory()->post->create();

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

    /** @test */
    function it_can_query_by_status()
    {
        $this->factory()->post->create_many(5, ['post_status' => 'doggie']);
        $builder = new QueryBuilder(new WP_Query);

        $doggies = $builder->whereStatus('doggie')->results();
        $this->assertCount(5, $doggies);
    }

    /** @test */
    function it_can_query_by_slug()
    {
        $post_id = $this->factory()->post->create(['post_name' => 'sluggy']);
        $builder = new QueryBuilder(new WP_Query);
        $builder->whereSlug('sluggy');

        $this->assertSame($post_id, $builder->results()->first()->ID);
    }

    /** @test */
    function it_can_set_arbitrary_query_vars()
    {
        $query = new WP_Query('foo=bar');
        $this->assertSame('bar', $query->get('foo'));

        $builder = new QueryBuilder($query);
        $builder->set('foo', 'donut');

        $this->assertSame('donut', $query->get('foo'));
    }

    /** @test */
    function it_delegates_query_scopes_to_the_model()
    {
        $model = new ModelTestScope();
        $this->factory()->post->create_many(3, [
            'post_type' => $model->post_type,
            'post_status' => 'publish',
        ]);
        $this->factory()->post->create_many(4, [
            'post_type' => $model->post_type,
            'post_status' => 'draft',
        ]);
        $this->factory()->post->create_many(5, [
            'post_type' => $model->post_type,
            'post_status' => 'inherit',
        ]);

        $builder = new QueryBuilder(new WP_Query);
        $builder->setModel($model);

        $this->assertCount(3, $builder->published()->results());
        $this->assertCount(4, $builder->draft()->results());
        $this->assertCount(5, $builder->revision()->results());
    }

    /** @test */
    function undefined_scopes_throw_method_not_found_exception()
    {
        $model = new ModelTestScope();
        $this->factory()->post->create_many(3, [
            'post_type' => $model->post_type,
            'post_status' => 'publish',
        ]);

        $builder = new QueryBuilder(new WP_Query);
        $builder->setModel($model);

        $this->assertFalse(method_exists($model, 'scopeNonExistentScope'));

        try {
            $builder->nonExistentScope();
        } catch (\BadMethodCallException $e) {
            return;
        }

        $this->fail('Expected a BadMethodCallException due to missing query scope');
    }

    /** @test */
    function scopes_can_pass_parameters_to_the_model_methods()
    {
        $model = new ModelTestScope();
        $parent_id = $this->factory()->post->create([
            'post_type' => $model->post_type,
        ]);

        $children = $this->factory()->post->create_many(3, [
            'post_type' => $model->post_type,
            'post_parent' => $parent_id,
        ]);

        $builder = new QueryBuilder(new WP_Query);
        $builder->setModel($model);

        $this->assertCount(3, $builder->childOf($parent_id)->results());
        $this->assertEqualSets($children, $builder->childOf($parent_id)->results()->pluck('id')->all());
    }

    /** @test */
    function it_provides_readonly_access_to_the_wrapped_query_object()
    {
        $query   = new WP_Query;
        $builder = new QueryBuilder($query);

        $this->assertSame($query, $builder->getQuery());
    }

}

class CustomCPT extends Model
{
    const POST_TYPE = 'custom';
}

class ModelTestScope extends Model
{
    const POST_TYPE = 'custom';

    public function scopeDraft(QueryBuilder $builder)
    {
        return $builder->whereStatus('draft');
    }

    public function scopePublished(QueryBuilder $builder)
    {
        return $builder->whereStatus('publish');
    }

    public function scopeRevision(QueryBuilder $builder)
    {
        return $builder->whereStatus('inherit');
    }

    public function scopeChildOf(QueryBuilder $builder, $parent)
    {
        return $builder->set('post_parent', $parent);
    }
}
