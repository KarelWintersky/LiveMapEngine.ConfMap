/**
 * Попытка запихнуть все методы работы с картой в класс
 */
class MapManager {
    static VERSION = '2024-07-23';

    /**
     * Инстанс инфобокса, null - когда не создан
     */
    static __InfoBox = null;

    /**
     * Текущий регион, для которого открыт инфобокс
     * @type {null}
     */
    static current_infobox_region_id = null;

    /**
     * ID карты
     *
     * @type {null}
     */
    map_alias = null;

    /* ====================================================================== */
    /**
     * Исходное определение карты
     *
     * @type {{}}
     */
    theMap = {};

    /**
     * Список регионов, имеющих контент (то есть выделенных)
     *
     * @type {{}}
     */
    present_regions = [];

    /**
     * Глобальный объект карты
     *
     * @type {{}}
     */
    map = {};

    /**
     * Объект глобальной декларации регионов на карте. Включает параметры по каждому региону и созданные на их основе Leaflet Vector Layers
     * @type {{}}
     */
    regionsDataset = {};

    /**
     * Layers Group Set для карты
     *
     * @type {{}}
     */
    LGS = {};

    /**
     * Базовые границы карты
     *
     * @type {{}}
     */
    baseMapBounds = {};

    /**
     * Режим показа окна с информацией о регионе: infobox|colorbox
     *
     * @type {string}
     */
    infobox_mode = '';

    /**
     *
     * @param mapDefinition - определение карты, полученное из JS-запроса `/map:js/ID.js`
     * @param present_regions - массив регионов, имеющих контент
     * @param options
     * @param is_debug
     */
    constructor(mapDefinition = {}, present_regions = [], options = {}, is_debug = false)
    {
        this.options = {
            use_canvas: true,           // используется ли рендер Canvas?
            checkWLH_onStart: true,       // анализировать ли при загрузке страницы window.location.hash и показывать ли инфобокс при необходимости?
        }
        this.options = Object.assign(this.options, options);

        this.present_regions = present_regions;

        this.theMap = mapDefinition;
        this.IS_DEBUG = is_debug;
        this.map_alias = this.theMap.map.id;

        this.options = Object.assign(this.options, {
            focus: {
                animate_duration: this.theMap.display.focus.animate_duration || 0.7, // не используется нигде, какое-то легаси!
                highlight_color: this.theMap.display.focus.highlight_color || '#ff0000',
                timeout: this.theMap.display.focus.timeout || 1500,
            }
        });

        this.infobox_mode
            = this.theMap.hasOwnProperty('display') && this.theMap.display.hasOwnProperty('viewmode') && this.theMap.display['viewmode'] !== ''
            ? this.theMap.display['viewmode'] : 'folio';

        /*this.infobox_mode = JSHelpers.has(this.theMap, 'display.viewmode') ? this.theMap.display['viewmode'] : 'folio';*/

        this.LGS = {};
        this.regionsDataset = {};
        this.baseMapBounds = {};
    }

    /**
     * Устанавливает фон для контейнера карты
     *
     * @param target
     */
    setBackgroundColor(target) {

        $(target).css('background-color', this.theMap['display']['background_color']);
    }

