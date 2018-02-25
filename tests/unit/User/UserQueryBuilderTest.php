<?php

use Silk\User\Model;
use Silk\User\QueryBuilder;
use Silk\Support\Collection;

class UserQueryBuilderTest extends WP_UnitTestCase
{
    /** @test */
    function it_can_accept_an_existing_wp_user_query()
    {
        $this->assertInstanceOf(QueryBuilder::class, new QueryBuilder(new WP_User_Query));
    }

    /** @test */
    function if_no_user_query_instance_is_provided_it_will_create_one_for_us()
    {
        $this->assertInstanceOf(QueryBuilder::class, new QueryBuilder);
    }

    /** @test */
    function it_has_a_named_constructor_for_creating_a_new_instance()
    {
        $this->assertInstanceOf(QueryBuilder::class, QueryBuilder::make());
    }

    /** @test */
    function it_returns_the_results_as_a_collection()
    {
        $this->assertInstanceOf(Collection::class, QueryBuilder::make()->results());
    }

    /** @test */
    function it_can_take_and_return_a_user_model()
    {
        $builder = new QueryBuilder();

        $user = new Model;
        $builder->setModel($user);

        $this->assertSame($user, $builder->getModel());
    }

    /** @test */
    function it_can_accept_arbitrary_query_vars()
    {
        $query = Mockery::spy(WP_User_Query::class);

        $builder = new QueryBuilder($query);
        $builder->set('count_total', false);

        $query->shouldHaveReceived('set')->with('count_total', false);
    }


    /** @test */
    function it_returns_the_results_as_a_collection_of_model_instances_when_set()
    {
        $new_user_id = $this->factory()->user->create();

        $builder = new QueryBuilder();
        $builder->setModel(new Model);
        $results = $builder->results();

        $this->assertInstanceOf(Model::class, $results->first());
        $this->assertContains($new_user_id, $results->pluck('ID'));
    }

}
