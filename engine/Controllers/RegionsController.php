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
use LiveMapEngine\MapMaker;
use Psr\Log\LoggerInterface;

class RegionsController extends AbstractClass
{
    const JSON_ENCODE_FLAGS = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK | JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR;

    private MapMaker $map;

    public function __construct($options = [], LoggerInterface $logger = null)
    {
        parent::__construct($options, $logger);

        $id_map = App::$id_map;

        // Выносим в конструктор, но только по одной причине: ID карты для проекта livemap.confmap ИЗВЕСТЕН
        $this->map = new MapMaker($this->pdo, $id_map, [
            'config_path'   =>  Path::create( config('path.storage') )->join($id_map),
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
    public function view_region_info()
    {
        $id_region = $_GET['id']    ?? null;

        if ($this->map->loadConfig()->is_error) {
            throw new \RuntimeException($this->map->state->getMessage());
        }

        $region_data = $this->map->getMapRegionData($id_region, [ 'title', 'content', 'content_restricted']);

        $t = new Template();
        $t
            ->setTemplateDir( config('smarty.path.template'))
            ->setCompileDir( config('smarty.path.cache'))
            ->setForceCompile( config('smarty.force_compile'))
            ->registerClass("Arris\AppRouter", "Arris\AppRouter");

        $t->assign('is_present', $region_data['is_present']);
        $t->assign('map_alias', App::$id_map);
        $t->assign('region_id', $id_region);
        $t->assign('region_title', $region_data['title']);
        $t->assign('region_text', $region_data['content']);
        $t->assign('is_can_edit', $region_data['can_edit']);
        $t->assign('edit_button_url', AppRouter::getRouter('update.region.info'));
        $t->setTemplate('view.region/view.region.html.tpl');

        $content = $t->render(false);

        $this->template->assignRAW($content);
    }

    /**
     * @throws SyntaxError
     */
    public function view_region_edit_form()
    {
        if ($this->map->loadConfig()->is_error) {
            throw new \RuntimeException($this->map->state->getMessage());
        }

        if (!$this->map->simpleCheckCanEdit()) {
            throw new AccessDeniedException("Обновление региона недоступно, недостаточный уровень допуска");
        }

        $this->template->assign("html_callback", AppRouter::getRouter('view.frontpage'));
        $this->template->assign("form_actor", AppRouter::getRouter('update.region.info'));
        $this->template->setTemplate("_edit.region.tpl");

        $id_region = $_GET['id']    ?? null;

        $content_fields = [ 'title', 'content', 'content_restricted', 'content_json' ];

        $region_data = $this->map->getMapRegionData($id_region, $content_fields);

        $this->template->assign("content", $region_data['content']);
        $this->template->assign("content_title", ($region_data['is_present'] == 1) ? htmlspecialchars($region_data['title'],  ENT_QUOTES | ENT_HTML5) : '');
        $this->template->assign("content_restricted", htmlspecialchars($region_data['content_restricted'] ?? '', ENT_QUOTES | ENT_HTML5));

        // Конвертируем значение поля content_json в JSON-структуру и передаем её в шаблон
        $this->template->assign("json", json_decode($region_data['content_json'] ?? '', true));
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
        ]);

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

        $data = [
            'id_map'            =>  $_REQUEST['edit:id:map'],
            'edit_whois'        =>  0,
            'edit_ipv4'         =>  ip2long(\Arris\Helpers\Server::getIP()),
            'id_region'         =>  $_REQUEST['edit:id:region'],
            'title'             =>  $_REQUEST['edit:region:title'],
            'content'           =>  $_REQUEST['edit:region:content'],
            'content_restricted'=>  $_REQUEST['edit:region:content_restricted'],
            'edit_comment'      =>  $_REQUEST['edit:region:comment'],
            'is_excludelists'   =>  $_REQUEST['edit:is:excludelists'],
            'is_publicity'      =>  $_REQUEST['edit:is:publicity']
        ];

        // Каждое кастомное поле нужно описать здесь и передать в будущую JSON-структуру
        $json = [
            'lsi'       =>  [
                'index'     =>  self::json('lsi-index'),
                'type'      =>  self::json('lsi-type'),
                'climate'   =>  self::json('lsi-climate')
            ],
            'history'   =>  [
                'year'      =>  self::json('history-year'),
                'text'      =>  self::json('history-text')
            ]
        ];

        // пакуем контент в JSON
        $data['content_json'] = json_encode($json, self::JSON_ENCODE_FLAGS);

        $result = $this->map->storeMapRegionData($data);

        if ($result->is_error) {
            throw new \RuntimeException($result->getMessage());
        }

        // logging

        // assign
        $this->template->assignResult($result);
    }

    /**
     * Хелпер для доступа к REQUEST json: полям
     *
     * @param string $field
     * @return mixed|string
     */
    private static function json(string $field = ''): string
    {
        return empty($field) ? '' : $_REQUEST["json:{$field}"];
    }

}