    /**
     * Создает карту и зум на ней, в зависимости от параметров
     *
     * @param target
     * @returns map
     */
    createMap(target) {
        let map = null;

        let use_zoom_slider;
        let use_zoom_slider_position = this.theMap['display']['zoom_slider_position'] || 'bottomright';

        let _options = {
            crs: L.CRS.Simple,
            minZoom: this.theMap['display']['zoom_min'],
            maxZoom: this.theMap['display']['zoom_max'],
        };

        if (this.options.use_canvas) {
            _options.preferCanvas = true;
            _options.renderer = L.canvas();
        } else {
            _options.preferCanvas = false;
            _options.renderer = L.svg({ padding: Number(this.theMap['display']['zoom_max']) + 1 }); // должно быть, походу, maxzoom+1
        }

        switch (this.theMap['display']['zoom_mode']) {
            case 'native': {
                use_zoom_slider = false;
                _options.zoomControl = true;
                break;
            }
            case 'smooth': {
                use_zoom_slider = false;
                _options.scrollWheelZoom = false;   // disable original zoom function
                _options.smoothWheelZoom = true;    // enable smooth zoom
                _options.smoothSensitivity = 1;     // zoom speed. default is 1

                _options.zoomControl = true;
                break;
            }
            default: {
                use_zoom_slider = true;
                _options.zoomControl = false;
            }
        }

        // switch не используем, потому что от типа карты сейчас ничего не зависит, а карта типа tileset не поддерживается
        // и legacy image pyramids - который "разные файлы подложки в зависимости от разного зума - тоже пока не используется (@todo)
        /*
        switch (this.theMap.map.type) {
            case 'bitmap': {
                map = L.map(target, _options);

                if (use_zoom_slider) {
                    map.addControl(new L.Control.Zoomslider({position: use_zoom_slider_position}));
                } else {
                    map.zoomControl.setPosition(use_zoom_slider_position);
                }

                map.attributionControl.setPrefix(this.theMap.map.attribution || '');

                break;
            }
            case 'vector': {
                map = L.map(target, _options);

                if (use_zoom_slider) {
                    map.addControl(new L.Control.Zoomslider({position: use_zoom_slider_position}));
                } else {
                    map.zoomControl.setPosition(use_zoom_slider_position);
                }

                map.attributionControl.setPrefix(this.theMap.map.attribution || '');

                break;
            }
            case 'tileset': {
                break;
            }
        }// switch
        */
        map = L.map(target, _options);

        if (use_zoom_slider) {
            map.addControl(new L.Control.Zoomslider({position: use_zoom_slider_position}));
        } else {
            map.zoomControl.setPosition(use_zoom_slider_position);
        }

        map.attributionControl.setPrefix(this.theMap.map.attribution || '');

        // (L.control.scale()).addTo(map);

        this.map = map;

        return map;
    } // -createMap

    /**
     * Отдает интерактивный элемент карты из массива regionsDataset по ID
     *
     * @param id_region
     * @returns {*|Object}
     */
    getMapElement(id_region) {
        return this.regionsDataset[id_region];
    }

    /**
     * Возвращает bounds карты. И вроде бы не используется для именно setBounds()
     * Кроме возврата, еще и сохраняет значения в this.baseMapBounds
     *
     * @returns {(number[]|*[])[]}
     */
    getBounds()
    {
        let bounds = [
            [0, 0],
            [this.theMap['map']['height'], this.theMap['map']['width']]
        ];

        /*if (theMap['display']['maxbounds']) {
        let mb = theMap['display']['maxbounds'];

        bounds = [
            [
                // mb['topleft_h'] * theMap['map']['height'],
                // mb['topleft_w'] * theMap['map']['width']
                0, 0
            ],
            [
                mb['bottomright_h'] * theMap['map']['height'],
                mb['bottomright_w'] * theMap['map']['width']
            ]
        ];
        }*/
        this.baseMapBounds = bounds;
        return bounds;
    }

    /**
     * На деле, похоже, параметр передавать не нужно, его можно взять из _mapManager.baseMapBounds
     *
     * Здесь же нам нужно будет определять параметры разных файлов для разных уровней зума. Но это не точно!
     *
     * @param base_map_bounds
     * @returns {null}
     */
    createImageOverlay(base_map_bounds) {
        let image = null;

        switch (this.theMap.map.type) {
            case 'bitmap': {
                image = L.imageOverlay( this.theMap['map']['imagefile'], base_map_bounds);
                break;
            }
            case 'vector': {
                image = L.imageOverlay( this.theMap['map']['imagefile'], base_map_bounds);
                break;
            }
            case 'tileset': {
                //@todo: почему ESO-то?
                // storage/ID/tiles/z/x_y.jpg - наверное так должно быть?
                L.tileLayer('eso/{z}/{x}/{y}.jpg', {
                    minZoom: this.theMap['display']['zoom_min'],
                    maxZoom: this.theMap['display']['zoom_max'],
                    attribution: 'ESO/INAF-VST/OmegaCAM',
                    tms: true
                });

                break;
            }
        }

        return image;
    }

