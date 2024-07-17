<?php

namespace Confmap\Controllers;

use Confmap\AbstractClass;
use Confmap\OpenGraph;
use Psr\Log\LoggerInterface;

class PagesController extends AbstractClass
{
    public function __construct($options = [], LoggerInterface $logger = null)
    {
        parent::__construct($options, $logger);
    }

    public function view_about()
    {
        OpenGraph::makeForMap();
        $this->template->assign("html_title", OpenGraph::$og_default['title']);
        $this->template->setTemplate("_about.tpl");
    }

}