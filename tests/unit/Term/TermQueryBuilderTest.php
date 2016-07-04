<?php

use Silk\Term\Category;
use Silk\Taxonomy\Taxonomy;
use Silk\Term\TermQueryBuilder;
use Illuminate\Support\Collection;

class TermQueryBuilderTest extends WP_UnitTestCase
{
    use TermFactoryHelpers;

    /**
     * @test
     */
    public function it_returns_the_results_as_a_collection()
    {
        $query = new TermQueryBuilder;

        $this->assertInstanceOf(Collection::class, $query->results());
    }

    /**
     * @test
     */
    function it_can_limit_the_results_to_a_given_taxonomy()
    {
        /**
         * By default get_terms does not include any that are not assigned to a post.
         *
         * We will limit the query to tags, but to be sure, assign all terms to a post.
         */
        $post_id = $this->factory->post->create();
        $this->createManyTagsForPost(3, $post_id);
        $this->createManyCatsForPost(3, $post_id);

        $results = (new TermQueryBuilder)
            ->forTaxonomy('post_tag')
            ->results();

        $this->assertCount(3, $results);
        $this->assertSame(['post_tag','post_tag','post_tag'], $results->pluck('taxonomy')->all());
    }

    /**
     * @test
     */
    public function it_can_include_unattached_terms()
    {
        $post_id = $this->factory->post->create();
        $this->createManyTags(3); // empties
        $this->createManyTagsForPost(3, $post_id); // assigned

        $query = (new TermQueryBuilder)
            ->forTaxonomy('post_tag')
            ->includeEmpty();

        $results = $query->results();

        $this->assertCount(6, $results);
    }

    /**
     * @test
     */
    public function it_can_limit_the_maximum_number_of_results_to_a_given_number()
    {
        $this->createManyTags(7);

        $query = (new TermQueryBuilder)
            ->includeEmpty()
            ->limit(5);

        $this->assertCount(5, $query->results());
    }

    /**
     * @test
     * @expectedException Silk\Exception\WP_ErrorException
     */
    public function it_blows_up_if_trying_to_query_terms_of_a_non_taxonomy()
    {
        (new TermQueryBuilder)
            ->forTaxonomy('non-existent')
            ->results();
    }

    /**
     * @test
     */
    public function it_can_accept_and_return_a_model()
    {
        $model = new Category;
        $builder = (new TermQueryBuilder)->setModel($model);

        $this->assertSame($model, $builder->getModel());
    }

}
