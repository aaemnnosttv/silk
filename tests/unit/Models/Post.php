<?php

use Silk\Models\Post;
use Silk\Silk\WP_ErrorException;
use Symfony\Component\VarDumper\Caster\Caster;

class PostModelTest extends WP_UnitTestCase
{
	/**
	 * @test
	 */
	function it_can_find_a_post_by_the_id()
	{
		$post_id = $this->factory->post->create();
		$model   = Post::fromID($post_id);

		$this->assertInstanceOf(Post::class, $model);
		$this->assertInstanceOf(WP_Post::class, $model->post);
		$this->assertEquals($post_id, $model->id);
	}

	/**
	 * @test
	 */
	function it_can_find_a_post_by_the_slug()
	{
		$the_slug = 'foo-bar-slug';
		$post_id  = $this->factory->post->create(['post_name' => $the_slug]);
		$model    = Post::fromSlug($the_slug);

		$this->assertEquals($post_id, $model->id);
	}

	/**
	 * @test
	 * @expectedException Silk\Models\Exceptions\PostNotFoundException
	 */
	function it_blows_up_if_no_post_exists_for_given_id()
	{
		Post::fromID(123958723409817209872350872395872304);
	}

	/**
	 * @test
	 */
	function it_proxies_property_access_to_the_post_if_not_available_on_the_instance()
	{
	    $post_id = $this->factory->post->create();
		$post = get_post($post_id);
		$model = new Post($post);

		$this->assertEquals($post->post_date, $model->post_date);
		$this->assertEquals($post->post_excerpt, $model->post_excerpt);
	}

	/**
	 * @test
	 */
	function it_provides_an_object_for_interacting_with_the_post_meta()
	{
	    $post_id = $this->factory->post->create();
		$post_meta = get_post_custom($post_id);
		$model = Post::fromID($post_id);

		$this->assertEquals($post_meta, $model->meta()->all()->toArray());
	}

	/**
	 * @test
	 */
	function it_can_create_a_new_post()
	{
	    $model = Post::create([
			'post_title' => 'Foo'
		]);

		$post = \get_post($model->id);

		$this->assertEquals($post->ID, $model->id);
	}


	/**
	 * @test
	 * @expectedException Silk\WP_ErrorException
	 */
	function it_blows_up_if_required_attributes_are_not_passed_when_created()
	{
		Post::create();
	}

	/**
	 * @test
	 */
	function it_handles_trashing_and_untrashing()
	{
	    $model = Post::create([
			'post_title' => 'Yay, I\'m Alive!',
			'post_status' => 'publish'
		]);

		$this->assertEquals('publish', get_post_status($model->id));

		$model->trash();

		$this->assertEquals('trash', get_post_status($model->id));

		$model->untrash();

		$this->assertEquals('publish', get_post_status($model->id));
	}

	/**
	 * @test
	 */
	function it_has_a_method_for_refreshing_the_wrapped_post()
	{
	    $model = Post::create([
			'post_title' => 'OG Title'
		]);

		// the post is modified elsewhere
		wp_update_post([
			'ID' => $model->id,
			'post_title' => 'Changed'
		]);

		$this->assertEquals('OG Title', $model->post_title);
		$this->assertEquals('Changed', get_the_title($model->id));

		$model->refresh();

		$this->assertEquals('Changed', $model->post_title);
	}

	/**
	 * @test
	 */
	function it_can_save_changes_back_to_the_database()
	{
		$model = Post::create([
			'post_title' => 'OG Title'
		]);

		$model->post_title = 'Changed';

		$model->save();

		$this->assertEquals('Changed', get_the_title($model->id));
	}


}
