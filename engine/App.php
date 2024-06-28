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
use LiveMapEngine\Auth\AccessControl;

class App extends \Arris\App
{
    public static $id_map = 'spring.confederation';
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
    // public static $auth;

    /**
     * @var Template;
     */
    public static $template;

    /**
     * @var FlashMessages
     */
    public static $flash;
    /**
     * @var AccessControl
     */
    public static AccessControl $acl;

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
            'path'  =>  [
                'template'  =>  $_path_install->join('templates')->toString(true),
                'cache'     =>  config('path.cache')
            ],
            'force_compile'     =>  _env('DEBUG.SMARTY_FORCE_COMPILE', false, 'bool')
        ]);

        config('app', [
            'copyright'     =>  'Confmap Pre-Release (based on LiveMap Engine v2+)',
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
        config('credentials.db', $db_credentials);

        App::$pdo = new DBWrapper(
            config('credentials.db'),
            [ 'slow_query_threshold' => 100 ],
            AppLogger::scope('mysql')
        );
        $app->addService('pdo', App::$pdo);
    }

    public static function initAuth()
    {
        App::$acl = new AccessControl([
            'hostname'  =>  config('credentials.db.hostname'),
            'database'  =>  config('credentials.db.database'),
            'username'  =>  config('credentials.db.username'),
            'password'  =>  config('credentials.db.password')
        ]);

        config('auth', [
            'id'            =>  App::$acl->currentUser->id,
            'is_logged_in'  =>  App::$acl->currentUser->is_logged_in,
            'username'      =>  App::$acl->currentUser->username,
            'email'         =>  App::$acl->currentUser->email,
            'ipv4'          =>  App::$acl->currentUser->ipv4,

            'is_admin'      =>  App::$acl->currentUser->is_admin
        ]);

        // config('auth', App::$acl->currentUser);

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

    /**
     * @throws \SmartyException
     */
    public static function initTemplate()
    {
        App::$template = new Template([], [], AppLogger::scope('template'));

        App::$template
            ->setTemplateDir( config('smarty.path.template'))
            ->setCompileDir( config('smarty.path.cache'))
            ->setForceCompile( config('smarty.force_compile'))
            ->registerPlugin( Template::PLUGIN_MODIFIER, 'dd', 'dd', false)
            ->registerPlugin(Template::PLUGIN_FUNCTION, "_env", static function($params)
            {
                $default = (empty($params['default'])) ? '' : $params['default'];
                if (empty($params['key'])) return $default;
                $k = getenv($params['key']);
                return ($k === false) ? $default : $k;
            }, false )
            ->registerPlugin(Template::PLUGIN_FUNCTION, "config", static function($params)
            {
                return empty($params['key']) ? config() : config($params['key']);
            }, false)
            ->registerPlugin(Template::PLUGIN_MODIFIER, 'getenv', 'getenv', false)
            ->registerClass("Arris\AppRouter", "Arris\AppRouter");

        App::$template->setTemplate("_map.tpl");

        App::$flash = new FlashMessages();
    }


}