    /**
     * Строит датасет регионов на карте с информацией о стилях отображения
     * Бывший buildPolymap()
     * @todo: Реализовать в folio и colorbox режимах
     *
     * @returns {null}
     */
    buildRegionsDataset() {
        let theMapRegions = this.theMap['regions'];
        let defaultDisplaySettings = this.theMap.display_defaults;

        let dataset = Object.create({ }); // если создавать с null - то массив будет короче, но не будет содержать метода hasOwnProperty, который полезен.

        Object.keys( theMapRegions ).forEach(function( key ) {
            let region = theMapRegions[key];

            let type = region['type'];
            let coords = region['coords'];
            let layer = region['layer']; // к какому слою относится регион?
            let is_have_content = region.hasOwnProperty('title');
            let dd_key = is_have_content ? 'present' : 'empty';
            let dd_key_hover = is_have_content ? 'present_hover' : 'empty_hover';

            let options = {
                id: region.id,
                title: region.title || region.id,
                coords: coords,
                layer: layer,
                radius: region['radius'] || 10,

                // present или empty - нужно брать из данных о регионе (пока что берётся present для всех регионов).
                /* Параметры по-умолчанию для создания региона. В дальнейшем (on('mouseout'), on('mouseover') будем брать из структуры region */
                /* Это изменяемые параметры для региона. Они будут использованы для его создания */
                stroke: region['stroke'] || defaultDisplaySettings.region[dd_key].stroke,
                color: region['borderColor'] || defaultDisplaySettings.region[dd_key].borderColor,
                width: region['borderWidth'] || defaultDisplaySettings.region[dd_key].borderWidth,
                opacity: region['borderOpacity'] || defaultDisplaySettings.region[dd_key].borderOpacity,
                fill: region['fill'] || defaultDisplaySettings.region[dd_key].fill,
                fillColor: region['fillColor'] || defaultDisplaySettings.region[dd_key].fillColor,
                fillOpacity: region['fillOpacity'] || defaultDisplaySettings.region[dd_key].fillOpacity,
                display_defaults: {},
            };

            /*
            А это неизменяемые параметры, они будут использованы для изменения стилей при событиях
            on('mouseover') и on('mouseout')
            * */
            options.display_defaults = {
                region: {
                    default: {
                        stroke: options['stroke'],
                        borderColor: options['color'],
                        borderWidth: options['width'],
                        borderOpacity: options['borderOpacity'],
                        fill: options['fill'],
                        fillColor: options['fillColor'],
                        fillOpacity: options['fillOpacity'],
                    },
                    hover: {
                        stroke: defaultDisplaySettings.region[dd_key_hover].stroke || defaultDisplaySettings.region[dd_key].stroke,
                        borderColor: defaultDisplaySettings.region[dd_key_hover].borderColor || defaultDisplaySettings.region[dd_key].borderColor,
                        borderWidth: defaultDisplaySettings.region[dd_key_hover].borderWidth || defaultDisplaySettings.region[dd_key].borderWidth,
                        borderOpacity: defaultDisplaySettings.region[dd_key_hover].borderOpacity || defaultDisplaySettings.region[dd_key].borderOpacity,
                        fill: defaultDisplaySettings.region[dd_key_hover].fill || defaultDisplaySettings.region[dd_key].fill,
                        fillColor: defaultDisplaySettings.region[dd_key_hover].fillColor || defaultDisplaySettings.region[dd_key].fillColor,
                        fillOpacity: defaultDisplaySettings.region[dd_key_hover].fillOpacity || defaultDisplaySettings.region[dd_key].fillOpacity,
                    }
                },
                poi: {
                    any: {
                        iconClass: defaultDisplaySettings.poi.any.iconClass,
                        markerColor: defaultDisplaySettings.poi.any.markerColor,
                        iconColor: defaultDisplaySettings.poi.any.iconColor,
                        iconXOffset: defaultDisplaySettings.poi.any.iconXOffset,
                        iconYOffset: defaultDisplaySettings.poi.any.iconYOffset,
                    },
                    /*
                    // не используются, так как есть проблемы с отловом события mouseover/mouseout над POI
                    // см /public/frontend/view.map.fullscreen.js:82
                    default: {
                        iconClasses: 'fa-brands fa-fort-awesome', // display.poi.any
                        markerColor: 'green',
                        iconColor: '#FFF',
                        iconXOffset: -1,
                        iconYOffset: 0
                    },
                    hover: {
                        iconClasses: 'fa-brands fa-fort-awesome', // display.poi.any
                        markerColor: 'red',
                        iconColor: '#FFF',
                        iconXOffset: -1,
                        iconYOffset: 0
                    }*/
                }
            };

            let entity = null;
            switch (type) {
                case 'polygon': {
                    options.type = 'polygon';
                    entity = L.polygon(coords, options);
                    break;
                }
                case 'rect': {
                    options.type = 'rect';
                    entity = L.rectangle(coords, options);
                    break;
                }
                case 'circle': {
                    options.type = 'circle';
                    entity = L.circle(coords, options);
                    break;
                }
                case 'marker': {
                    options.type = 'poi';
                    options.keyboard = false;

                    let fa = {
                        icon: `fa ${options.display_defaults.poi.any.iconClass}`,
                        markerColor: options.display_defaults.poi.any.markerColor,
                        iconColor: options.display_defaults.poi.any.iconColor,
                        iconXOffset: options.display_defaults.poi.any.iconXOffset,
                        iconYOffset: options.display_defaults.poi.any.iconYOffset
                    }

                    // кроме проблем, упомянутых в
                    entity = L.marker(coords, {
                        id: options.id,
                        title: options.title,
                        layer: options.layer,
                        type: 'poi',
                        coords: options.coords,
                        keyboard: false,
                        icon: L.icon.fontAwesome({
                            iconClasses: `fa ${fa.icon}`,
                            markerColor: fa.markerColor,
                            iconColor: fa.iconColor,
                            iconXOffset: fa.iconXOffset,
                            iconYOffset: fa.iconYOffset,
                        }),
                        poi: options.poi
                    });

                    break;
                }
                //@todo: СЮДА НАДО ДОБАВЛЯТЬ НОВЫЕ ТИПЫ ОБЪЕКТОВ НА КАРТЕ
            }

            if (entity) {
                dataset[ key ] = entity;
            }
        } );

        this.regionsDataset = dataset;

        return dataset;
    }

