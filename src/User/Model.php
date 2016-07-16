<?php

namespace Silk\User;

use WP_User;
use Silk\Type\Model as BaseModel;

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
     * @return static
     */
    public static function fromID($id)
    {
        return new static(new WP_User($id));
    }

    /**
     * Create a new instance from the username.
     *
     * @param  string $username  Username (login)
     *
     * @return static
     */
    public static function fromUsername($username)
    {
        if (! $user = get_user_by('login', $username)) {
            throw new \Exception("No user found with username: $username");
        }

        return new static($user);
    }

    /**
     * Create a new instance from the user's email address.
     *
     * @param  string $email  User email address
     *
     * @return static
     */
    public static function fromEmail($email)
    {
        if (! $user = get_user_by('email', $email)) {
            throw new \Exception("No user found with email address: $email");
        }

        return new static($user);
    }

    /**
     * Create a new instance from the user's slug.
     *
     * @param  string $slug  User slug (nicename)
     *
     * @return static
     */
    public static function fromSlug($slug)
    {
        if (! $user = get_user_by('slug', $slug)) {
            throw new \Exception("No user found with slug: $slug");
        }

        return new static($user);
    }

    public static function typeId()
    {}

    /**
    * Get a new query builder for the model.
    *
    * @return \Silk\Contracts\BuildsQueries
    */
    public function newQuery()
    {
        throw new \Exception(__METHOD__);
    }

    /**
     * Get the map of action => class for resolving active actions.
     *
     * @return array
     */
    protected function actionClasses()
    {
        return [
            'save' => Action\UserSaver::class,
            'delete' => Action\UserDeleter::class,
        ];
    }

    /**
     * Magic getter.
     *
     * @param  string $property
     *
     * @return mixed
     */
    public function __get($property)
    {
        if (! array_key_exists($property, $this->objectAliases)) {
            return parent::__get($property);
        }

        return data_get($this->object, $this->objectAliases[$property]);
    }

    /**
     * Magic setter.
     *
     * @param string $property  The property name
     * @param mixed  $value     The new property value
     */
    public function __set($property, $value)
    {
        if (array_key_exists($property, $this->objectAliases)) {
            $property = $this->objectAliases[$property];
        }

        if (property_exists($this->object, $property)) {
            $this->object->$property = $value;
            return;
        }

        $this->object->$property = $value;
    }
}
