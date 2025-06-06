<?php

namespace App\Controllers;

use App\AbstractClass;
use App\App;
use App\Units\Map;
use Arris\Entity\Result;
use Arris\Path;
use Arris\Presenter\Template;
use LiveMapEngine\Helpers;
use LiveMapEngine\Map\MapMaker;
use LiveMapEngine\Map\MapMakerInterface;
use LiveMapEngine\SVGParser;
use Psr\Log\LoggerInterface;
use RuntimeException;

#[\AllowDynamicProperties]
class JSController extends AbstractClass
{
    public MapMakerInterface $map;

    private Result $state;

    public function __construct($options = [], LoggerInterface $logger = null)
    {
        parent::__construct($options, $logger);

        $this->state = new Result();
    }

    /**
     * @return void
     *
     * @todo: перенести в framework, как и шаблон к нему
     */
    public function view_js_map_definition(): void
    {
        $map_id = App::$id_map;

        $this->map = new MapMaker($this->pdo, $map_id, [
            'path_storage'  =>  Path::create( config('path.storage') ),
            'path_config'   =>  Path::create( config('path.storage') )->join($map_id),
            'json_parser'   =>  [Map::class, 'parseJSONFile']
        ]);

        if ($this->map->loadConfig()->is_error) {
            throw new \RuntimeException($this->map->loadConfig()->getMessage());
        }

        $json = $this->map->mapConfig;

        $image_info = array(
            'width'     =>  0,
            'height'    =>  0,
            'ox'        =>  0,
            'oy'        =>  0
        );
        $max_bounds = null;

        $paths_data = [];
        $layers = [];



        try {
            if ($json->type === "vector" && empty($json->image)) {
                throw new RuntimeException( "[JS Builder] Declared vectorized image-layer, but image definition not found." );
            }

            $image_info = [];
            // это данные по сдвигу из конфига карты
            if (!empty($json->image)) {
                $image_info = [
                    'width'     =>  $json->image->width,
                    'height'    =>  $json->image->height,
                    'ox'        =>  $json->image->ox,
                    'oy'        =>  $json->image->oy
                ];
            }

            /* ============ SVG load ============= */
            if (empty($json->layout->file)) {
                throw new RuntimeException( "[JS Builder] Layout file not defined." );
            }

            // генерируем имя файла разметки
            $svg_filename
                = Path::create(getenv('PATH.STORAGE'))
                ->join($map_id)
                ->joinName($json->layout->file)
                ->toString();

            if (!is_file($svg_filename)) {
                throw new RuntimeException( "[JS Builder] Layout file {$svg_filename} not found." );
            }
            $svg_content = \file_get_contents( $svg_filename );

            if ($svg_content === '') {
                throw new RuntimeException( "[JS Builder] Layout file is empty" );
            }

            /* =============== Layout ============ */

            // информация о слоях
            if (empty($json->layout)) {
                throw new RuntimeException( "[JS Builder] Layout data not found." );
            }

            $_svgParserClass = new SVGParser( $svg_content );

            if ($_svgParserClass->parser_state->is_error) {
                throw new RuntimeException( "[JS Builder] SVG Parsing error " . $_svgParserClass->parser_state->getMessage() );
            }

            // image layer from file
            // надо проверить наличие слоёв в массиве определений
            //@todo: предполагается, что слой с изображением ВСЕГДА будет "Image" - это может быть не так?
            $layer_name = "Image";
            $_svgParserClass->parseImages( $layer_name );

            if ($json->type === "bitmap" && $_svgParserClass->getImagesCount()) {
                $image_info = $_svgParserClass->getImageInfo();

                // использовать параметры из файла карты НЕЛЬЗЯ, потому что размеры слоя разметки привязаны к размеру карты в файле
                // если мы изменим размеры (maxBounds) до размеров оригинальной картинки - все сломается :(
                // $image_info['width'] = $json->image->width;
                // $image_info['height'] = $json->image->height;

                $_svgParserClass->set_CRSSimple_TranslateOptions( $image_info['ox'], $image_info['oy'], $image_info['height'] );
            } else {
                $_svgParserClass->set_CRSSimple_TranslateOptions( 0, 0, $image_info['height'] );
            }

            /*if (!empty($json->layout->layers)) {
                $layers_list = $json->layout->layers;
            } else {
                $layers_list[] = "Paths";
            }*/

            foreach($json->layout->layers as $layer) {
                // грузим конфиг по умолчанию из $json
                $layer_config = null;

                /**
                 * Определение слоя. Скорее всего оно никогда не будет null.
                 * Только если не указать в массиве !->layout->layers имя слоя, которого нет в структуре !->layers
                 *
                 * @var \stdClass $layer_config
                 */
                if (!empty($json->layers->{$layer})) {
                    $layer_config = $json->layers->{$layer};
                }

                $_svgParserClass->parseLayer($layer);   // парсит слой (определяет атрибут трансформации слоя и конвертит в объекты все элементы)

                // установим конфигурационные значения для пустых регионов для текущего слоя
                $_svgParserClass->setLayerDefaultOptions($layer_config);

                // получаем все элементы на слое
                $paths_at_layer = $_svgParserClass->getElementsAll();

                // dd($paths_at_layer);

                // теперь нам нужны айдишники этих элементов на слое. Их надо проверить в БД и заполнить значениями кастомных полей из БД
                /*$paths_at_layers_ids = implode(", ", array_map( static function($item){
                    return "'{$item}'";
                }, array_keys($paths_at_layer)));*/

                // вытаскиваем из БД контент регионов и заполненность полей кастомной разметки регионов. По умолчанию там будет NULL если разметка "по умолчанию"!
                $paths_at_layer_filled = $this->map->getRegionsWithInfo($paths_at_layer);

                // фильтруем по доступности пользователю (is_publicity)
                $paths_at_layer_filled = Helpers::checkRegionsVisibleByCurrentUser($paths_at_layer_filled, $this->map_alias);

                foreach ($paths_at_layer_filled as $path_present) {
                    $id_region = $path_present['id_region'];

                    // если конфиг слоя определен (практически всегда)
                    if ($layer_config) {
                        // если определены параметры заполнения региона
                        if ($layer_config->display_defaults->present->fill && $layer_config->display_defaults->present->fill == 1) {

                            if (!array_key_exists('fillColor', $path_present) && $layer_config->display_defaults->present->fillColor) {
                                $path_present['fillColor'] = $layer_config->display_defaults->present->fillColor;
                            }

                            if (!array_key_exists('fillOpacity', $path_present) && $layer_config->display_defaults->present->fillOpacity) {
                                $path_present['fillOpacity'] = $layer_config->display_defaults->present->fillOpacity;
                            }

                            // Кажется, это некорректная проверка. Поля будут всегда, но они могут содержать NULL. Поэтому нам надо проверить еще и значение поля на NULL
                            // Хотя нет, если они NULL - то в MapManager.js параметрам региона будет сопоставлен параметр слоя в buildRegionsDataset(), строка 338.
                            // То есть можно оставить старую проверку, там будет значение NULL которое трактуют правильно (надеюсь) - 2024-09-30
                            // с другой стороны, сейчас MapManager.js работает некорректно. Он не учитывает значения по-умолчанию для слоя. Только для региона и глобальные
                            // display_defaults

                            /*if (
                                $layer_config->display_defaults->present->fillColor &&
                                ( !array_key_exists('fillColor', $path_present) || is_null($path_present['fillColor']))
                            ) {
                                $path_present['fillColor'] = $layer_config->display_defaults->present->fillColor;
                            }

                            if (
                                $layer_config->display_defaults->present->fillOpacity &&
                                ( !array_key_exists('fillOpacity', $path_present) || is_null($path_present['fillOpacity']))
                            ) {
                                $path_present['fillOpacity'] = $layer_config->display_defaults->present->fillOpacity;
                            }*/

                        }

                        // если определены параметры кастомной отрисовки границ региона
                        if ($layer_config->display_defaults->present->stroke && $layer_config->display_defaults->present->stroke == 1) {

                            // аналогично, см. выше

                            /*if (
                                $layer_config->display_defaults->present->borderColor &&
                                ( !array_key_exists('borderColor', $path_present) || is_null($path_present['borderColor']))
                            ) {
                                $path_present['borderColor'] = $layer_config->display_defaults->present->borderColor;
                            }

                            if (

                                $layer_config->display_defaults->present->borderWidth &&
                                ( !array_key_exists('borderWidth', $path_present) || is_null($path_present['borderWidth']))
                            ) {
                                $path_present['borderWidth'] = $layer_config->display_defaults->present->borderWidth;
                            }

                            if (
                                $layer_config->display_defaults->present->borderOpacity &&
                                ( !array_key_exists('borderOpacity', $path_present) || is_null($path_present['borderOpacity']))
                            ) {
                                $path_present['borderOpacity'] = $layer_config->display_defaults->present->borderOpacity;
                            }*/

                            if (!array_key_exists('borderColor', $path_present) && $layer_config->display_defaults->present->borderColor) {
                                $path_present['borderColor'] = $layer_config->display_defaults->present->borderColor;
                            }

                            if (!array_key_exists('borderWidth', $path_present) && $layer_config->display_defaults->present->borderWidth) {
                                $path_present['borderWidth'] = $layer_config->display_defaults->present->borderWidth;
                            }

                            if (!array_key_exists('borderOpacity', $path_present) && $layer_config->display_defaults->present->borderOpacity) {
                                $path_present['borderOpacity'] = $layer_config->display_defaults->present->borderOpacity;
                            }
                        }

                    } else {
                        // иначе, конфиг слоя не определен, используются глобальные дефолтные значения

                        if (!array_key_exists('fillColor', $path_present)) {
                            $path_present['fillColor'] = $json->display_defaults->present->fillColor ?: "#00ff00";
                        }

                        if (!array_key_exists('fillOpacity', $path_present)) {
                            $path_present['fillOpacity'] = $json->display_defaults->present->fillOpacity ?: 0.1;
                        }

                        if (!array_key_exists('borderColor', $path_present)) {
                            $path_present['borderColor'] = $json->display_defaults->present->borderColor ?: "#000000";
                        }

                        if (!array_key_exists('borderWidth', $path_present)) {
                            $path_present['borderWidth'] = $json->display_defaults->present->borderWidth ?: 0;
                        }

                        if (!array_key_exists('borderOpacity', $path_present)) {
                            $path_present['borderOpacity'] = $json->display_defaults->present->borderOpacity ?: 0;
                        }
                    }

                    $path_present['title'] = htmlspecialchars($path_present['title'], ENT_QUOTES | ENT_HTML5);
                    unset($path_present['edit_date']);

                    if (array_key_exists($id_region, $paths_at_layer)) {

                        $paths_at_layer[ $id_region ] = array_merge(
                            $paths_at_layer[ $id_region ],
                            $path_present
                        );

                    }
                }

                $layers[] = [
                    'id'        =>  $layer,
                    'hint'      =>  \htmlspecialchars($layer_config->hint, ENT_QUOTES | ENT_HTML5),
                    'zoom'      =>  $layer_config->zoom ?? $json->display->zoom,
                    'zoom_min'  =>  $layer_config->zoom_min ?? -100,
                    'zoom_max'  =>  $layer_config->zoom_max ?? 100,

                    'display_defaults'  =>  $layer_config->display_defaults
                    // 'regions'   =>  $paths_at_layer
                ];

                $paths_data += $paths_at_layer;
            }

        } catch (\RuntimeException $e) {
            $this->template->assign('/JSBuilderError', $this->state->getMessage());
        }

        // maxbounds {
        $max_bounds = [
            'present'   =>  0
        ];
        if (property_exists_recursive($json, 'display->maxbounds')) {
            $max_bounds = [
                'present'   =>  1,
                'topleft_h'     =>  $json->display->maxbounds[0][0] ?? -1,
                'topleft_w'     =>  $json->display->maxbounds[0][1] ?? -1,
                'bottomright_h' =>  $json->display->maxbounds[1][0] ?? 1,
                'bottomright_w' =>  $json->display->maxbounds[1][1] ?? 1
            ];
        }
        $this->template->assign('maxbounds', $max_bounds);
        // } maxbounds

        // Основная информация о карте
        $this->template->assign("map", [
            'type'          =>  $json->type,
            'title'         =>  $json->title,
            'description'   =>  $json->description ?? $json->title ?? '',
            'alias'         =>  $map_id,
            'imagefile'     =>  $json->image->file,
            'width'         =>  $image_info['width'],
            'height'        =>  $image_info['height'],
            'ox'            =>  $image_info['ox'],
            'oy'            =>  $image_info['oy'],
        ]);

        $_assign_display = [
            'zoom'                      =>  $json->display->zoom,
            'zoom_max'                  =>  $json->display->zoom_max,
            'zoom_min'                  =>  $json->display->zoom_min,
            'zoom_mode'                 =>  $json->display->zoom_mode ?? 'slider',
            'background_color'          =>  $json->display->background_color,
            'custom_css'                =>  $json->display->custom_css ?? [], // файлы кастомных стилей для карты
            'focus_animate_duration'    =>  $json->display->focus_animate_duration ?? 0.7, // НЕ ИСПОЛЬЗУЕТСЯ НИГДЕ!
            'focus_highlight_color'     =>  $json->display->focus_highlight_color ?? '#ff0000',
            'focus_timeout'             =>  $json->display->focus_timeout ?? 1000,
            'viewmode'                  =>  $json->display->viewmode ?? 'colorbox',
            // 'cursor'                    =>  $json->display->cursor ?? 'pointer'
        ];

        switch ($_assign_display['viewmode']) {
            case 'colorbox': {
                $_assign_display['viewoptions'] = [
                    'width'     =>  $json->display->viewoptions->width ?? 800,
                    'height'    =>  $json->display->viewoptions->height ?? 600,
                ];
                break;
            }
            case 'infobox': {
                $_assign_display['viewoptions'] = $json->display->viewoptions->order ?? 'infobox>regionbox';
                break;
            }
            case 'hintbox': {
                $_assign_display['viewoptions'] = $json->display->viewoptions->position ?? 'topright';
                break;
            }
            default: {
                // folio
            }
        }

        $this->template->assign("display", $_assign_display);

        /* Механизм сбора данных для расцветки регионов по-умолчанию */
        $display_defaults_region =[];
        $display_defaults_region['empty'] = [
            "stroke"        =>  $json->display_defaults->region->{'empty'}->{'stroke'} ?? 0,
            "borderColor"   =>  $json->display_defaults->region->{'empty'}->{'borderColor'} ?? "#ff0000",
            "borderWidth"   =>  $json->display_defaults->region->{'empty'}->{'borderWidth'} ?? 0,
            "borderOpacity" =>  $json->display_defaults->region->{'empty'}->{'borderOpacity'} ?? 0,
            "fill"          =>  $json->display_defaults->region->{'empty'}->{'fill'} ?? 0,
            "fillColor"     =>  $json->display_defaults->region->{'empty'}->{'fillColor'} ?? "#ffffff",
            "fillOpacity"   =>  $json->display_defaults->region->{'empty'}->{'fillOpacity'} ?? 0,
        ];
        $display_defaults_region['empty_hover'] = [
            "stroke"        =>  $json->display_defaults->region->{'empty:hover'}->{'stroke'}          ?? $display_defaults_region['empty']['stroke'],
            "borderColor"   =>  $json->display_defaults->region->{'empty:hover'}->{'borderColor'}     ?? $display_defaults_region['empty']['borderColor'],
            "borderWidth"   =>  $json->display_defaults->region->{'empty:hover'}->{'borderWidth'}     ?? $display_defaults_region['empty']['borderWidth'],
            "borderOpacity" =>  $json->display_defaults->region->{'empty:hover'}->{'borderOpacity'}   ?? $display_defaults_region['empty']['borderOpacity'],
            "fill"          =>  $json->display_defaults->region->{'empty:hover'}->{'fill'}            ?? $display_defaults_region['empty']['fill'],
            "fillColor"     =>  $json->display_defaults->region->{'empty:hover'}->{'fillColor'}       ?? $display_defaults_region['empty']['fillColor'],
            "fillOpacity"   =>  $json->display_defaults->region->{'empty:hover'}->{'fillOpacity'}     ?? $display_defaults_region['empty']['fillOpacity'],
        ];
        $display_defaults_region['present'] = [
            "stroke"        =>  $json->display_defaults->region->{'present'}->{'stroke'}          ?? $display_defaults_region['empty']['stroke'],
            "borderColor"   =>  $json->display_defaults->region->{'present'}->{'borderColor'}     ?? $display_defaults_region['empty']['borderColor'],
            "borderWidth"   =>  $json->display_defaults->region->{'present'}->{'borderWidth'}     ?? $display_defaults_region['empty']['borderWidth'],
            "borderOpacity" =>  $json->display_defaults->region->{'present'}->{'borderOpacity'}   ?? $display_defaults_region['empty']['borderOpacity'],
            "fill"          =>  $json->display_defaults->region->{'present'}->{'fill'}            ?? $display_defaults_region['empty']['fill'],
            "fillColor"     =>  $json->display_defaults->region->{'present'}->{'fillColor'}       ?? $display_defaults_region['empty']['fillColor'],
            "fillOpacity"   =>  $json->display_defaults->region->{'present'}->{'fillOpacity'}     ?? $display_defaults_region['empty']['fillOpacity'],
        ];
        $display_defaults_region['present_hover'] = [
            "stroke"        =>  $json->display_defaults->region->{'present:hover'}->{'stroke'}          ?? $display_defaults_region['present']['stroke'],
            "borderColor"   =>  $json->display_defaults->region->{'present:hover'}->{'borderColor'}     ?? $display_defaults_region['present']['borderColor'],
            "borderWidth"   =>  $json->display_defaults->region->{'present:hover'}->{'borderWidth'}     ?? $display_defaults_region['present']['borderWidth'],
            "borderOpacity" =>  $json->display_defaults->region->{'present:hover'}->{'borderOpacity'}   ?? $display_defaults_region['present']['borderOpacity'],
            "fill"          =>  $json->display_defaults->region->{'present:hover'}->{'fill'}            ?? $display_defaults_region['present']['fill'],
            "fillColor"     =>  $json->display_defaults->region->{'present:hover'}->{'fillColor'}       ?? $display_defaults_region['present']['fillColor'],
            "fillOpacity"   =>  $json->display_defaults->region->{'present:hover'}->{'fillOpacity'}     ?? $display_defaults_region['present']['fillOpacity'],
        ];

        $display_defaults_poi = [];
        $display_defaults_poi['any'] = [
            // 'iconClass'     =>  Helpers::property_get_recursive($json, "display_defaults->poi->any->iconClass", '->', 'fa-fort-awesome'),

            'iconClass'     =>  $json->display_defaults->poi->{'any'}->{'iconClass'}        ?? 'fa-fort-awesome',
            'markerColor'   =>  $json->display_defaults->poi->{'any'}->{'markerColor'}      ?? 'black',
            'iconColor'     =>  $json->display_defaults->poi->{'any'}->{'iconColor'}        ?? 'white',
            'iconXOffset'   =>  $json->display_defaults->poi->{'any'}->{'iconXOffset'}      ?? -1,
            'iconYOffset'   =>  $json->display_defaults->poi->{'any'}->{'iconYOffset'}      ?? 0,
        ];
        /*
        // не используются, так как есть проблемы с отловом события mouseover/mouseout над POI
        // см /public/frontend/view.map.fullscreen.js:82
        $display_defaults_poi['empty'] = [
             'iconClass'     =>  $json->display_defaults->poi->{'empty'}->{'iconClass'}          ?? 'fa-fort-awesome',
             'markerColor'     =>  $json->display_defaults->poi->{'empty'}->{'markerColor'}      ?? 'black',
             'iconColor'     =>  $json->display_defaults->poi->{'empty'}->{'iconColor'}          ?? 'white',
             'iconXOffset'     =>  $json->display_defaults->poi->{'empty'}->{'iconXOffset'}      ?? -1,
             'iconYOffset'     =>  $json->display_defaults->poi->{'empty'}->{'iconYOffset'}      ?? 0,
         ];
         $display_defaults_poi['empty_hover'] = [
             'iconClass'     =>  $json->display_defaults->poi->{'empty:hover'}->{'iconClass'}    ?? $display_defaults_poi['empty']['iconClass'],
             'markerColor'   =>  $json->display_defaults->poi->{'empty:hover'}->{'markerColor'}  ?? $display_defaults_poi['empty']['markerColor'],
             'iconColor'     =>  $json->display_defaults->poi->{'empty:hover'}->{'iconColor'}    ?? $display_defaults_poi['empty']['iconColor'],
             'iconXOffset'   =>  $json->display_defaults->poi->{'empty:hover'}->{'iconXOffset'}  ?? $display_defaults_poi['empty']['iconXOffset'],
             'iconYOffset'   =>  $json->display_defaults->poi->{'empty:hover'}->{'iconYOffset'}  ?? $display_defaults_poi['empty']['iconYOffset'],
         ];
         $display_defaults_poi['present'] = [
             'iconClass'     =>  $json->display_defaults->poi->{'present'}->{'iconClass'}        ?? $display_defaults_poi['empty']['iconClass'],
             'markerColor'   =>  $json->display_defaults->poi->{'present'}->{'markerColor'}      ?? $display_defaults_poi['empty']['markerColor'],
             'iconColor'     =>  $json->display_defaults->poi->{'present'}->{'iconColor'}        ?? $display_defaults_poi['empty']['iconColor'],
             'iconXOffset'   =>  $json->display_defaults->poi->{'present'}->{'iconXOffset'}      ?? $display_defaults_poi['empty']['iconXOffset'],
             'iconYOffset'   =>  $json->display_defaults->poi->{'present'}->{'iconYOffset'}      ?? $display_defaults_poi['empty']['iconYOffset'],
         ];
         $display_defaults_poi['present_hover'] = [
             'iconClass'     =>  $json->display_defaults->poi->{'present:hover'}->{'iconClass'}    ?? $display_defaults_poi['present']['iconClass'],
             'markerColor'   =>  $json->display_defaults->poi->{'present:hover'}->{'markerColor'}  ?? $display_defaults_poi['present']['markerColor'],
             'iconColor'     =>  $json->display_defaults->poi->{'present:hover'}->{'iconColor'}    ?? $display_defaults_poi['present']['iconColor'],
             'iconXOffset'   =>  $json->display_defaults->poi->{'present:hover'}->{'iconXOffset'}  ?? $display_defaults_poi['present']['iconXOffset'],
             'iconYOffset'   =>  $json->display_defaults->poi->{'present:hover'}->{'iconYOffset'}  ?? $display_defaults_poi['present']['iconYOffset'],
         ];*/

        $display_defaults = [
            "region"    =>  $display_defaults_region,
            "poi"       =>  $display_defaults_poi
        ];

        // параметры для секции theMap.display.region и theMap.display.poi
        $this->template->assign("display_defaults", $display_defaults);


        $this->template->assign('layers', $layers);
        $this->template->assign('regions', $paths_data);

        /*
         * В рамках фреймворка нужно рендерить шаблон из файла во фреймворке и отдавать CONTENT_TYPE_RAW с заголовком JS
         * Либо рендерить не из файла, а из HEREDOC
         * */

        $this->template->setTemplate("_js/theMapDefinition.tpl");
        $this->template->setRenderType(Template::CONTENT_TYPE_JS);
    }

    /**
     * Тестовый метод. Планирую функционал переноса генерации JS-файла во фреймворк
     *
     * @throws \SmartyException
     */
    public function test()
    {
        // нам нужно отдать во фреймворк инстанс шаблонизатора, а получить - срендеренный текст.
        // инстанс класса Template может не поддерживать chaining

        $t = new Template();
        $t
            ->setTemplateDir( '' /* путь к файлам фреймворка */)
            ->setCompileDir( '/dev/shm/')
            ->setForceCompile( true)
            ->registerPlugin( Template::PLUGIN_MODIFIER, 'json_encode', 'json_encode', false)
            ->setTemplate( '', /* путь к файлу шаблона */);
        $t->assign("data", []);

        $t->setRenderType(Template::CONTENT_TYPE_JS);
    }

}

# -eof- #