<?php

namespace Confmap\Units;

use Psr\Log\LoggerInterface;

interface MapInterface
{
    /**
     * @param $PDO
     * @param string $id_map
     * @param array $options
     * @param LoggerInterface|null $logger
     */
    public function __construct($PDO = null, string $id_map = '', array $options = [], ?LoggerInterface $logger = null);

}