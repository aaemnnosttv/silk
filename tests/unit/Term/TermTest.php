<?php

use Silk\Term\Model;
use Silk\Meta\Meta;
use Silk\Meta\ObjectMeta;
use Silk\Taxonomy\Builder;
use Silk\Taxonomy\Taxonomy;
use Silk\WordPress\Term\Tag;
use Silk\WordPress\Term\Category;

class TermTest extends WP_UnitTestCase
{
    use TermFactoryHelpers;

    /** @test */
    function it_can_create_a_term_from_a_new_instance()
    {
        $model = new Category();
        $model->name = 'Red';
        $model->save();

        $term = get_term_by('name', 'Red', 'category');

        $this->assertSame($model->id, $term->term_id);
    }

    /** @test */
    function it_can_be_instantiated_with_an_array_of_attributes()
    {
        $model = new Category([
            'name' => 'Blue'
        ]);

        $this->assertSame('Blue', $model->object->name);
        $this->assertSame('Blue', $model->name);
    }

    /** @test */
    function it_can_create_a_new_instance_from_a_wp_term()
    {
        wp_insert_term('Blue', 'category');
        $term = get_term_by('name', 'Blue', 'category');

        $model = new Category($term);

        $this->assertInstanceOf(Category::class, $model);
        $this->assertSame($term->term_id, $model->id);
    }

    /** @test */
    function it_can_create_a_new_instance_from_a_term_slug()
    {
        wp_insert_term('Green', 'category');

        $model = Category::fromSlug('green');

        $term = get_term_by('slug', 'green', 'category');

        $this->assertSame($term->term_id, $model->id);
    }

    /**
     * @test
     * @expectedException \Silk\Term\Exception\TermNotFoundException
     */
    function it_blows_up_if_the_term_cannot_be_found_by_slug()
    {
        Category::fromSlug('non-existent-slug');
    }

    /**
     * @test
     * @expectedException \Silk\Term\Exception\TaxonomyMismatchException
     */
    function it_blows_up_if_the_terms_taxonomy_does_not_match_the_models()
    {
        wp_insert_term('Green', 'post_tag');
        $tag_term = get_term_by('name', 'Green', 'post_tag');

        new Category($tag_term);
    }

    /** @test */
    function it_can_create_a_new_instance_from_a_term_id()
    {
        $ids = wp_insert_term('Purple', 'category');

        $model = Category::fromID($ids['term_id']);

        $this->assertInstanceOf(Category::class, $model);
        $this->assertSame($ids['term_id'], $model->id);
    }

    /**
     * @test
     * @expectedException \Silk\Term\Exception\TermNotFoundException
     */
    function it_blows_up_if_the_term_cannot_be_found_by_id()
    {
        Category::fromID(0);
    }

    /** @test */
    function it_has_a_named_constructor_for_creating_a_new_instance_and_term_at_the_same_time()
    {
        $model = Category::create([
            'name' => 'Carnivore',
            'slug' => 'meat-eater',
        ]);

        $term = get_term_by('slug', 'meat-eater', 'category');

        $this->assertSame($term->term_id, $model->id);
        $this->assertSame('meat-eater', $model->slug);
    }

    /** @test */
    function it_has_method_for_checking_if_the_term_exists()
    {
        $model = new Category;
        $this->assertFalse($model->exists());

        $model->name = 'Alive';
        $model->save();

        $this->assertTrue($model->exists());
    }

    /** @test */
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

    /** @test */
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

    /** @test */
    function it_can_delete_itself()
    {
        $model = Category::create(['name' => 'Doomed']);
        $this->assertTrue($model->exists());

        $model->delete();

        $this->assertFalse($model->exists());
        $this->assertEmpty($model->id);
        $this->assertEmpty($model->term_taxonomy_id);
    }

    /** @test */
    function it_blows_up_if_it_tries_to_delete_a_non_existent_term()
    {
        $model = new Category; // does not exist yet
        $model->delete();
    }

    /** @test */
    function that_non_existent_properties_return_null()
    {
        $model = new Category;
        $this->assertNull($model->non_existent_property);
    }

    /** @test */
    function it_reports_proxied_properties_as_set()
    {
        $model = new Category;

        $this->assertTrue(isset($model->name));
        $this->assertTrue(isset($model->slug));
        $this->assertTrue(isset($model->taxonomy));
    }