    /**
     * Возвращает Windows Location Hash Link
     *
     * @param id
     * @param action
     * @returns {string}
     */
    static WLH_makeLink(id = '', action = 'view') {
        return id !== '' ? `#${action}=[${id}]` : ``
    }

    /**
     * Анализируем Window.Location.Hash и определяем опции фокусировки/показа региона.
     * Возвращаем опции действия.
     *
     * Следует учитывать, что на карте может не быть региона, переданного в параметрах. Для обработки этой ситуации
     * передается массив карты (и имя текущего слоя?).
     *
     * @param dataset
     * @returns {{id_region: null, action: null}}
     */
    static WLH_getAction(dataset) {
        let regexp_pattern = /(view|focus)=\[(.*)\]/;
        let wlh = window.location.hash;
        let wlh_params = wlh.match(regexp_pattern);
        let options = {
            action: null,
            id_region: null
        };

        if (
            ((wlh.length > 1) && (wlh_params !== null))
            &&
            (((wlh_params[1] === 'view') || (wlh_params[1] === 'focus')) && (wlh_params[2] !== ''))
            &&
            ( wlh_params[2] in dataset )
        ) {
            options = {};
            options.action = wlh_params[1];
            options.id_region = wlh_params[2];
        }
        return options;
    }

    /**
     * Загружает контент из БД и записывает его в контейнер infoBox
     *
     * @todo: метод loadContent должен только ходить на бэк!
     *
     * @param target
     * @param id_region
     * @returns {boolean}
     */
    loadContent(id_region, target = 'section-infobox-content') {
        if (!(id_region in this.regionsDataset)) {
            console.log(`[${id_region}] not found at regionsDataset.`);
            return false;
        }
        let $target = $(`#${target}`);

        if (this.IS_DEBUG) console.log(`Called do_LoadContent for ${id_region}`);

        if (MapManager.current_infobox_region_id !== id_region) {
            let url = MapManager.makeURL('view', this.theMap.map['id'], id_region, false);

            $target.html('');

            $.get(url, function(){ }).done(function(data){
                if (this.IS_DEBUG) console.log(`data loaded, length ${data.length}`);

                MapManager.current_infobox_region_id = id_region;

                $target
                    .html(data)
                    .scrollTop(0)
                ;
                // scroll box to top
                // document.getElementById(target).scrollTop = 0;
            });
        }
    }

