<?php

namespace Confmap;

use Arris\AppLogger;
use Arris\Database\DBWrapper;
use Arris\Template\Template;
use Psr\Log\LoggerInterface;
use Smarty;

class AbstractClass
{
    public ?\Arris\App $app;

    public ?DBWrapper $pdo;

    public ?Smarty $smarty;

    public ?Template $template;

    public array $options = [];

    public DBConfigTables $tables;

    public string $map_alias = '';

    /**
     * @var \Monolog\Logger
     */
    public $logger;

    public bool $is_internal_request;

    public function __construct($options = [], LoggerInterface $logger = null)
    {
        $this->options = $options;
        $this->tables = new DBConfigTables();

        $this->app = \Confmap\App::factory();
        $this->logger = AppLogger::scope('main');

        $this->pdo = App::$pdo;
        $this->template = App::$template;

        $this->is_internal_request = array_key_exists('mode', $_GET) && $_GET['mode'] == 'internal';

        $this->map_alias = App::$map_id;
    }

}