<?php

namespace OpenDialogAi\Core\Conversation\DataClients\Serializers\Helpers;

use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class SerializationTreeHelper
{
    /***
     * Filters a lazy array $original by strict array $allowed
     *
     * @param  array  $lazyTree
     * @param  array  $allowed
     *
     * @return array
     */
    static function filter(array $lazyTree, array $allowed) {
        $result = [];
        foreach($lazyTree as $key => $value) {
            if(is_numeric($key) && in_array($value, $allowed, true)) {
                $result[$key] = $value;
            }
            if(array_key_exists($key, $allowed)) {
                $result[$key] = self::filter(is_callable($value) ? $value() : $value, $allowed[$key]);
            }
        }
        return $result;
    }

    /**
     * @param  array  $lazyTree
     * @param  array  $expected
     *
     * @return array
     */
    static function missing(array $lazyTree, array $expected ) {
        $result = [];
        foreach($expected as $key => $value) {
            // Numeric keys indicate a field name to be serialized (e.g 'createdAt').
            if(is_numeric($key) && !in_array($value, $lazyTree, true)) {
                $result[$key] = $value;
            }
            // String keys indicate a reference to another object (e.g, 'conditions')
            else if(array_key_exists($key, $lazyTree)) {
                $subtree = $lazyTree[$key];
                $subtree = is_callable($subtree) ? $subtree() : $subtree;
                $missing = self::missing($subtree, $value);
                if(!empty($missing)) {
                    $result[$key] = $missing;
                }
            }
        }
        return $result;
    }

    /**
     * Takes a serialization tree array and filters the top-level
     * based on a an array of allow field names
     *
     * @param  array  $tree
     * @param  array  $allowed
     *
     * @return array
     */
    public static function filterSerializationTree(array $tree, array $allowed): array
    {
        return array_filter($tree,
            fn($value, $key) => (is_numeric($key) && in_array($value, $allowed, true)) || in_array($key, $allowed, true),
            ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Creates a 'child' context from a serilization context
     * by descending the serialization tree through the provided attribute.
     *
     * @param  array   $parentContext
     * @param  string  $attribute
     *
     * @return array
     */
    public static function createChildContext(array $parentContext, string $attribute): array
    {
        if (isset($parentContext[AbstractNormalizer::ATTRIBUTES][$attribute])) {
            $parentContext[AbstractNormalizer::ATTRIBUTES] = $parentContext[AbstractNormalizer::ATTRIBUTES][$attribute];
        } else {
            unset($parentContext[AbstractNormalizer::ATTRIBUTES]);
        }

        return $parentContext;
    }
}
