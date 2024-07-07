const VERSION = '2024-07-07';
const focus_animate_duration = window.theMap['display']['focus']['animate_duration'] || 0.7;
const focus_highlight_color = window.theMap['display']['focus']['highlight_color'] || '#ff0000';
const focus_timeout = window.theMap['display']['focus']['timeout'] || 1500;
const IS_DEBUG = false;
const DEBUG_SET_STYLE_WHILE_HOVER = true;

let base_map_bounds;
let map;

$(function() {
    let _mapManager = window.mapManager;

    map = _mapManager.createMap('map'); // also _mapManager.map;
    _mapManager.setBackgroundColor(".leaflet-container");

    base_map_bounds = _mapManager.getBounds();

    let image = _mapManager.createImageOverlay(base_map_bounds);
    image.addTo(_mapManager.map);

    // строим массив всех регионов
    _mapManager.buildRegionsDataset();

    _mapManager.map.fitBounds(base_map_bounds);
    _mapManager.map.setZoom( window.theMap['display']['zoom'] );

    // биндим к каждому объекту функцию, показывающую информацию
    Object.keys( _mapManager.regionsDataset ).forEach(function(id_region){
        let map_element = _mapManager.getMapElement(id_region);

        map_element.on('click', function() {
            // альтернатива - менять window.location.hash
            // а ниже отлавливать его изменения

            // infobox или colorbox - зависит от режима отображения региона

            //@todo: что дальше? Универсальный метод открытия описания региона, переименовать hintbox
            //@todo: переименуем в hintbox

            if (_mapManager.infobox_mode === 'infobox') {

                if (MapManager.current_infobox_region_id === id_region) {
                    _mapManager.manageInfoBox('toggle', id_region);
                } else {
                    _mapManager.manageInfoBox('show', id_region);
                }
                MapManager.current_infobox_region_id = id_region;

            } else if (_mapManager.infobox_mode === 'colorbox') {

                window.location.hash = MapManager.WLH_makeLink(id_region);

                _mapManager.manageColorBox('show', id_region);

            } else if (_mapManager.infobox_mode === 'hintbox') {
                _mapManager.manageHintBox('show', id_region);
            }



        }).on('mouseover', function() {
            // выставляем стили для региона при наведении на него мышки, для маркера типа POI стиль не ставится
            if (false === DEBUG_SET_STYLE_WHILE_HOVER) return;

            if (map_element.options.type !== 'poi') {
                // Если используется SVG renderer - можно делать через setStyle({ className: '' })
                map_element.setStyle({
                    stroke: map_element.options.display_defaults.region.hover.stroke,
                    color: map_element.options.display_defaults.region.hover.borderColor,
                    weight: map_element.options.display_defaults.region.hover.borderWidth,
                    opacity: map_element.options.display_defaults.region.hover.borderOpacity,

                    fill: map_element.options.display_defaults.region.hover.fill,
                    fillColor: map_element.options.display_defaults.region.hover.fillColor,
                    fillOpacity: map_element.options.display_defaults.region.hover.fillOpacity,
                });
            } else {
                // Событие MOUSEOVER для L.Marker'а ловится корректно и позволяет изменить иконку элемента, НО...
                // ... но событие MOUSEOUT не ловится (или ловится однократно) и поменять иконку обратно невозможно.
                // Вполне вероятно, что это баг плагина FontAwesomeIcon, надо тестировать.
                // По этой же причине дефолтные параметры для POI закомментированы в конфиге
                return false;
                /*map_element
                    .setIcon(L.icon.fontAwesome({
                    iconClasses: `fa ${map_element.options.poi.hover.iconClasses}`,
                    markerColor: map_element.options.poi.hover.markerColor,
                    iconColor: map_element.options.poi.hover.iconColor,
                    iconXOffset: map_element.options.poi.hover.iconXOffset,
                    iconYOffset: map_element.options.poi.hover.iconYOffset,
                }));*/
            }

        }).on('mouseout', function() {
            if (false === DEBUG_SET_STYLE_WHILE_HOVER) return;

            // выставляем стили для региона при наведении при уходе с него мышки, для маркера типа POI стиль не ставится (по крайней мере не так)
            if (map_element.options.type !== 'poi') {
                map_element.setStyle({
                    stroke: map_element.options.display_defaults.region.default.stroke,
                    color: map_element.options.display_defaults.region.default.borderColor,
                    weight: map_element.options.display_defaults.region.default.borderWidth,
                    opacity: map_element.options.display_defaults.region.default.borderOpacity,

                    fill: map_element.options.display_defaults.region.default.fill,
                    fillColor: map_element.options.display_defaults.region.default.fillColor,
                    fillOpacity: map_element.options.display_defaults.region.default.fillOpacity,
                });
            } else {
                return false;
                // событие MOUSEOUT НЕ ЛОВИТСЯ и поменять иконку обратно невозможно
                /*map_element.setIcon(L.icon.fontAwesome({
                    iconClasses: `fa ${map_element.options.poi.default.iconClass}`,
                    markerColor: map_element.options.poi.default.markerColor,
                    iconColor: map_element.options.poi.default.iconColor,
                    iconXOffset: map_element.options.poi.default.iconXOffset,
                    iconYOffset: map_element.options.poi.default.iconYOffset,
                }));*/
            }

        });
    });

    // раскладываем регионы по layer-группам
    Object.keys( _mapManager.regionsDataset ).forEach(function(id_region){
        let id_layer = window.theMap['regions'][id_region]['layer'];

        let zoom_default = window.theMap['layers'][id_layer]['zoom'];
        let zoom_min = window.theMap['layers'][id_layer]['zoom_min'];
        let zoom_max = window.theMap['layers'][id_layer]['zoom_max'];

        // Создаем слой с нужными параметрами в структуре LGS
        if (!(id_layer in _mapManager.LGS)) {
            let l = new L.LayerGroup();
            _mapManager.LGS[ id_layer ] = {
                actor: l,
                visible: false, // все слои скрыты
                zoom: zoom_default,
                zoom_min: zoom_min,
                zoom_max: zoom_max,
            };
        }
        _mapManager.LGS[id_layer].actor.addLayer( _mapManager.regionsDataset[id_region] );
    });

    // показываем layer-группы или скрываем нужные
    Object.keys( _mapManager.LGS ).forEach(function(lg) {
        if (
            _mapManager.map.getZoom().inbound( _mapManager.LGS[lg].zoom_min, _mapManager.LGS[lg].zoom_max )
        ) {
            _mapManager.map.addLayer( _mapManager.LGS[lg].actor );
            _mapManager.LGS[lg].visible = true;
        } else {
            _mapManager.LGS[lg].actor.addTo(_mapManager.map);
            _mapManager.LGS[lg].actor.remove(); // map.addLayer( LGS[lg].actor );
            _mapManager.LGS[lg].visible = false;
        }
    });


    //@todo: нужно устанавливать в MapControls соотв.флаги
    MapControls.controlRegionsBoxPresent    = MapControls.declareControl_RegionsBox();
    MapControls.controlInfoBoxPresent       = MapControls.declareControl_InfoBox();
    MapControls.controlBackwardPresent      = MapControls.declareControl_Backward();
    MapControls.controlHintBoxPresent       = MapControls.declareControl_RegionTitle(); //@todo: rename to hintbox

    // не показываем контрол "назад" если страница загружена в iframe
    if (! MapControls.isLoadedToIFrame()) {
        // и контрол создан
        if (MapControls.controlBackwardPresent) {
            _mapManager.map.addControl( new L.Control.Backward() );
        }
    }

    // показываем список регионов только если он не пуст
    if (regions_with_content_ids.length) {
        // и контрол создан
        if (MapControls.controlRegionsBoxPresent) {
            _mapManager.map.addControl( new L.Control.RegionsBox() );
        }
    }

    // анализируем window.location.hash
    // (_mapManager.options.checkWLH_onStart)
    //@todo: этот режим должен работать, если в MapManager установлен какой-то флаг. Какой? И как он передается из конфига карты?
    if (true) {
        let wlh_options = MapManager.WLH_getAction(_mapManager.regionsDataset);
        if (wlh_options) {
            let id_region = wlh_options.id_region;

            // было бы более интересным решением имитировать триггером клик по ссылке на регионе, но.. оно не работает
            // $("a.action-focus-at-region[data-region-id='" + wlh_options.id_region + "']").trigger('click');

            if (wlh_options.id_region != null) {
                _mapManager.wlhFocusRegion(_mapManager.map, wlh_options.id_region, _mapManager.LGS);

                if (_mapManager.infobox_mode === 'infobox') {
                    _mapManager.manageInfoBox('show', id_region);
                    MapManager.current_infobox_region_id = id_region;
                } else if (_mapManager.infobox_mode === 'colorbox') {
                    _mapManager.manageColorBox('show', id_region);
                } else if (_mapManager.infobox_mode === 'hintbox') {
                    _mapManager.manageHintBox('show', id_region);
                }
            }
        }
    }

    // Событие на зуме
    _mapManager.map.on('zoomend', function() {
        let currentZoom = _mapManager.map.getZoom();
        // console.log("Current zoom: " + currentZoom);

        Object.keys( _mapManager.LGS ).forEach(function(lg){
            let zmin = _mapManager.LGS[lg].zoom_min;
            let zmax = _mapManager.LGS[lg].zoom_max;
            let actor = _mapManager.LGS[lg].actor;

            if (currentZoom.inbound(zmin, zmax)) {
                _mapManager.map.addLayer( actor );
                _mapManager.LGS[lg].visible = true;
            }
            else {
                _mapManager.map.removeLayer( actor );
                _mapManager.LGS[lg].visible = false;
            }
        });
    });

});

