<?php

use Silk\Taxonomy\Builder;
use Silk\Taxonomy\Taxonomy;

class TaxonomyBuilderTest extends WP_UnitTestCase
{
    /** @test */
    function it_takes_the_taxonomy_name_to_construct()
    {
        $this->assertInstanceOf(Builder::class, new Builder('new_tax'));
        $this->assertInstanceOf(Builder::class, Builder::make('new_tax'));
    }

    /**
     * @test
     * @expectedException Silk\Taxonomy\Exception\InvalidTaxonomyNameException
     */
    function it_blows_up_if_the_taxononmy_name_is_too_short()
    {
        Builder::make('')->register();
    }

    /**
     * @test
     * @expectedException Silk\Taxonomy\Exception\InvalidTaxonomyNameException
     */
    function it_blows_up_if_the_taxononmy_name_is_too_long()
    {
        Builder::make('thisismorethanthirtytwocharacters')->register();
    }

    /** @test */
    function it_returns_a_new_taxonomy_instance_after_registering()
    {
        $registered = Builder::make('new_tax')->register();

        $this->assertInstanceOf(Taxonomy::class, $registered);
        $this->assertSame('new_tax', $registered->id);
    }

    /** @test */
    function it_has_methods_for_setting_the_labels()
    {
        $registered = Builder::make('genre')
            ->oneIs('Genre')
            ->manyAre('Genres')
            ->register();

        $this->assertSame('All Genres', $registered->labels->all_items);
    }

    /** @test */
    function it_registers_the_taxonomy_for_the_given_types()
    {
        Builder::make('new_tax')
            ->forTypes('post')
            ->register();

        $this->assertObjectHasTaxonomy('post', 'new_tax');
    }

    /** @test */
    function it_has_dedicated_methods_for_public_visibility()
    {
        $public = Builder::make('a-public-taxonomy')->open();
        $this->assertTrue($public->get('public'));

        $private = Builder::make('a-private-taxonomy')->closed();
        $this->assertFalse($private->get('public'));
    }

    protected function assertObjectHasTaxonomy($object, $taxonomy)
    {
        $taxonomies = get_object_taxonomies($object);

        $this->assertContains($taxonomy, $taxonomies, print_r($taxonomies, true));
    }
}
