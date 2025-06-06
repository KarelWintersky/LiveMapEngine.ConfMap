<?php

namespace App\Controllers;

use App\AbstractClass;
use App\App;
use App\OpenGraph;
use App\Units\Map;
use Arris\Path;
use LiveMapEngine\Map\MapMaker;
use Psr\Log\LoggerInterface;

class MapsController extends AbstractClass
{
    public MapMaker $map;

    /**
     * @param $options
     * @param LoggerInterface|null $logger
     */
    public function __construct($options = [], LoggerInterface $logger = null)
    {
        parent::__construct($options, $logger);
    }

    /**
     * @return void
     */
    public function view_map_fullscreen(): void
    {
        $map_id = App::$id_map;

        $this->map = new MapMaker($this->pdo, $map_id, [
            'path_storage'  =>  Path::create( config('path.storage') ),
            'path_config'   =>  Path::create( config('path.storage') )->join($map_id),
            'json_parser'   =>  [Map::class, 'parseJSONFile']
        ]);
        // добавить метод addParser() ?

        // Вообще, загружаемый конфиг можно было бы кэшировать, храня дату обновления файла и данные.
        // Проверять дату файла, если она не соответствует закешированной - загружать с диска.
        // НО: это привязка к редису, нужно предусмотреть fallback + операция загрузки карты нетяжелая

        if ($this->map->loadConfig()->is_error) {
            throw new \RuntimeException($this->map->loadConfig()->getMessage());
        }

        $this->map->loadMap(); // А вот тут можно было бы что-то кэшировать. Что? mapRegionsWithInfo как исходный массив данных до фильтров по пользователям?

        $this->template->assign('map_alias', $this->map_alias);

        // custom CSS, всегда массив, возможно, пустой {
        $custom_css_paths = [];
        foreach ($this->map->getConfig("display->custom_css", []) as $file) {
            $fn
                = (new Path($this->map->path_storage))
                ->join($this->map_alias)
                ->join('styles')
                ->join($file)
                ->toString();

            /*
            // начиная с Arris 2.12.0 можно не применять к $fn toString(), а сказать:
            if ($fn->isFile()) {
                $custom_css_paths[] = "/storage/{$this->map_alias}/styles/" . $fn->toString();
            }
            */

            if (is_file($fn)) {
                $custom_css_paths[] = "/storage/{$this->map_alias}/styles/{$file}";
            }
        }
        $this->template->assign('custom_css_paths', $custom_css_paths); //@todo: cacheAble
        // } custom_css

        $this->template->assign('regions_with_content_ids', $this->map->mapRegionsWithInfo_IDS); //@todo: cacheAble?

        $this->template->assign('map_regions_order_by_title', $this->map->mapRegionWithInfoOrderByTitle); //@todo: cacheAble?
        $this->template->assign('map_regions_order_by_date', $this->map->mapRegionWithInfoOrderByDate); //@todo: cacheAble?
        $this->template->assign('map_regions_count', count($this->map->mapRegionsWithInfo)); //@todo: cacheAble?

        // может быть перекрыто настройкой из конфига.
        //@todo: обновить в livemap с учетом нового модуля Map
        //@todo: Параметры этих контейнеров нужно передавать в конструктор MapControls - ведь он должен отвечать за взаиморасположение и поведение контролов на карте

        // Эти параметры зависят от viewmode карты И индивидуальных настроек секций. Индивидуальные настройки секций перекрывают viewmode
        $this->template->assign("sections_present", [
            'infobox'   =>  $this->map->getConfig('display->viewmode', 'folio') === 'infobox',  // либо колорбокс, либо инфобокс
            'colorbox'  =>  $this->map->getConfig('display->viewmode', 'folio') === 'colorbox', // на самом деле конечно нужно определять секции совокупно
            'hint'      =>  $this->map->getConfig('display->viewmode', 'folio') === 'hintbox',  // режим показа контента

            // на самом деле следующие два поля надо определять на основании трех предыдущих... или нет?

            'regions'   =>  true && ( $this->map->getConfig('display->sections->regions', true) ),
            'backward'  =>  true && ( $this->map->getConfig('display->sections->backward', true)),
        ]);

        //@todo: !
        $this->template->assign("section_position", [

        ]);

        $this->template->assign("sections_custom_regions_title",
            $this->map->getConfig("display->sections->regions->title") ?: 'Интересные места на карте'
        );

        $this->template->assign("section_backward_content", [
            [ 'link' => '/about', 'text' => 'Что это?' ]
        ]);

        //@todo: это можно брать из описания секций в конфиге + дефолтные значения
        $this->template->assign('section', [
            'infobox_control_position'      =>  'topright',
            'regionbox_control_position'    =>  'topleft',
            'regionbox_textalign'           =>  'left'
        ]);

        $this->template->assign("og", OpenGraph::makeForMap());
        $this->template->assign("html_title", OpenGraph::$og_default['title']);

        $this->template->assign("custom_js", [
            'fontawesome'   =>  false
        ]);

        $this->template->setTemplate("_map.tpl");
    }

}

# -eof- #