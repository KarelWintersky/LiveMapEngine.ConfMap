<?php

namespace Confmap;

use Arris\Path;
use ColinODell\Json5\SyntaxError;
use Confmap\Units\Map;

class ACL
{
    const MAP_ROLE_TO_INT = [
        'ANYONE'    =>  0,
        'VISITOR'   =>  16,
        'EDITOR'    =>  256,
        'OWNER'     =>  1024,
        'ROOT'      =>  16384
    ];

    const MAP_INT_TO_ROLE = [
        0       =>  'ANYONE',
        16      =>  'VISITOR',
        256     =>  'EDITOR',
        1024    =>  'OWNER',
        16384   =>  'ROOT'
    ];

    /**
     * Временная проверка возможности редактирования регионов на карте
     *
     * Делается на основе списка админских емейлов и списка емейлов в поле `can_edit` определения карты
     *
     * @param $map_alias
     * @return bool
     * @throws SyntaxError
     * @todo: передавать первым аргументом конфиг карты
     *
     */
    public static function simpleCheckCanEdit($map_alias)
    {
        $map = new Map(App::$pdo, $map_alias);
        $map->loadConfig(
            Path::create( config('path.storage') )->join($map_alias)
        );
        $admin_emails = getenv('AUTH.ADMIN_EMAILS') ? explode(' ', getenv('AUTH.ADMIN_EMAILS')) : [];

        $allowed_editors = array_merge($map->mapConfig->can_edit ?? [], $admin_emails);

        return !is_null(config('auth.email')) && in_array(config('auth.email'), $allowed_editors);
    }

    /**
     * @param string $role
     * @param string $is_publicity - enum('ANYONE','VISITOR','EDITOR','OWNER','ROOT')
     * @return bool
     */
    public static function isRoleGreater(string $role = 'ANYONE', string $is_publicity = 'ANYONE')
    {
        return self::MAP_ROLE_TO_INT[ $role ] >= self::MAP_ROLE_TO_INT[ $is_publicity ];
    }

}