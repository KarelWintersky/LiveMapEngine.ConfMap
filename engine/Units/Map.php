<?php

namespace Confmap\Units;

class Map
{
    public static function parseJSONFile($fn)
    {
        return json5_decode($fn);
    }

}