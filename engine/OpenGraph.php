<?php

namespace App;

class OpenGraph
{
    public static $og_default;
    private static function getDefault(): array
    {
        return self::$og_default = [
                'url'           =>  "{$_SERVER['REQUEST_SCHEME']}://{$_SERVER['HTTP_HOST']}/",
                'type'          =>  'website',
                'title'         =>  "Звездный атлас Конфедерации Человечества",
                'description'   =>  "Звездный атлас Конфедерации Человечества",
                'image'         =>  "{$_SERVER['REQUEST_SCHEME']}://{$_SERVER['HTTP_HOST']}/frontend/images/og_image.png",
                'logo'          =>  "{$_SERVER['REQUEST_SCHEME']}://{$_SERVER['HTTP_HOST']}/frontend/images/og_image.png",
                'site_name'     =>  "Звездный атлас Конфедерации Человечества",

                'domain'        =>  $_SERVER['HTTP_HOST'],
            ];
    }

    /**
     * Генерирует OpenGraph-информацию для страницы
     *
     * @param string|null $map_alias
     * @param \stdClass|null $map
     * @return array
     */
    public static function makeForMap(?string $map_alias = null, \stdClass $map = null): array
    {
        return self::getDefault();
    }

}