$(document).on('click', '#actor-edit', function() {
    // клик на кнопку "редактировать"
    let _mapManager = window.mapManager;

    let region_id = $(this).data('region-id');
    document.location.href = MapManager.makeURL('edit', _mapManager.map_alias, region_id);

}).on('click', '#actor-regions-toggle', function (el) {

    MapControls.toggle_Regions(this);

}).on('click', '#actor-viewbox-toggle', function (el) {

    MapControls.toggle_Info(this);

}).on('click', "#actor-backward-toggle", function (el) {

    MapControls.toggle_Backward(this);

}).on('change', "#sort-select", function(e){
    // клик на смену режима сортировки в инфобоксе "regions"
    let must_display = (e.target.value === 'total') ? "#data-ordered-alphabet" : "#data-ordered-latest";
    let must_hide = (e.target.value === 'total') ? "#data-ordered-latest" : "#data-ordered-alphabet";
    $(must_hide).hide();
    $(must_display).show();

}).on('click', '.action-focus-at-region', function(){
    // клик на ссылке в списке регионов

    let _mapManager = window.mapManager;

    let id_region = $(this).data('region-id');
    // console.log(`current_infobox_region_id = ${MapManager.current_infobox_region_id}`);

    _mapManager.onClickFocusRegion(map, id_region, _mapManager.LGS);
    _mapManager.manageInfoBox('show', id_region);

    window.location.hash = MapManager.WLH_makeLink(id_region);
    return false;

}).on('click', '#actor-section-infobox-toggle', function(){

    let _mapManager = window.mapManager;

    _mapManager.manageInfoBox('hide', null);

}).escape(function() {
    // ловит ESCAPE
    let _mapManager = window.mapManager;

    if (_mapManager.infobox_mode === 'infobox') {
        _mapManager.manageInfoBox('hide', null);
    } else if (_mapManager.infobox_mode === 'colorbox') {
        _mapManager.manageColorBox('hide', null, '');
    } else if (_mapManager.infobox_mode === 'hintbox') {
        _mapManager.manageHintBox('hide');
    }
});
