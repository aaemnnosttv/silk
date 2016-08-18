<?php

namespace Silk\User;

use WP_User;
use Silk\Type\Model as BaseModel;
use Illuminate\Support\Collection;
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
     * User Constructor.
     *
     * @param array|WP_User $user  User object or array of attributes
     */
    public function __construct($user = null)
    {
        $attributes = is_array($user) ? $user : [];

        if (! $user instanceof WP_User) {
            $user = new WP_User();
        }

        $this->object = $this->normalizeData($user);

        $this->fill($attributes);
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
     * Create a new instance using the currently authenticated user.
     *
     * @return static
     */
    public static function auth()
    {
        return new static(wp_get_current_user());
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
    * @return \Silk\Contracts\Query\BuildsQueries
    */
    public function newQuery()
    {
        return QueryBuilder::make()->setModel($this);
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

        $this->setId($result)->refresh();

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
            $this->setObject(new WP_User);
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
        $this->setObject(new WP_User($this->id));

        return $this;
    }

    /**
     * Set the WP_User object on the model.
     *
     * @param WP_User $user
     *
     * @return $this
     */
    protected function setObject($user)
    {
        $this->object = $this->normalizeData($user);

        return $this;
    }

    /**
     * Normalize the user data object on the given User.
     *
     * This is necessary for object aliases and shorthand properties to work properly
     * due to the fact that the WP_User's data object is a plain object which
     * does not always contain all properties as is the case with other WP objects.
     *
     * @param WP_User $user
     *
     * @return WP_User
     */
    protected function normalizeData(WP_User $user)
    {
        Collection::make([
            'ID',
            'user_login',
            'user_pass',
            'user_nicename',
            'user_email',
            'user_registered',
            'user_activation_key',
            'user_status',
            'display_name',
            'spam',
            'deleted',
        ])->diff(array_keys((array) $user->data))
            ->each(function ($property) use ($user) {
                $user->data->$property = null; // exists but ! isset
            });

        return $user;
    }

    /**
     * Get the aliased object.
     *
     * Most user data from the database is stored as an object on the user's `data` property.
     *
     * @return object|\stdClass
     */
    protected function getAliasedObject()
    {
        return $this->object->data;
    }
}
