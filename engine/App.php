<?php

namespace Confmap;

use AJUR\Template\FlashMessages;
use Arris\AppLogger;
use Arris\Core\Dot;
use Arris\Database\DBWrapper;
use Arris\DelightAuth\Auth\Auth;
use Arris\Path;
use Arris\Template\Template;
use Kuria\Error\ErrorHandler;
use Arris\Helpers\Server;

class App extends \Arris\App
{
    /**
     * @var DBWrapper
     */
    public static DBWrapper $pdo;

    /**
     * @var Dot
     */
    public static Dot $config;

    /**
     * @var Auth
     */
    public static $auth;

    /**
     * @var Template;
     */
    public static $template;

    /**
     * @var FlashMessages
     */
    public static $flash;

    public static function init()
    {
        $app = App::factory();

        $_path_install = Path::create( getenv('PATH.INSTALL') );
        $_path_monolog = Path::create( getenv('PATH.LOGS') );

        config('path', [
            'install'   =>  $_path_install->toString(true),
            'logs'      =>  $_path_monolog->toString(true),
            'public'    =>  $_path_install->join('public')->toString('/'),
            'cache'     =>  $_path_install->join('cache')->toString('/'),
            'storage'   =>  getenv('PATH.STORAGE')
        ]);

        config('smarty', [
            'path_template'     =>  $_path_install->join('templates')->toString(true),
            'path_cache'        =>  config('path.cache'),
            'force_compile'     =>  _env('DEBUG.SMARTY_FORCE_COMPILE', false, 'bool')
        ]);

        config('app', [
            'copyright'     =>  getenv('COPYRIGHT') ?? 'LiveMap Engine version 1.5+ "Algrist"',
        ]);
    }

    public static function initErrorHandler()
    {
        $is_debug = !_env('IS.PRODUCTION', false, 'bool');
        $errorHandler = new ErrorHandler();
        $errorHandler->setDebug($is_debug);
        error_reporting(E_ALL & ~E_NOTICE);
        $errorHandler->register();
    }

    public static function initLogger()
    {
        AppLogger::init('Confmap', bin2hex(\random_bytes(8)), [
            'default_logfile_path'      => config('path.logs'),
            'default_logfile_prefix'    => date_format(date_create(), 'Y-m-d') . '__'
        ] );
    }

    public static function initMobileDetect()
    {
        $MOBILE_DETECT_INSTANCE = new \Detection\MobileDetect();
        config('features', [
            'is_cli'        =>  PHP_SAPI === "cli",
            'is_mobile'     =>  PHP_SAPI !== "cli" && $MOBILE_DETECT_INSTANCE->isMobile(),
            'is_iphone'     =>  $MOBILE_DETECT_INSTANCE->is('iPhone'),
            'is_android'    =>  $MOBILE_DETECT_INSTANCE->is('Android'),
        ]);
    }

    public static function initDBConnection()
    {
        $app = self::factory();

        /**
         * Database
         */
        $db_credentials = [
            'driver'            =>  'mysql',
            'hostname'          =>  getenv('DB.HOST'),
            'database'          =>  getenv('DB.NAME'),
            'username'          =>  getenv('DB.USERNAME'),
            'password'          =>  getenv('DB.PASSWORD'),
            'port'              =>  getenv('DB.PORT'),
            'charset'           =>  'utf8',
            'charset_collate'   =>  'utf8_general_ci',
            'slow_query_threshold'  => 1
        ];
        config('db_credentials', $db_credentials);

        App::$pdo = new DBWrapper(
            config('db_credentials'),
            [ 'slow_query_threshold' => 100 ],
            AppLogger::scope('mysql')
        );
        $app->addService('pdo', App::$pdo);
    }

    public static function initAuth()
    {
        $app = self::factory();

        /**
         * Auth Delight
         */
        App::$auth = new Auth(new \PDO(
            sprintf(
                "mysql:dbname=%s;host=%s;charset=utf8mb4",
                config('db_credentials.database'),
                config('db_credentials.hostname')
            ),
            config('db_credentials.username'),
            config('db_credentials.password')
        ));
        $app->addService(Auth::class, App::$auth);
        config('auth', [
            'id'            =>  App::$auth->id(),
            'is_logged_in'  =>  App::$auth->isLoggedIn(),       // флаг "залогинен"
            'username'      =>  App::$auth->getUsername(),      // пользователь
            'email'         =>  App::$auth->getEmail(),
            'ipv4'          =>  Server::getIP(),                // IPv4

            'is_admin'      =>  App::$auth->hasRole(\Confmap\AuthRoles::ADMIN),
        ]);

    }

    public static function initRedis()
    {
        \Arris\Cache\Cache::init([
            'enabled'   =>  getenv('REDIS.ENABLED'),
            'host'      =>  getenv('REDIS.HOST'),
            'port'      =>  getenv('REDIS.PORT'),
            'password'  =>  getenv('REDIS.PASSWORD'),
            'database'  =>  getenv('REDIS.DATABASE')
        ], [ ], App::$pdo, AppLogger::scope('redis'));
    }

    public static function initTemplate()
    {

    }


}