<?php

namespace Silk\Meta;

trait TypeMeta
{
    /**
     * Meta API for this type
     *
     * @param  string $key  Meta key to retreive or empty to retreive all.
     *
     * @return object
     */
    public function meta($key = '')
    {
        $meta = new ObjectMeta(static::OBJECT_TYPE, $this->id);

        if ($key) {
            return $meta->get($key);
        }

        return $meta;
    }
}