    /**
     * Управляет поведением контейнера infoBox
     *
     * @param event
     * @param id_region
     */
    manageInfoBox(event, id_region) {
        // Если контейнера нет - создаем его.
        // @todo: инстанс контейнера должен объявляться в MapManager ? как и признак его создания?
        if (!MapManager.__InfoBox) {
            MapManager.__InfoBox = new L.Control.InfoBox();
            this.map.addControl( MapManager.__InfoBox );
        }

        let $infobox = $("#section-infobox");
        let $infobox_toggle_button = $('#actor-section-infobox-toggle');
        let current_infobox_visible_state = $infobox_toggle_button.data('content-visibility');

        switch (event) {
            case 'show': {
                this.loadContent(id_region);

                window.location.hash = MapManager.WLH_makeLink(id_region);

                current_infobox_visible_state = true;

                $infobox.show();
                break;
            }
            case 'hide': {
                current_infobox_visible_state = false;

                history.pushState('', document.title, window.location.pathname);

                $infobox.hide();
                break;
            }
            case 'toggle': {
                if (current_infobox_visible_state) {
                    history.pushState('', document.title, window.location.pathname);
                    current_infobox_visible_state = false;
                } else {
                    current_infobox_visible_state = true;
                    window.location.hash = MapManager.WLH_makeLink(id_region);
                }
                $infobox.toggle();
                break;
            }
        }

        $infobox_toggle_button.data('content-visibility', current_infobox_visible_state);
    }

    /**
     * Управляет поведением контейнера HintBox.
     * В версии от 2024-07-06 и ранее оно называется $sections_present.title, #section-region-title
     *
     * @param event
     * @param id_region
     * @param title
     */
    manageHintBox(event = 'show', id_region, title = '') {
        if (!MapControls.controlHintBoxPresent) {
            return false;
        }

        let $target = $("#section-region-hint-content");
        if (event === 'hide') {
            if ($target) {
                $target.html('');
            }
            return true;
        }

        let region = this.getRegionProperties(id_region);
        title
            = (region.hasOwnProperty('title') && region['title'] !== '')
            ? region['title']
            : '';

        if ($target) {
            $target.html(title);
            return true;
        }

        return false;
    }

    /**
     * Исходя из имени, управляет поведением окна ColorBox, в которое загружается контент.
     *
     * @param event
     * @param id_region
     * @param title
     */
    manageColorBox(event = 'show', id_region, title = '') {
        let that = this;

        if (event === 'hide') {
            parent.$.colorbox.close();
            return true;
        }

        let url = MapManager.makeURL(
            'view',
            this.theMap.map['id'],
            id_region,
            MapControls.isLoadedToIFrame()
        );

        let region = this.getRegionProperties(id_region);
        title
            = (region.hasOwnProperty('title') && region['title'] !== '')
            ? region['title']
            : '';

        $.colorbox({
            href: url,
            width: that.theMap.display.viewoptions.width || 800,
            height: that.theMap.display.viewoptions.height || 600,
            opacity: 0.8,

            title: title,
            onComplete: function () {
                window.location.hash = MapManager.WLH_makeLink(id_region);
            },
            onClosed: function() {
                // что делаем при закрытии колобокса?
                history.pushState('', document.title, window.location.pathname);
                // window.location.hash = MapManager.WLH_makeLink(); // не нужно, потому что иначе оставляет решётку в WLH
            }
        });
    }

    /**
     * Возвращает всю информацию о регионе из датасета regionsDataset или false при отсутствии
     *
     * @param id_region
     * @returns {*|boolean}
     */
    getRegion(id_region) {
        return (
            this.regionsDataset.hasOwnProperty(id_region)
                ? this.regionsDataset[id_region]
                : false);
    }

    /**
     * Возвращает свойства (options) о регионе из датасета regionsDataset или false при отсутствии
     *
     * @param id_region
     */
    getRegionProperties(id_region) {
        return (
            this.regionsDataset.hasOwnProperty(id_region) && this.regionsDataset[id_region].hasOwnProperty('options')
            ? this.regionsDataset[id_region]['options']
            : false);
    }

    /**
     * Генерирует URL для действия
     *
     * @param action
     * @param map_alias
     * @param id_region
     * @param is_iframe
     * @returns {string}
     */
    static makeURL(action = 'view', map_alias, id_region, is_iframe = false) {
        let _act = null;
        let urls = window['REGION_URLS'];
        switch (action) {
            case 'view': {
                _act = urls['view'];
                break;
            }
            case 'edit': {
                _act = urls['edit'];
                break;
            }
        }
        return `${_act}?map=${map_alias}&id=${id_region}${ is_iframe ? '&resultType=iframe' : '' }`;
    }

