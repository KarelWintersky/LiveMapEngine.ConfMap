<?php

namespace Confmap\Controllers;

use Arris\AppRouter;
use Arris\Path;
use Arris\Template\Template;
use ColinODell\Json5\SyntaxError;
use Confmap\AbstractClass;
use Confmap\App;
use Confmap\Exceptions\AccessDeniedException;
use Confmap\Units\Map;
use LiveMapEngine\ContentExtra\Confmap\ConfmapHandler;
use LiveMapEngine\DataCollection\DataCollection;
use LiveMapEngine\Map\MapMaker;
use Psr\Log\LoggerInterface;

class RegionsController extends AbstractClass
{
    private MapMaker $map;

    public function __construct($options = [], LoggerInterface $logger = null)
    {
        parent::__construct($options, $logger);

        $id_map = App::$id_map;

        // Выносим в конструктор, но только по одной причине: ID карты для проекта livemap.confmap ИЗВЕСТЕН
        $this->map = new MapMaker($this->pdo, $id_map, [
            'path_storage'  =>  Path::create( config('path.storage') ),
            'path_config'   =>  Path::create( config('path.storage') )->join($id_map),
            'json_parser'   =>  [Map::class, 'parseJSONFile']
        ]);
    }

    /**
     * Показ инфы по региону
     *
     * @return void
     * @throws \ColinODell\Json5\SyntaxError
     * @throws \JsonException
     * @throws \SmartyException
     */
    public function view_region_info(): void
    {
        $id_region = $_GET['id'] ?? null;

        if ($this->map->loadConfig()->is_error) {
            throw new \RuntimeException($this->map->state->getMessage());
        }

        $region_data = $this->map->getMapRegionData($id_region, [ 'title', 'content', 'content_restricted', 'content_extra']);

        $t = new Template();
        $t
            ->setTemplateDir( config('smarty.path.template'))
            ->setCompileDir( config('smarty.path.cache'))
            ->setForceCompile( config('smarty.force_compile'))
            ->registerPlugin(Template::PLUGIN_MODIFIER, 'dd', 'dd', false)
            ->registerClass("Arris\AppRouter", "Arris\AppRouter");

        $t->assign('is_present', $region_data['is_present']);
        $t->assign('map_alias', App::$id_map);
        $t->assign('region_id', $id_region);
        $t->assign('region_title', $region_data['title']);
        $t->assign('region_text', $region_data['content']);
        $t->assign('is_can_edit', $region_data['can_edit']);
        $t->assign('edit_button_url', AppRouter::getRouter('update.region.info'));
        $t->assign('is_display_extra_content', $region_data['is_display_extra_content'] ?? 0);

        $t->assign("view_mode", $this->map->getConfig('display->viewmode')); // тип просмотра для шаблона

        $extra_data_handler = new ConfmapHandler();
        $content_extra = $extra_data_handler->renderView($region_data['content_extra'] ?? '{}');

        // @TODO: ВАЖНО, В ШАБЛОНЕ ХОДИМ ТАК: {$json->economy->type}, А НЕ ЧЕРЕЗ ТОЧКУ!!!!
        $t->assign('content_extra', $content_extra);

        $t->setTemplate('view.region/view.region.tpl');

        $content = $t->render(false);

        $this->template->assignRAW($content);
    }

