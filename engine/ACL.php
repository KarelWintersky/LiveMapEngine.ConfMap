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
     * @param string $role
     * @param string $is_publicity - enum('ANYONE','VISITOR','EDITOR','OWNER','ROOT')
     * @return bool
     */
    public static function isRoleGreater(string $role = 'ANYONE', string $is_publicity = 'ANYONE')
    {
        return self::MAP_ROLE_TO_INT[ $role ] >= self::MAP_ROLE_TO_INT[ $is_publicity ];
    }

}