    /**
     * OnClick фокусировка региона
     *
     * @todo: Похоже, что не учитывает POI. А должен?
     *
     * @param map
     * @param id_region
     * @param LGS
     */
    onClickFocusRegion(map, id_region, LGS) {
        let id_layer = this.theMap['regions'][id_region]['layer'];
        let is_visible = LGS[id_layer].visible;
        let bounds;
        let focus_highlight_color = this.options.focus.highlight_color;
        let focus_timeout = this.options.focus.timeout;

        if (this.IS_DEBUG) console.log(`onclick_FocusRegion -> layer ${id_layer} is_visible ${is_visible}`);
        if (this.IS_DEBUG) console.log( LGS[id_layer].actor );

        // сохраняем оригинальный стиль региона
        // let old_style = this.regionsDataset[id_region].options['fillColor'];
        let old_style = this.getRegionProperties(id_region)['fillColor'];

        if (is_visible) {
            bounds = this.regionsDataset[id_region].getBounds();

            this.regionsDataset[ id_region ].setStyle({ fillColor: focus_highlight_color }); //@todo: делать ли это если установлена соответствующая опция? Какая?

            // когда-то было:
            // map.panTo( bounds.getCenter(), { animate: true, duration: 1, noMoveStart: true});
            map.setView( bounds.getCenter(), map._zoom, { animate: true, duration: 1, noMoveStart: true} );

        } else {
            map.setZoom( this.theMap['layers'][id_layer]['zoom']+1, {
                animate: true
            } );
            bounds = this.regionsDataset[id_region].getBounds();

            this.regionsDataset[ id_region ].setStyle({ fillColor: focus_highlight_color }); //@todo: делать ли это если установлена соответствующая опция? Какая? (то есть метод с проверкой?)

            map.panTo( bounds.getCenter(), { animate: true, duration: 1, noMoveStart: true});
        }

        // восстанавливаем по таймауту
        let that = this;
        setTimeout(function(){
            that.regionsDataset[id_region].setStyle({ fillColor: old_style });
        }, focus_timeout);
    }

    /**
     *
     * @param map
     * @param id_region
     * @param LGS
     */
    wlhFocusRegion(map, id_region, LGS) {
        let region = this.getRegion(id_region);
        if (!region) {
            return false;
        }

        /* позиционируем */
        let id_layer = this.theMap['regions'][id_region]['layer'];
        let is_visible = LGS[id_layer].visible;
        let bounds;

        if (this.IS_DEBUG) {
            console.log("Текущий зум: ", map.getZoom());
            console.log("Запрашиваемый регион: " , id_region);
            console.log("принадлежит группе слоёв " , id_layer);
            console.log("Видимость группы слоёв с регионом: " , is_visible);
            console.log("Описание группы слоёв: ", LGS[id_layer]);
        }

        let zmin = LGS[id_layer].zoom_min;
        let zmax = LGS[id_layer].zoom_max;

        if (this.IS_DEBUG) {
            console.log("Зум слоя (из инфо карты)", window.theMap['layers'][id_layer]['zoom']);
            console.log("Зум слоя (из layergroup)", LGS[id_layer]['zoom']);
        }

        let currentZoom = map.getZoom();

        // добавляем все слои
        Object.keys( LGS ).forEach(function(lg){
            map.addLayer( LGS[lg].actor );
            LGS[lg].visible = true;
        });

        map.fitBounds(this.baseMapBounds);

        map.setZoom( window.theMap.display.zoom, {
            animate: false
        });

        // pan to
        if (region.options.type == 'poi') {
            // poi
            bounds = region._latlng;
            map.panTo( bounds, { animate: false, duration: 1, noMoveStart: true});
        } else {
            // polygon
            bounds = region.getBounds();
            map.panTo( bounds.getCenter(), { animate: false, duration: 1, noMoveStart: true});
        }

        // удаляем все невидные слои
        Object.keys( LGS ).forEach(function(lg){
            if (!(window.theMap['layers'][id_layer]['zoom'].inbound(zmin, zmax))) {
                if (IS_DEBUG) console.log('Надо скрыть слой ' + lg);

                map.removeLayer( LGS[id_layer].actor );
                LGS[id_layer].visible = false;
            }
        });
    }



}