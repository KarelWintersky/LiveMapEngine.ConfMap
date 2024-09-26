<?php

use Arris\AppLogger;
use Arris\AppRouter;
use Arris\Exceptions\AppRouterNotFoundException;
use Confmap\App;
use Confmap\Controllers\AuthController;
use Dotenv\Dotenv;
use Kuria\Error\ErrorHandler;

define('__PATH_ROOT__', dirname(__DIR__));
define('__PATH_CONFIG__', '/etc/arris/livemap.confmap/');

if (!session_id()) @session_start();

try {
    if (!is_file(__PATH_ROOT__ . '/vendor/autoload.php')) {
        throw new RuntimeException("[FATAL ERROR] No 3rd-party libraries installed.");
    }
    require_once __PATH_ROOT__ . '/vendor/autoload.php';

    Dotenv::create( __PATH_CONFIG__, 'common.conf' )->load();
    $app = App::factory();

    App::init();
    App::initErrorHandler();
    App::initLogger();
    App::initTemplate();
    App::initMobileDetect();

    App::initDBConnection();
    App::initAuth();
    App::initRedis();

    AppRouter::init(AppLogger::addScope('router'));
    AppRouter::setDefaultNamespace('\Confmap\Controllers');

    AppRouter::get('/',             [\Confmap\Controllers\MapsController::class, 'view_map_fullscreen'],'view.frontpage');
    AppRouter::get('/about',               [\Confmap\Controllers\PagesController::class, 'view_about'], 'view.about');

    AppRouter::get('/js/confmap.js',[\Confmap\Controllers\JSController::class, 'view_js_map_definition', 'view.map.js']);

    AppRouter::get('/region/get',   [\Confmap\Controllers\RegionsController::class, 'view_region_info'], 'ajax.get.region_info');

    AppRouter::get('/auth/login', [\Confmap\Controllers\AuthController::class, 'view_form_login'], 'view.form.login');
    AppRouter::post('/auth/login', [\Confmap\Controllers\AuthController::class, 'callback_login'], 'callback.form.login');
    AppRouter::get('/auth/logout', [\Confmap\Controllers\AuthController::class, 'callback_logout'], 'view.form.logout');

    AppRouter::group(
        [
            'before'    =>  '\Confmap\Middlewares\AuthMiddleware@check_is_logged_in'
        ], static function() {
            // редактировать регион: форма и коллбэк
            AppRouter::get('/region/edit', [\Confmap\Controllers\RegionsController::class, 'view_region_edit_form'], 'edit.region.info');
            AppRouter::post('/region/edit', [\Confmap\Controllers\RegionsController::class, 'callback_update_region'], 'update.region.info');
        }
    );


    AppRouter::dispatch();

    App::$template->assign("flash_messages", json_encode( App::$flash->getMessages() ));

    App::$template->assign("_auth", \config('auth'));
    App::$template->assign("_request", $_REQUEST);
    App::$template->assign("_config", config());

} catch (\Confmap\Exceptions\AccessDeniedException $e) {

    AppLogger::scope('access.denied')->notice($e->getMessage(), [ $_SERVER['REQUEST_URI'], config('auth.ipv4') ] );

    App::$template->assign('message', $e->getMessage());
    App::$template->setTemplate("_errors/403.tpl");

} catch (AppRouterNotFoundException $e) {

    AppLogger::scope('main')->notice("AppRouter::NotFound", [ $e->getMessage(), $e->getInfo() ] );
    http_response_code(404);

    App::$template->setTemplate("_errors/404.tpl");
    App::$template->assign("message", $e->getMessage());

}/* catch (\RuntimeException|\Exception $e) {
// Пока не внедрим кастомную страницу для Kuria + логгирование там же
// для прода этот блок надо раскомментировать
// для дева - закомментировать (чтобы исключения ловила курия)
// пока что кастомная страницы Курии НИКАКАЯ (и не ведет логи)

    AppLogger::scope('main')->notice("Runtime Error", [ $e->getMessage() ] );
    http_response_code(500);
    App::$template->setTemplate("_errors/500.tpl");
    App::$template->assign("message", $e->getMessage());

    if (getenv('IS.PRODUCTION') == 0) {
        echo "<h1>(RUNTIME) EXCEPTION</h1>";
        echo "<h3>_REQUEST</h3>";
        d($_REQUEST);
        echo "<h3>REQUEST_URI</h3>";
        d($_SERVER['REQUEST_URI']);
        echo "<h3>EXCEPTION DUMP</h3>";
        \Arris\Util\Debug::ddt($e->getTrace());
        dd($e);
    }
}*/

$render = App::$template->render();
if ($render) {
    $render = \preg_replace('/^\h*\v+/m', '', $render); // удаляем лишние переводы строк
    echo $render;
}

logSiteUsage( AppLogger::scope('site_usage') );

if (App::$template->isRedirect()) {
    App::$template->makeRedirect();
}

# -eof- #
