<?php

use Silk\User\Model as User;

use Silk\Contracts\BuildsQueries;

class UserModelTest extends WP_UnitTestCase
{
    /**
     * @test
     */
    function it_can_be_constructed_with_no_parameters()
    {
        new User;
    }

    /**
     * @test
     */
    public function it_takes_a_wp_user_in_the_constructor()
    {
        new User(new WP_User);
    }

    /**
     * @test
     */
    public function it_can_create_a_new_instance_from_a_user_id()
    {
        $user_id = $this->factory->user->create();

        $model = User::fromID($user_id);

        $this->assertInstanceOf(User::class, $model);
        $this->assertSame($user_id, $model->id);
    }

    /**
     * @test
     * @expectedException Silk\User\Exception\UserNotFoundException
     */
    public function it_blows_up_if_unable_to_locate_a_user_by_id()
    {
        User::fromID(0);
    }


    /**
     * @test
     */
    public function it_can_create_a_new_instance_from_a_username()
    {
        $user = $this->factory->user->create_and_get();

        $model = User::fromUsername($user->user_login);

        $this->assertInstanceOf(User::class, $model);
        $this->assertSame($user->ID, $model->id);
    }

    /**
     * @test
     * @expectedException Silk\User\Exception\UserNotFoundException
     */
    public function it_blows_up_if_no_user_is_found_with_the_given_username()
    {
        User::fromUsername('non-existent-username');
    }


    /**
     * @test
     */
    public function it_can_create_a_new_instance_from_the_users_email_address()
    {
        $user = $this->factory->user->create_and_get();

        $model = User::fromEmail($user->user_email);

        $this->assertInstanceOf(User::class, $model);
        $this->assertSame($user->ID, $model->id);
    }

    /**
     * @test
     * @expectedException Silk\User\Exception\UserNotFoundException
     */
    public function it_blows_up_if_no_user_is_found_with_the_given_email()
    {
        User::fromEmail('non-existent@user.com');
    }

    /**
     * @test
     */
    public function it_can_create_a_new_instance_from_the_user_slug()
    {
        $user = $this->factory->user->create_and_get();

        $model = User::fromSlug($user->user_nicename);

        $this->assertInstanceOf(User::class, $model);
        $this->assertSame($user->ID, $model->id);
    }

    /**
     * @test
     * @expectedException Silk\User\Exception\UserNotFoundException
     */
    public function it_blows_up_if_no_user_is_found_with_the_given_slug()
    {
        User::fromSlug('non-existent');
    }

    /**
     * @test
     */
    public function it_aliases_some_properties_to_user_data()
    {
        $user = $this->factory->user->create_and_get();
        $model = new User($user);

        $this->assertSame($user->user_email, $model->email);
        $this->assertSame($user->user_nicename, $model->slug);
        $this->assertSame($user->user_login, $model->username);
    }

    /**
     * @test
     */
    public function it_can_create_a_new_user_from_a_new_instance()
    {
        $model = new User;
        $model->username = 'bigbird';
        $model->password = 'rub_a_dub_dub';
        $model->save();

        $this->assertNotEmpty($model->id,
            "Failed asserting that the User ID $model->id is > 0"
        );
    }

    /**
     * @test
     * @expectedException \Silk\Exception\WP_ErrorException
     */
    public function it_blows_up_if_trying_to_create_a_user_without_a_username()
    {
        $model = new User;
        $model->username = '';
        $model->password = 'password';
        $model->save();
    }

    /**
     * @test
     */
    public function it_can_update_an_existing_user()
    {
        $user = $this->factory->user->create_and_get();

        $model = new User($user);
        $model->first_name = 'Franky';
        $model->last_name = 'Fivefingers';
        $model->save();

        $updated = new WP_User($user->ID);

        $this->assertSame('Franky', $updated->first_name);
        $this->assertSame('Fivefingers', $updated->last_name);
    }

    /**
     * @test
     */
    public function it_can_delete_the_modeled_user()
    {
        $user = $this->factory->user->create_and_get();

        $model = new User($user);

        $model->delete();

        $this->assertFalse(get_user_by('ID', $user->ID));
    }

    /**
     * @test
     */
    public function it_exposes_the_meta_api_for_users()
    {
        $user = $this->factory->user->create_and_get();

        $model = new User($user);

        $model->meta('position')->set('President');
        $model->meta('salary')->set('big');

        $this->assertSame('President', get_user_meta($user->ID, 'position', true));
        $this->assertSame('big', get_user_meta($user->ID, 'salary', true));

        update_user_meta($user->ID, 'favorite_food', 'pizza');

        $this->assertSame('pizza', $model->favorite_food);
    }

    /**
     * @test
     */
    public function the_query_method_fulfills_the_contract()
    {
        $this->assertInstanceOf(BuildsQueries::class, User::query());
    }

    /**
     * @test
     */
    public function it_has_a_method_to_get_the_url_for_the_users_posts()
    {
        $user = $this->factory->user->create_and_get(['nicename' => 'franky']);
        $model = new User($user);

        $this->assertSame(
            get_author_posts_url($user->ID),
            $model->postsUrl()
        );
    }

}
