<?php

namespace Confmap;

use Arris\App;
use Arris\AppLogger;
use Arris\Database\DBWrapper;
use Arris\Template\Template;
use Psr\Log\LoggerInterface;
use Smarty;

#[AllowDynamicProperties]
class AbstractClass
{
    public App $app;

    public DBWrapper $pdo;

    public Smarty $smarty;

    public Template $template;

    public array $options = [];

    public $tables;

    /**
     * @var \Monolog\Logger
     */
    public $logger;

    public bool $is_internal_request;

    public function __construct($options = [], LoggerInterface $logger = null)
    {
        $this->app = App::factory();
        $this->logger = AppLogger::scope('main');

        $this->options = $options;

        $this->tables = new DBConfigTables();

        $this->is_internal_request = array_key_exists('mode', $_GET) && $_GET['mode'] == 'internal';
    }

}