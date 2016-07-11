<?php

use Silk\Taxonomy\Builder;
use Silk\Taxonomy\Taxonomy;

class TaxonomyBuilderTest extends WP_UnitTestCase
{
    /**
     * @test
     */
    function it_takes_the_taxonomy_name_to_construct()
    {
        new Builder('new_tax');
    }

    /**
     * @test
     */
    public function it_has_a_named_constructor_also()
    {
        $this->assertInstanceOf(Builder::class, Builder::make('new_tax'));
    }

    /**
     * @test
     */
    public function it_returns_a_new_taxonomy_instance_after_registering()
    {
        $registered = Builder::make('new_tax')->register();

        $this->assertInstanceOf(Taxonomy::class, $registered);
        $this->assertSame('new_tax', $registered->id);
    }

    /**
     * @test
     */
    public function it_has_methods_for_setting_the_labels()
    {
        // on('registered_taxonomy', function($taxonomy, $object_type, $args) {
        //     print_r($args);
        // })->onlyIf(function ($taxonomy) {
        //     return $taxonomy == 'genre';
        // });

        $registered = Builder::make('genre')
            ->oneIs('Genre')
            ->manyAre('Genres')
            ->register();

        // var_dump(get_taxonomy('genre'));

        $this->assertSame('All Genres', $registered->labels->all_items);
    }

    /**
     * @test
     */
    public function it_registers_the_taxonomy_for_the_given_types()
    {
        Builder::make('new_tax')
            ->forTypes('post')
            ->register();

        $this->assertObjectHasTaxonomy('post', 'new_tax');
    }

    protected function assertObjectHasTaxonomy($object, $taxonomy)
    {
        $taxonomies = get_object_taxonomies($object);

        $this->assertContains($taxonomy, $taxonomies, print_r($taxonomies, true));
    }
}
