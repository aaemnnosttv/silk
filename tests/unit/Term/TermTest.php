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
        $tag_term = get_term_by('name', 'Green', 'post_tag');

        Category::fromWpTerm($tag_term);
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

    /**
     * @test
     * @expectedException Silk\Term\Exception\TermNotFoundException
     */
    function it_blows_up_if_the_term_cannot_be_found_by_id()
    {
        Category::fromID(0);
    }

    /**
     * @test
     */
    function it_has_a_named_constructor_for_creating_a_new_instance_and_term_at_the_same_time()
    {
        $model = Category::create([
            'name' => 'Carnivore',
            'slug' => 'meat-eater',
        ]);

        $term = get_term_by('slug', 'meat-eater', 'category');

        $this->assertSame('meat-eater', $model->slug);
    }

    /**
     * @test
     */
    function it_has_method_for_checking_if_the_term_exists()
    {
        $model = new Category;
        $this->assertFalse($model->exists());

        $model->name = 'Alive';
        $model->save();

        $this->assertTrue($model->exists());
    }

    /**
     * @test
     */
    function it_has_a_method_for_checking_if_the_term_is_a_child_of_another_term()
    {
        $parent = Category::create([
            'name' => 'Parent'
        ]);

        $child = Category::create([
            'name' => 'Child',
            'parent' => $parent->id
        ]);

        $this->assertTrue($child->isChildOf($parent->id));
        $this->assertTrue($child->isChildOf($parent));
    }

    /**
     * @test
     */
    function it_can_save_changes_to_the_database()
    {
        $model = Category::create(['name' => 'Initial Name']);

        $this->assertSame(
            'Initial Name',
            get_term_field('name', $model->id, $model->taxonomy)
        );

        $model->name = 'New Name';
        $model->save();

        $this->assertSame(
            'New Name',
            get_term_field('name', $model->id, $model->taxonomy)
        );
    }

    /**
     * @test
     * @expectedException Silk\Exception\WP_ErrorException
     */
    function it_blows_up_if_trying_to_save_a_term_without_a_name()
    {
        $model = new Category;
        $model->save();
    }

    /**
     * @test
     */
    function that_non_existent_properties_return_null()
    {
        $model = new Category;
        $this->assertNull($model->non_existent_property);
    }

}
