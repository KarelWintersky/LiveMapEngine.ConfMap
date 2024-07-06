<?php

namespace Confmap;

#[\AllowDynamicProperties]
final class DBConfigTables
{
    /**
     * @var
     */
    public string $projects;

    public string $maps; 

    public string $log_actions;

    public string $users;

    public string $map_data_regions;

    public function __construct()
    {
        $this->log_actions  = 'log_actions';

        /*
         * Delight-im AUTH tables
         */
        $this->users = 'users';

        /**
         * Livemap Engine tables
         */
        $this->map_data_regions = 'map_data_regions';

    }

}