<?php

namespace App;

use Arris\AppLogger;
use Arris\Cache\Cache;
use Arris\Core\Dot;
use Arris\Database\Config;
use Arris\Database\Connector;
use Arris\DelightAuth\Auth\Auth;
use Arris\Path;
use Arris\Presenter\FlashMessages;
use Arris\Presenter\Template;
use Arris\Toolkit\RedisClientException;
use Kuria\Error\ErrorHandler;
use LiveMapEngine\Auth\AccessControl;

class App extends \Arris\App
{
    public static string $id_map = 'spring.confederation';

    /**
     * @var Connector
     */
    public static Connector $pdo;

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
    public static Template $template;

    /**
     * @var FlashMessages
     */
    public static FlashMessages $flash;
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
        AppLogger::scope('router');
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
        App::$pdo = (new Config())
            ->setHost(getenv('DB.HOST'))
            ->setPort(getenv('DB.PORT'))
            ->setUsername(getenv('DB.USERNAME'))
            ->setPassword( getenv('DB.PASSWORD'))
            ->setDatabase(getenv('DB.NAME'))
            ->connect();
    }

    public static function initAuth()
    {
        App::$acl = new AccessControl();

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

    /**
     * @throws RedisClientException
     */
    public static function initRedis()
    {
        Cache::init(
            redis_host: getenv('REDIS.HOST'),
            redis_port: getenv('REDIS.PORT'),
            redis_database: getenv('REDIS.DATABASE'),
            redis_enabled: getenv('REDIS.ENABLED'),
            PDO: App::$pdo,
            logger: AppLogger::scope('redis')
        );
    }

    /**
     * @throws \SmartyException
     */
    public static function initPresenter()
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
    }

    /**
     * @return void
     */
    public static function initFlashMessages(): void
    {
        App::$flash = new FlashMessages();

        App::$template->assign("flash_messages", json_encode( App::$flash->getMessages() ));
    }


}