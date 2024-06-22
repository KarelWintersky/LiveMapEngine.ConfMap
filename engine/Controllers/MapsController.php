<?php

namespace Confmap\Controllers;

use Arris\Path;
use Confmap\AbstractClass;
use Confmap\App;
use Confmap\Units\Map;
use LiveMapEngine\MapMaker;
use Psr\Log\LoggerInterface;

class MapsController extends AbstractClass
{
    public MapMaker $map;

    public function __construct($options = [], LoggerInterface $logger = null)
    {
        parent::__construct($options, $logger);
    }

    public function view_map_fullscreen()
    {
        $map_id = App::$id_map;

        $this->map = new MapMaker($this->pdo, $map_id, [
            'config_path'   =>  Path::create( config('path.storage') )->join($map_id),
            'json_parser'   =>  [Map::class, 'parseJSONFile']
        ]);

        if ($this->map->loadConfig()->is_error) {
            throw new \RuntimeException($this->map->loadConfig()->getMessage());
        }

        $this->map->loadMap();

        $this->template->setTemplate("_map.tpl");

        $this->template->assign('map_alias', $this->map_alias);

        if (!empty($this->mapConfig->display->custom_css)) {
            $this->template->assign('custom_css', "/storage/{$this->map_alias}/styles/{$this->mapConfig->display->custom_css}");
        }

        $this->template->assign('regions_with_content_ids', $this->map->mapRegionsWithInfo_IDS);

        $this->template->assign('map_regions_order_by_title', $this->map->mapRegionWithInfoOrderByTitle);
        $this->template->assign('map_regions_order_by_date', $this->map->mapRegionWithInfoOrderByDate);
        $this->template->assign('map_regions_count', count($this->map->mapRegionsWithInfo));

        // может быть перекрыто настройкой из конфига.
        //@todo: обновить в livemap с учетом нового модуля Map
        $this->template->assign("sections_present", [
            'infobox'   =>  true,
            'regions'   =>  true && ( $this->map->mapConfig->display->sections->regions ?? true ),
            'backward'  =>  true && ( $this->map->mapConfig->display->sections->backward ?? true ),
            'title'     =>  false,
            'colorbox'  =>  false,
        ]);

        // @todo: перенести в livemap
        $this->template->assign("sections_custom_regions_title", $this->map->getConfig("display_defaults->sections->regions->title") ?: 'Интересные места на карте');

        $this->template->assign("section_backward_content", [
            [ 'link' => '/about', 'text' => 'Что это?' ]
        ]);

        $this->template->assign('section', [
            'infobox_control_position'      =>  'topright',
            'regionbox_control_position'    =>  'topleft',
            'regionbox_textalign'           =>  'left'
        ]);
    }

}