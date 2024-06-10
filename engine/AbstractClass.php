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

    public $tables;

    /**
     * @var \Monolog\Logger
     */
    public $logger;

    public bool $is_internal_request;

    public function __construct($options = [], LoggerInterface $logger = null)
    {
        $this->app = \Confmap\App::factory();
        $this->logger = AppLogger::scope('main');

        $this->options = $options;

        $this->tables = new DBConfigTables();
        $this->template = App::$template;

        $this->is_internal_request = array_key_exists('mode', $_GET) && $_GET['mode'] == 'internal';
    }

}