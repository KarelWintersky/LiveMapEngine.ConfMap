<?php

namespace Confmap\Controllers;

use Confmap\AbstractClass;
use Psr\Log\LoggerInterface;

class PagesController extends AbstractClass
{
    public function __construct($options = [], LoggerInterface $logger = null)
    {
        parent::__construct($options, $logger);
    }

    public function view_about()
    {
        $this->template->setTemplate("_about.tpl");
    }

}