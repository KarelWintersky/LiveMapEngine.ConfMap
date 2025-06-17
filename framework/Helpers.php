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
    public static function filter_array_for_allowed(array $input_array = [], string $required_key = '', array $allowed_values = [], string $default_value = ''): mixed
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
    public static function property_get_recursive($object, string $path, string $separator = '->', mixed $default = null): mixed
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
     * https://gist.github.com/nyamsprod/10adbef7926dbc449e01eaa58ead5feb
     *
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

        return Helpers::property_exists_recursive($object, \implode('->', $properties));
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

    /**
     * @param string $val
     * @return float
     */
    public static function floatvalue(string $val): float
    {
        $val = \str_replace(",",".",$val);
        $val = \preg_replace('/\.(?=.*\.)/', '', $val);
        return \floatval($val);
    }


    /**
     * Возвращает результирующий конфиг, в котором опции дефолтного конфига заменены на опции кастомного конфига,
     * а несуществующие в кастомном оставлены как есть в дефолтном.
     *
     * При `include_new_params = true` добавит в результирующий конфиг опции из кастомного даже если они не существуют в дефолтном.
     *
     * @todo: Arris.helpers ?
     *
     * @param array $default_config
     * @param array $custom_config
     * @param bool $include_new_params
     * @return array
     */
    public static function mergeConfigs(array $default_config, array $custom_config, bool $include_new_params = false): array
    {
        $result = $default_config;

        foreach ($custom_config as $key => $value) {
            // Если ключ существует в дефолтном конфиге
            if (array_key_exists($key, $default_config)) {
                // Если оба значения являются массивами - рекурсивно объединяем
                if (is_array($value) && is_array($default_config[$key])) {
                    $result[$key] = self::mergeConfigs($default_config[$key], $value, $include_new_params);
                } else {
                    // Заменяем значение
                    $result[$key] = $value;
                }
            } elseif ($include_new_params) {
                // Если ключа нет в дефолтном, но включено добавление новых параметров
                $result[$key] = $value;
            }
        }

        return $result;
    }



}