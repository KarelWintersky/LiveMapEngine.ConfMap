<?php

namespace App;

use Arris\AppLogger;
use Arris\Presenter\Template;
use PDO;
use Psr\Log\LoggerInterface;
use Smarty;

class AbstractClass
{
    public ?\Arris\App $app;

    public PDO $pdo;

    public ?Smarty $smarty;

    public ?Template $template;

    public array $options = [];

    public DBConfigTables $tables;

    public string $map_alias = '';

    public AppLogger\Monolog\Logger $logger;

    public bool $is_internal_request;

    public function __construct($options = [], LoggerInterface $logger = null)
    {
        $this->options = $options;
        $this->tables = new DBConfigTables();

        $this->app = \App\App::factory();
        $this->logger = AppLogger::scope('main');

        $this->pdo = App::$pdo;
        $this->template = App::$template;

        $this->is_internal_request = array_key_exists('mode', $_GET) && $_GET['mode'] == 'internal';

        $this->map_alias = App::$id_map;
    }

}