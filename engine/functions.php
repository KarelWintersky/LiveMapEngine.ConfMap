<?php

use Arris\Database\DBWrapper;
use Confmap\App;
use Psr\Log\LoggerInterface;
use Arris\Helpers\Server;

/**
 * @param array|string $key
 * @param $value [optional]
 * @return string|array|bool|mixed|null
 */
function config(array|string $key = '', $value = null): mixed
{
    $app = App::factory();

    if (!is_null($value) && !empty($key)) {
        $app->setConfig($key, $value);
        return true;
    }

    if (is_array($key)) {
        foreach ($key as $k => $v) {
            $app->setConfig($k, $v);
        }
        return true;
    }

    if (empty($key)) {
        return $app->getConfig();
    }

    return $app->getConfig($key);
}

/**
 * https://gist.github.com/nyamsprod/10adbef7926dbc449e01eaa58ead5feb
 *
 * @param $object
 * @param string $path
 * @param string $separator
 * @return bool
 */
function property_exists_recursive($object, string $path, string $separator = '->'): bool
{
    if (!\is_object($object)) {
        return false;
    }

    $properties = \explode($separator, $path);
    $property = \array_shift($properties);
    if (!\property_exists($object, $property)) {
        return false;
    }

    try {
        $object = $object->$property;
    } catch (Throwable $e) {
        return false;
    }

    if (empty($properties)) {
        return true;
    }

    return \property_exists_recursive($object, \implode('->', $properties));
}

function logSiteUsage(LoggerInterface $logger, $is_print = false)
{
    $metrics = [
        'time.total'        =>  number_format(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 6, '.', ''),
        'memory.usage'      =>  memory_get_usage(true),
        'memory.peak'       =>  memory_get_peak_usage(true),
        'site.url'          =>  $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
        'isMobile'          =>  config('features.is_mobile'),
    ];

    /**
     * @var DBWrapper $pdo
     */
    $pdo = (App::factory())->getService('pdo');

    if (!is_null($pdo)) {
        $stats = $pdo->getStats();
        $metrics['mysql.queries'] = $stats['total_queries'];
        $metrics['mysql.time'] = $stats['total_time'];
    }

    $metrics['ipv4'] = Server::getIP();

    $logger->notice('', $metrics);
}
