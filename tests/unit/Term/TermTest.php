<?php

use Silk\Term\Category;

class TermTest extends WP_UnitTestCase
{
    /**
     * @test
     */
    function it_can_create_a_term_from_a_new_instance()
    {
        $model = new Category();
        $model->name = 'Red';
        $model->save();

        $term = get_term_by('name', 'Red', 'category');

        $this->assertSame($model->id, $term->term_id);
    }

    /**
     * @test
     */
    function it_can_create_a_new_instance_from_a_wp_term()
    {
        wp_insert_term('Blue', 'category');
        $term = get_term_by('name', 'Blue', 'category');

        $model = Category::fromWpTerm($term);

        $this->assertInstanceOf(Category::class, $model);
        $this->assertSame($term->term_id, $model->id);
    }

    /**
     * @test
     */
    function it_can_create_a_new_instance_from_a_term_slug()
    {
        wp_insert_term('Green', 'category');

        $model = Category::fromSlug('green');

        $term = get_term_by('slug', 'green', 'category');

        $this->assertSame($term->term_id, $model->id);
    }

    /**
     * @test
     * @expectedException Silk\Term\Exception\TermNotFoundException
     */
    function it_blows_up_if_the_term_cannot_be_found_by_slug()
    {
        Category::fromSlug('non-existent-slug');
    }

    /**
     * @test
     * @expectedException Silk\Term\Exception\TaxonomyMismatchException
     */
    function it_blows_up_if_the_terms_taxonomy_does_not_match_the_models()
    {
            wp_insert_term('Green', 'post_tag');
            $term = get_term_by('name', 'Green', 'post_tag');

            Category::fromWpTerm($term);
    }

    /**
     * @test
     */
    function it_can_create_a_new_instance_from_a_term_id()
    {
        $ids = wp_insert_term('Purple', 'category');

        $model = Category::fromID($ids['term_id']);

        $this->assertInstanceOf(Category::class, $model);
        $this->assertSame($ids['term_id'], $model->id);
    }

}
