<?php

namespace Silk\User;

use WP_User;
use Silk\Type\Model as BaseModel;
use Silk\Exception\WP_ErrorException;
use Silk\User\Exception\UserNotFoundException;

class Model extends BaseModel
{
    /**
     * The object type in WordPress
     */
    const OBJECT_TYPE = 'user';

    /**
     * The primary ID property on the object
     */
    const ID_PROPERTY = 'ID';

    /**
     * Type object property aliases
     * @var array
     */
    protected $objectAliases = [
        'email'    => 'user_email',
        'slug'     => 'user_nicename',
        'username' => 'user_login',
        'password' => 'user_pass',
    ];

    /**
     * User Constructor.
     *
     * @param WP_User $user
     */
    public function __construct(WP_User $user = null)
    {
        if (! $user) {
            $user = new WP_User;
        }

        $this->object = $user;
    }

    /**
     * Create a new instance from the user ID.
     *
     * @param  string|int $id  User ID
     *
     * @throws UserNotFoundException
     *
     * @return static
     */
    public static function fromID($id)
    {
        if (! $user = get_user_by('id', $id)) {
            throw new UserNotFoundException("No user found with ID $id");
        }

        return new static($user);
    }

    /**
     * Create a new instance from the username.
     *
     * @param  string $username  Username (login)
     *
     * @throws UserNotFoundException
     *
     * @return static
     */
    public static function fromUsername($username)
    {
        if (! $user = get_user_by('login', $username)) {
            throw new UserNotFoundException("No user found with username: $username");
        }

        return new static($user);
    }

    /**
     * Create a new instance from the user's email address.
     *
     * @param  string $email  User email address
     *
     * @throws UserNotFoundException
     *
     * @return static
     */
    public static function fromEmail($email)
    {
        if (! $user = get_user_by('email', $email)) {
            throw new UserNotFoundException("No user found with email address: $email");
        }

        return new static($user);
    }

    /**
     * Create a new instance from the user's slug.
     *
     * @param  string $slug  User slug (nicename)
     *
     * @throws UserNotFoundException
     *
     * @return static
     */
    public static function fromSlug($slug)
    {
        if (! $user = get_user_by('slug', $slug)) {
            throw new UserNotFoundException("No user found with slug: $slug");
        }

        return new static($user);
    }

    /**
     * Get the URL for the user's posts archive.
     *
     * @return string
     */
    public function postsUrl()
    {
        return get_author_posts_url($this->id, $this->slug);
    }

    /**
    * Get a new query builder for the model.
    *
    * @return \Silk\Contracts\BuildsQueries
    */
    public function newQuery()
    {
        return QueryBuilder::make();
    }


    /**
     * Save the changes to the database.
     *
     * @throws WP_ErrorException
     *
     * @return $this
     */
    public function save()
    {
        if (! $this->id) {
            $result = wp_insert_user($this->object);
        } else {
            $result = wp_update_user($this->object);
        }

        if (is_wp_error($result)) {
            throw new WP_ErrorException($result);
        }

        $this->setId($result);

        return $this;
    }

    /**
     * Delete the modeled record from the database.
     *
     * @return $this
     */
    public function delete()
    {
        if (wp_delete_user($this->id)) {
            $this->object = new WP_User;
        }

        return $this;
    }

    /**
     * Reload the object from the database.
     *
     * @return $this
     */
    public function refresh()
    {
        $this->object = new WP_User($this->id);

        return $this;
    }
}
