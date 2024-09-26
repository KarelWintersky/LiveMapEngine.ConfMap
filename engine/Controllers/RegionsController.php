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
use LiveMapEngine\DataCollection;
use LiveMapEngine\Helpers;
use LiveMapEngine\Map\MapMaker;
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
        $t->assign('is_display_extra_content', $region_data['is_display_extra_content']);

        $t->assign("view_mode", $this->map->getConfig('display->viewmode')); // тип просмотра для шаблона

        $content_extra = $region_data['content_extra'] ?? '{}';

        /*
        Для упрощения построения круговой диаграммы долей экономики часть расчетов сделаем здесь.
        */
        $json = new DataCollection($content_extra);
        $json->setSeparator('->');
        $json->parse();

        $pcd_natural = $json->getData("economy->shares->natural", 0, casting: 'int');
        $pcd_financial = $json->getData("economy->shares->financial", 0, casting: 'int');
        $pcd_industrial = $json->getData("economy->shares->industrial", 0, casting: 'int');
        $pcd_social = $json->getData("economy->shares->social", 0, casting: 'int');
        $pcd_sum = $pcd_natural + $pcd_financial + $pcd_industrial + $pcd_social;

        $pie_chart_data = [];
        if ($pcd_sum > 0) {
            // единичка экономики = сектору в 30 градусов
            $pie_chart_data[] = [
                'data'  =>  $pcd_natural * 30,
                'label' =>  round( $pcd_natural / $pcd_sum, 2 )*100 . '%',
                'color' =>  '#77e359',
                'hint'  =>  'Природная'
            ];
            $pie_chart_data[] = [
                'data'  =>  $pcd_financial * 30,
                'label' =>  round( $pcd_financial / $pcd_sum, 2 )*100 . '%',
                'color' =>  '#ff85ef',
                'hint'  =>  'Финансовая'
            ];
            $pie_chart_data[] = [
                'data'  =>  $pcd_industrial * 30,
                'label' =>  round( $pcd_industrial / $pcd_sum, 2 )*100 . '%',
                'color' =>  '#ed8d26',
                'hint'  =>  'Реальная'
            ];
            $pie_chart_data[] = [
                'data'  =>  $pcd_social * 30,
                'label' =>  round( $pcd_social / $pcd_sum, 2 )*100 . '%',
                'color' =>  '#8bd5f7',
                'hint'  =>  'Социальная'
            ];
        }

        // форматируем население (численность, не население!)
        // используем casting как closure
        $population = $json->getData(path: 'population->count', default: 0, casting: function($population) {
            if ($population >= 1) {
                // миллионы
                return number_format($population, 0, '.', ' ') . ' млн.';
            } elseif ($population > 0) {
                // меньше миллиона, тысячи
                return number_format($population * 1000, 0, '.', ' ') . ' тыс.';
            } else {
                return 0;
            }
        });

        $json->setData('population->count', $population);

        // круговая диаграмма
        $t->assign("pie_chart", [
            'present'   =>  $pcd_sum > 0,
            'full'      =>  json_encode($pie_chart_data, JSON_UNESCAPED_UNICODE)
        ]);
        // закончили с данными для круговой диаграммы

        // только нужно отдать не $json, а исправленные и модифицированные данные
        $t->assign('json', $json->getData()); // extra data
        /**
         * @TODO: ВАЖНО, В ШАБЛОНЕ ХОДИМ ТАК: {$json->economy->type}, А НЕ ЧЕРЕЗ ТОЧКУ!!!!
         * Если мы хотим ходить через точку - надо сначала сказать
         * $json->setIsAssociative();
         */

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
        $this->template->assign("json", json_decode($region_data['content_extra'] ?? '', true));

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
            "is_display_extra_content"
                                =>  $region_data["is_display_extra_content"]
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
            /*
            'edit_ipv4'         =>  $request->getData(null, default: \Arris\Helpers\Server::getIP(), casting: function ($ip) {
                return ip2long($ip);
            }),
            */
            'id_region'         =>  $request->getData('edit:id:region'),
            'title'             =>  $request->getData('edit:region:title'),
            'content'           =>  $request->getData('edit:region:content'),
            'content_restricted'=>  $request->getData('edit:region:content_restricted'),
            'edit_comment'      =>  $request->getData('edit:region:comment'),
            'is_excludelists'   =>  $request->getData('edit:is:excludelists'),
            'is_publicity'      =>  $request->getData('edit:is:publicity'),
            'is_display_extra_content'  => $request->getData('is_display_extra_content', default: 'no', casting: function ($data){
                // фишка в том, что с фронта установленный чекбокс приходит строкой `on` (по умолчанию, если не задан input val)
                // значение по-умолчанию задано 'no' (чекбокс снят, на бэк ничего не отправляется)
                // и проверяем, что передано. Либо 'on' (1), либо 'no' (0)
                return (strtolower($data) == 'on') ? 1 : 0;
            })
            /* Заменяет более короткий, но менее красивый код:
            * $data = array_key_exists('is_display_extra_content', $_REQUEST) && strtolower($_REQUEST['is_display_extra_content']) == 'on' ? 1 : 0
            */
        ];

        // Каждое кастомное поле нужно описать здесь и передать в будущую JSON-структуру

        // $request->setDefault('');

        $json = [
            // @todo: версия latest ОБРАТНО НЕСОВМЕСТИМЫХ изменений структуры, например переименования полей (ГГГГММДД)
            // она нужна для скриптов миграции данных из версии в версию.
            'version'   =>  '20240723',

            'lsi'       =>  [
                'index'     =>  self::json('lsi-index'),
                'type'      =>  self::json('lsi-type'),
                'atmosphere'=>  self::json('lsi-atmosphere'),
                'hydrosphere'   =>  self::json('lsi-hydrosphere'),
                'climate'   =>  self::json('lsi-climate')
            ],
            'history'   =>  [
                'year'  =>  [
                    'found'         =>  self::json('history-year-found'),
                    'colonization'  =>  self::json('history-year-colonization')
                ],
                'text'          =>  self::json('history-text'),
            ],
            'population'=>  [
                'count'     =>  Helpers::floatvalue(self::json('population-count')),
                'ethnic'    =>  self::json('population-ethnic'),
                'features'  =>  self::json('population-features'),
                'religion'  =>  self::json('population-religion')
            ],
            'economy'   =>  [
                'type'      =>  self::json('economy-type'),
                'shares'    =>  [
                    'natural'   =>  self::json('economy-shares-natural'),
                    'financial' =>  self::json('economy-shares-financial'),
                    'industrial'=>  self::json('economy-shares-industrial'),
                    'social'    =>  self::json('economy-shares-social')
                ],
                'assets'    =>  [
                    'natural'   =>  self::json('economy-assets-natural'),
                    'financial' =>  self::json('economy-assets-financial'),
                    'industrial'=>  self::json('economy-assets-industrial'),
                    'social'    =>  self::json('economy-assets-social'),
                    'oldmoney'  =>  self::json('economy-assets-oldmoney')
                ]
            ],
            'trade' =>  [
                'export'    =>  self::json('trade-export'),
                'import'    =>  self::json('trade-import'),
            ],
            'statehood' =>  [
                'ss'            =>  self::json('statehood-ss'),

                'type'          =>  self::json('statehood-type'),
                'dependency'    =>  self::json('statehood-dependency'),
                'radius'        =>  self::json('statehood-radius'),

                'administration_principle'  => self::json('statehood:administration_principle'),

                'local_governance'  =>  self::json('statehood-local_governance'),
                'terr_guards'   =>  self::json('statehood-terr_guards'),
                'agency'    =>  [
                    'css'       =>  self::json('statehood-agency-css'),
                    'drc'       =>  self::json('statehood-agency-drc'),
                    'psi'       =>  self::json('statehood-agency-psi'),
                    'starfleet' =>  self::json('statehood-agency-starfleet')
                ],
            ],
            'laws'  => [
                'passport'          =>  self::json('laws-passport'),
                'visa'              =>  self::json('laws-visa'),
                'gun_rights'        =>  self::json('laws-gun_rights'),
                'private_property'  =>  self::json('laws-private_property'),
                'gencard'   =>  [
                    'info'          =>  self::json('laws-gencard-info'),
                    'restrictions'  =>  self::json('laws-gencard-restrictions'),
                ],
            ],
            'culture'   =>  [
                'currency'      =>  self::json('culture-currency'),
                'holydays'      =>  self::json('culture-holydays'),
                'showplaces'    =>  self::json('culture-showplaces')
            ],
            // 'infrastructure'    =>  [], //
            'other'     =>  [
                'unverified'    =>  self::json('other-unverified_data'),
                'local_heroes'  =>  self::json('other-local_heroes'),
                'legacy'        => self::json('other-legacy'),
            ],
            'system_chart'  =>  self::json('system_chart'),
            'tags'          =>  self::json('tags'),

            // но можно и так:
            // хотя не нужно
            // 'tags'          =>  $request->getData('json:tags')
        ];

        // пакуем контент в JSON
        $data['content_extra'] = json_encode($json, self::JSON_ENCODE_FLAGS);

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
     * @param string $prefix
     * @return string
     */
    private static function json(string $field = '', string $prefix = 'json:'): string
    {
        if (empty($field)) {
            return '';
        }
        $rq_field = "{$prefix}{$field}";

        return  array_key_exists($rq_field, $_REQUEST)
                ? $_REQUEST[$rq_field]
                : '';
    }

}

# -eof- #