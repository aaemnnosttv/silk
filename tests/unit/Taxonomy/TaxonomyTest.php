<?php

use Silk\Term\Category;
use Silk\Taxonomy\Taxonomy;
use Silk\Contracts\BuildsQueries;
use Illuminate\Support\Collection;


class TaxonomyTest extends WP_UnitTestCase
{
    /**
     * @test
     */
    function it_can_staticly_check_the_existence_of_a_given_taxonomy()
    {
        $this->assertTrue(Taxonomy::exists('category'));
        $this->assertTrue(Taxonomy::exists('post_tag'));
        $this->assertFalse(Taxonomy::exists('non-existent'));
    }

    /**
     * @test
     */
    public function it_takes_the_taxonomy_object_to_construct()
    {
        $object = get_taxonomy('category');
        $taxonomy = new Taxonomy($object);
    }

    /**
     * @test
     */
    public function it_has_a_named_constructor_which_takes_the_taxonomy_identifier()
    {
        $this->assertInstanceOf(Taxonomy::class, Taxonomy::make('category'));
    }

    /**
     * @test
     * @expectedException Silk\Taxonomy\Exception\NonExistentTaxonomyException
     */
    public function it_blows_up_if_constructed_with_a_nonexistent_taxonomy()
    {
        new Taxonomy(get_taxonomy('non-existent'));
    }

    /**
     * @test
     */
    public function it_can_unregister_the_taxonomy()
    {
        $this->assertFalse(Taxonomy::exists('temp'));

        register_taxonomy('temp', []);

        $this->assertTrue(Taxonomy::exists('temp'));

        $taxonomy = new Taxonomy(get_taxonomy('temp'));
        $taxonomy->unregister();

        $this->assertFalse(Taxonomy::exists('temp'));
    }

    /**
     * @test
     * @expectedException Silk\Taxonomy\Exception\NonExistentTaxonomyException
     */
    public function it_blows_up_if_trying_to_unregister_a_nonexistent_taxonomy()
    {
        register_taxonomy('temp', []);

        $taxonomy = new Taxonomy(get_taxonomy('temp'));

        unregister_taxonomy('temp');

        $taxonomy->unregister();
    }

    /**
     * @test
     * @expectedException Silk\Exception\WP_ErrorException
     */
    public function it_blows_up_if_attempting_to_unregister_a_builtin_taxonomy()
    {
        $taxonomy = new Taxonomy(get_taxonomy('category'));
        $taxonomy->unregister();
    }

    /**
     * @test
     */
    public function it_has_a_method_for_fetching_terms()
    {
        $this->assertInstanceOf(BuildsQueries::class, Taxonomy::make('category')->terms());
    }

    /**
     * @test
     */
    public function it_proxies_properties_to_the_taxonomy_object()
    {
        $model = Taxonomy::make('category');

        $this->assertSame('Categories', $model->label);
    }

    /**
     * @test
     */
    public function it_has_readonly_properties()
    {
        $model = Taxonomy::make('category');

        $this->assertSame('category', $model->id);
    }

}