    /** @test */
    function it_has_a_method_for_returning_the_parent_instance()
    {
        $parent = Category::create([
            'name' => 'Parent'
        ]);

        $child = Category::create([
            'name' => 'Child',
            'parent' => $parent->id
        ]);

        $this->assertSame($child->parent, $child->parent()->id);
    }

    /** @test */
    function it_can_get_all_of_its_ancestors_as_model_instances_of_the_same_class()
    {
        $grand = Category::create([
            'name' => 'Grandparent'
        ]);
        $parent = Category::create([
            'name' => 'Parent',
            'parent' => $grand->id
        ]);
        $child = Category::create([
            'name' => 'Child',
            'parent' => $parent->id
        ]);

        $ancestors = $child->ancestors();

        $this->assertCount(2, $ancestors);
        $this->assertInstanceOf(Category::class, $ancestors[0]);
        $this->assertInstanceOf(Category::class, $ancestors[1]);
        $this->assertSame($parent->id, $ancestors[0]->id);
        $this->assertSame($grand->id, $ancestors[1]->id);
    }

    /** @test */
    function it_can_get_all_of_its_children_as_model_instances_of_the_same_class()
    {
        $grand = Category::create([
            'name' => 'Grandparent'
        ]);
        $parent = Category::create([
            'name' => 'Parent',
            'parent' => $grand->id
        ]);
        $child = Category::create([
            'name' => 'Child',
            'parent' => $parent->id
        ]);

        $children = $grand->children();

        $this->assertCount(2, $children);
        $this->assertInstanceOf(Category::class, $children[0]);
        $this->assertInstanceOf(Category::class, $children[1]);
        $this->assertSame($parent->id, $children[0]->id);
        $this->assertSame($child->id, $children[1]->id);
    }

    /** @test */
    function it_can_query_terms_of_the_same_type()
    {
        $post_id = $this->factory()->post->create();

        $this->createManyTagsForPost(5, $post_id);

        $tags = Tag::query()->results();

        $this->assertCount(5, $tags);

        foreach ($tags as $tag) {
            $this->assertInstanceOf(Tag::class, $tag);
        }
    }

    /** @test */
    function it_has_a_method_for_returning_the_taxonomy_model()
    {
        $term = new Category;

        $this->assertInstanceOf(Taxonomy::class, $term->taxonomy());
        $this->assertSame('category', $term->taxonomy()->id);
    }

    /** @test */
    function it_has_a_method_for_accessing_the_meta_api()
    {
        $model = Category::create(['name' => 'Testing']);

        $this->assertInstanceOf(ObjectMeta::class, $model->meta());
        $this->assertInstanceOf(Meta::class, $model->meta('some-key'));

        $model->meta('some-key')->set('single value');

        $this->assertSame('single value', get_term_meta($model->id, 'some-key', true));
    }

    /** @test */
    function it_returns_a_new_builder_for_its_taxonomy_if_not_registered_yet()
    {
        $this->assertInstanceOf(Builder::class, NewTerm::taxonomy());
    }

    /** @test */
    function it_has_a_method_for_getting_the_term_archive_url()
    {
        $model = $model = Category::create(['name' => 'Awesome']);

        $this->assertSame(
            get_term_link($model->id, $model->taxonomy),
            $model->url()
        );
    }

    /**
     * @test
     * @expectedException Silk\Exception\WP_ErrorException
     */
    function it_blows_up_if_getting_a_term_url_for_a_non_existent_term()
    {
        (new Category)->url();
    }

    /** @test */
    function it_has_a_method_for_soft_retrieving_the_model_by_its_primary_id()
    {
        $term_id = $this->factory()->category->create();

        try {
            $model = Category::find($term_id);
        } catch (\Exception $e) {
            $this->fail("Exception thrown while finding term with ID $term_id. " . $e->getMessage());
        }

        $this->assertEquals($term_id, $model->id);
    }

    /** @test */
    function find_returns_null_if_the_model_cannot_be_found()
    {
        $term_id = 0;

        try {
            $model = Category::find($term_id);
        } catch (\Exception $e) {
            $this->fail("Exception thrown while finding term with ID $term_id. " . $e->getMessage());
        }

        $this->assertNull($model);
    }
}

class NewTerm extends Model
{
    const TAXONOMY = 'new_taxonomy';
}
