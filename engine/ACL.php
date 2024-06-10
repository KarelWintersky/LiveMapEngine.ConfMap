<?php

namespace Confmap;

class ACL
{
    /**
     * Временная проверка возможности редактирования регионов на карте
     *
     * Делается на основе списка админских емейлов и списка емейлов в поле `can_edit` определения карты
     *
     * @todo: передавать первым аргументом конфиг карты
     *
     * @param $map_alias
     * @return bool
     */
    public static function simpleCheckCanEdit($map_alias)
    {
        $map = (new MapConfig($map_alias))->loadConfig()->getConfig();
        $admin_emails = getenv('AUTH.ADMIN_EMAILS') ? explode(' ', getenv('AUTH.ADMIN_EMAILS')) : [];
        $allowed_editors = array_merge($map->can_edit ?? [], $admin_emails);

        return !is_null(config('auth.email')) && in_array(config('auth.email'), $allowed_editors);
    }

}