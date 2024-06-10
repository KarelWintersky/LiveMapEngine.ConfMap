<?php

namespace Confmap\Controllers;

use Confmap\AbstractClass;
use Confmap\Units\Map;
use Psr\Log\LoggerInterface;

class MapsController extends AbstractClass
{
    public Map $map;

    public function __construct($options = [], LoggerInterface $logger = null)
    {
        parent::__construct($options, $logger);
    }

    public function view_map_fullscreen()
    {
        $this->map = new Map($this->map_alias);
        $this->map->loadConfig();
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
        $this->template->assign("sections_present", [
            'infobox'   =>  true,
            'regions'   =>  true && ( $this->mapConfig->display->sections->regions ?? true ),
            'backward'  =>  true && ( $this->mapConfig->display->sections->backward ?? true ),
            'title'     =>  false,
            'colorbox'  =>  false,
        ]);

        $this->template->assign("section_backward_content", []);

        if ($this->map->mapViewMode === 'wide:infobox>regionbox' || $this->map->mapViewMode === 'infobox>regionbox') {
            $this->template->assign('section', [
                'infobox_control_position'      =>  'topleft',
                'regionbox_control_position'    =>  'topright',
                'regionbox_textalign'           =>  'right'
            ]);
        } else {
            $this->template->assign('section', [
                'infobox_control_position'      =>  'topright',
                'regionbox_control_position'    =>  'topleft',
                'regionbox_textalign'           =>  'left'
            ]);
        }
    }

}