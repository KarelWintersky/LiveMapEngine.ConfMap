<?php

namespace LiveMapEngine;

use Throwable;

class Helpers
{
    /**
     * @param array $input_array
     * @param string $required_key
     * @param array $allowed_values
     * @param string $default_value
     * @return mixed
     */
    public static function filter_array_for_allowed(array $input_array = [], string $required_key = '', array $allowed_values = [], string $default_value = '')
    {
        return
            \array_key_exists($required_key, $input_array)
                ?
                (
                \in_array($input_array[ $required_key ], $allowed_values)
                    ? $input_array[ $required_key ]
                    : $default_value
                ) : $default_value;
    }

    /**
     * @param $object
     * @param string $path
     * @param string $separator
     * @param mixed|null $default
     * @return mixed|null
     */
    public static function property_get_recursive($object, string $path, string $separator = '->', mixed $default = null)
    {
        $properties = \explode($separator, $path);

        foreach ($properties as $p) {
            if (!\property_exists($object, $p)) {
                return $default;
            } else {
                $object = $object->{$p};
            }
        }
        return $object;
    }

    /**
     * @param $object
     * @param string $path
     * @param string $separator
     * @return bool
     */
    public static function property_exists_recursive($object, string $path, string $separator = '->'): bool
    {
        if (!\is_object($object)) {
            return false;
        }

        $properties = \explode($separator, $path);
        $property = \array_shift($properties);
        if (!\property_exists($object, $property)) {
            return false;
        }

        try {
            $object = $object->$property;
        } catch (Throwable $e) {
            return false;
        }

        if (empty($properties)) {
            return true;
        }

        return property_exists_recursive($object, \implode('->', $properties));
    }

    /**
     *
     * @param string $role
     * @param string $is_publicity
     * @return bool
     */
    public static function isRoleGreater(string $role = 'ANYONE', string $is_publicity = 'ANYONE'): bool
    {
        $MAP_ROLE_TO_INT = [
            'ANYONE'    =>  0,
            'VISITOR'   =>  16,
            'EDITOR'    =>  256,
            'OWNER'     =>  1024,
            'ROOT'      =>  16384
        ];

        $MAP_INT_TO_ROLE = [
            0       =>  'ANYONE',
            16      =>  'VISITOR',
            256     =>  'EDITOR',
            1024    =>  'OWNER',
            16384   =>  'ROOT'
        ];

        return $MAP_ROLE_TO_INT[ $role ] >= $MAP_ROLE_TO_INT[ $is_publicity ];
    }

    /**
     * Проходит по массиву регионов и фильтрует регионы на основе видимости для текущего пользователя на основе прав доступа к контенту
     *
     * Не реализовано
     *
     * @param array $regions_list
     * @return mixed
     */
    public static function checkRegionsVisibleByCurrentUser(array $regions_list):array
    {
        /*$user_id = Auth::getCurrentUser();
        $user_id
            = $user_id
            ? $user_id['uid']
            : ACL::USERID_SUPERADMIN;

        $current_role = ACL::getRole($user_id, $map_alias);

        return array_filter($regions_list, static function ($row) use ($current_role){
            return (bool)ACL::isValidRole( $current_role, $row[ 'is_publicity' ] );
        });*/
        return $regions_list;
    }





}