    /**
     д*/
    public function view_region_edit_form()
    {
        $id_region = $_GET['id']    ?? null;

        if (is_null($id_region)) {
            throw new AccessDeniedException("Неправильно передан ID региона, доступ запрещён");
        }

        if ($this->map->loadConfig()->is_error) {
            throw new \RuntimeException($this->map->state->getMessage());
        }

        if (!$this->map->simpleCheckCanEdit()) {
            throw new AccessDeniedException("Обновление региона недоступно, недостаточный уровень допуска");
        }

        $this->template->assign("html_callback", AppRouter::getRouter('view.frontpage'));
        $this->template->assign("form_actor", AppRouter::getRouter('update.region.info'));
        $this->template->setTemplate("edit.region/_edit.region.tpl");

        $content_fields = [ 'title', 'content', 'content_restricted', 'content_extra' ];

        $region_data = $this->map->getMapRegionData($id_region, $content_fields, 'OWNER', false);

        //@todo: в других подобных местах надо фильтровать контент по доступности. Но не тут!

        $this->template->assign("content", $region_data['content']);
        $this->template->assign("content_title", ($region_data['is_present'] == 1) ? htmlspecialchars($region_data['title'],  ENT_QUOTES | ENT_HTML5) : '');
        $this->template->assign("content_restricted", htmlspecialchars($region_data['content_restricted'] ?? '', ENT_QUOTES | ENT_HTML5));

        // Конвертируем значение поля content_extra в JSON-структуру и передаем её в шаблон
        // Если associative: true - то доступ через точку, иначе через стрелочку
        $extra_data_handler = new ConfmapHandler();
        $content_extra = $extra_data_handler->renderEdit($region_data['content_extra'] ?? '{}');
        // было '{}' - но мы пока передаем ассоциативный массив, а не объект. Для передачи объекта надо править шаблон.

        $this->template->assign("content_extra", $content_extra);

        // и больше никаких действий здесь не требуется!
        // магия сохранения будет в коллбэке!

        $this->template->assign([
            'id_region'         =>  $id_region,
            'id_map'            =>  App::$id_map,
            'title_map'         =>  $this->map->mapConfig->title,
            'html_callback'     =>  AppRouter::getRouter('view.frontpage'),
            'is_present'        =>  $region_data['is_present'],      // 1 - регион существует, 0 - новый регион

            'is_logged_user'    =>  config('auth.username'),
            'is_logged_user_ip' =>  config('auth.ipv4'),

            // copyright
            'copyright'         =>  config('app.copyright'),

            // revisions
            // 'region_revisions'  =>  $map_engine->getRegionRevisions( $map_alias, $region_id ),

            'is_exludelists'    =>  $region_data['is_exludelists'] ?? 'N',
            'is_publicity'      =>  $region_data['is_publicity'] ?? 'ANYONE',

            // использовать экстра-контент
            "is_display_extra_content" =>  $region_data["is_display_extra_content"] ?? 0 //@todo: ? флаг в конфиге карты: "имеется экстра-контент по-умолчанию" ?
        ]);

        // Устанавливаем нативный флаг для Smarty->escape_html = true. Автоэскейп актуален только для формы редактирования
        // (чтобы в инпутах можно было редактировать строки с кавычками)
        $this->template->setSmartyNativeOption("escape_html", true);

        //@todo: магия передачи пути к каталогу изображений карты через куки
        setcookie( getenv('AUTH.COOKIES.FILEMANAGER_STORAGE_PATH'), App::$id_map, 0, '/');
        setcookie( getenv('AUTH.COOKIES.FILEMANAGER_CURRENT_MAP'), App::$id_map, 0, '/');
    }

    /**
     * @throws SyntaxError
     */
    public function callback_update_region()
    {
        if ($this->map->loadConfig()->is_error) {
            throw new \RuntimeException($this->map->state->getMessage());
        }

        if (!$this->map->simpleCheckCanEdit()) {
            throw new AccessDeniedException("Обновление региона недоступно, недостаточный уровень допуска");
        }

        $request = new DataCollection();
        $request->import($_REQUEST); // separator пустой, данные - асс.массив, данные не парсим

        $data = [
            'id_map'            =>  $request->getData('edit:id:map'),
            // в общем проекте LiveMap значение берется из ID текущего пользователя:
            // 'edit_whois'        =>  App::$acl->currentUser->id,
            // В редакторе карты КОНФЕДЕРАЦИИ значение равно 0 (т.к. редактор один)
            'edit_whois'        =>  0,
            'edit_ipv4'         =>  ip2long(\Arris\Helpers\Server::getIP()),

            'id_region'         =>  $request->getData('edit:id:region'),
            'title'             =>  $request->getData('edit:region:title'),
            'content'           =>  $request->getData('edit:region:content'),
            'content_restricted'=>  $request->getData('edit:region:content_restricted'),
            'edit_comment'      =>  $request->getData('edit:region:comment'),
            'is_excludelists'   =>  $request->getData('edit:is:excludelists'),
            'is_publicity'      =>  $request->getData('edit:is:publicity'),
            'is_display_extra_content'  => $request->getData('is_display_extra_content', default: 'no', casting: function ($data){
                return (strtolower($data) == 'on') ? 1 : 0; // см DataCollection.md
            })
        ];

        // Каждое кастомное поле нужно описать в методе parseEditData(), завернуть в JSON-структуру и её строковое представление записать в БД
        $content_extra_handler = new ConfmapHandler();
        $data['content_extra'] = $content_extra_handler->parseEditData();

        $result = $this->map->storeMapRegionData($data);

        if ($result->is_error) {
            throw new \RuntimeException($result->getMessage());
        }

        // assign
        $this->template->assignResult($result);
    }


}

# -eof- #