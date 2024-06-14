<?php

namespace Confmap;

#[\AllowDynamicProperties]
final class DBConfigTables
{
    public $folders;

    public $files;

    public $log_download;

    public $log_view;

    public $log_actions;

    public $users;

    public string $map_data_regions;

    public function __construct()
    {
        $this->log_download = 'log_download';
        $this->log_view     = 'log_view';
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