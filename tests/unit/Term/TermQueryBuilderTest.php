<?php

use Silk\Term\QueryBuilder;
use Silk\WordPress\Term\Category;
use Silk\Support\Collection;

class TermQueryBuilderTest extends WP_UnitTestCase
{
    use TermFactoryHelpers;

    /** @test */
    function it_returns_the_results_as_a_collection()
    {
        $query = new QueryBuilder;

        $this->assertInstanceOf(Collection::class, $query->results());
    }

    /** @test */
    function it_can_limit_the_results_to_a_given_taxonomy()
    {
        /**
         * By default get_terms does not include any that are not assigned to a post.
         *
         * We will limit the query to tags, but to be sure, assign all terms to a post.
         */
        $post_id = $this->factory()->post->create();
        $this->createManyTagsForPost(3, $post_id);
        $this->createManyCatsForPost(3, $post_id);

        $results = (new QueryBuilder)
            ->forTaxonomy('post_tag')
            ->results();

        $this->assertCount(3, $results);
        $this->assertSame(['post_tag','post_tag','post_tag'], $results->pluck('taxonomy')->all());
    }

    /** @test */
    function it_can_include_unattached_terms()
    {
        $post_id = $this->factory()->post->create();
        $this->createManyTags(3); // empties
        $this->createManyTagsForPost(3, $post_id); // assigned

        $query = (new QueryBuilder)
            ->forTaxonomy('post_tag')
            ->includeEmpty();

        $results = $query->results();

        $this->assertCount(6, $results);
    }

    /** @test */
    function it_can_query_all_terms()
    {
        $post_id = $this->factory()->post->create();

        $this->createManyTags(2); // empties
        $this->createManyTagsForPost(3, $post_id); // assigned

        $tags = (new QueryBuilder)->forTaxonomy('post_tag')->all()->results();
        $this->assertCount(2 + 3, $tags);

        $this->createManyCats(4); // empties
        $this->createManyCatsForPost(5, $post_id); // assigned

        $cats = (new QueryBuilder)->forTaxonomy('category')->all()->results();
        // +1 cat for Uncategorized
        $this->assertCount(4 + 5 + 1, $cats);

        $alls = (new QueryBuilder)->all()->results();
        $this->assertCount(2 + 3 + 4 + 5 + 1, $alls);
    }

    /** @test */
    function it_can_limit_the_maximum_number_of_results_to_a_given_number()
    {
        $this->createManyTags(7);

        $query = (new QueryBuilder)
            ->includeEmpty()
            ->limit(5);

        $this->assertCount(5, $query->results());
    }

    /**
     * @test
     * @expectedException Silk\Exception\WP_ErrorException
     */
    function it_blows_up_if_trying_to_query_terms_of_a_non_taxonomy()
    {
        (new QueryBuilder)
            ->forTaxonomy('non-existent')
            ->results();
    }

    /** @test */
    function it_can_accept_and_return_a_model()
    {
        $model = new Category;
        $builder = (new QueryBuilder)->setModel($model);

        $this->assertSame($model, $builder->getModel());
    }

}
