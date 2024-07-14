/**
 * Контролы на карте
 */
class MapControls {
    static VERSION = '2024-06-14';

    // А может для каждого контрола свой класс?
    // Тогда можно будет инкапсулировать в каждом контроле свое поведение. Инстанциировать все контролы, но создавать только нужные.
    // И обрабатывать их поведение только при их реальном наличии...
    // ?
    static controlRegionsBoxPresent = false;
    static controlInfoBoxPresent = false;
    static controlBackwardPresent = false;
    static controlHintBoxPresent = false;
    /**
     * Инстансы контролов
     */
    static __regionsBox = null;

    static __infoBox = null;

    static __backward = null;

    static __hintBox = null;

    /**
     * Создает в объекте L Control-элемент: имя региона (для карт типа folio)
     * @todo: rename to hintBox (и метод, и контейнер)
     * @todo: кстати, имена контейнеров нужно где-то держать в статике. Тут же?
     * @todo: и вообще, отказаться от статичных методов, передавать ID контейнеров через конструктор
     *
     * @param target
     * @param position
     * @returns {boolean}
     */
    static declareControl_RegionHint(target = 'section-region-hint', position = 'topright') {
        let $target = $(`#${target}`);
        if ($target.length == 0) {
            return false;
        }

        L.Control.Hint = L.Control.extend({
            options: {
                position: $target.data('leaflet-control-position') || 'topright'
            },
            onAdd: function(map) {
                let div = L.DomUtil.get(target);
                L.DomUtil.removeClass(div, 'invisible');
                L.DomEvent.disableScrollPropagation(div);
                L.DomEvent.disableClickPropagation(div);
                return div;
            },
            onRemove: function(map){ }
        });
        return true;
    }

    /**
     * декларирует под именем L.Control.RegionsBox
     * инофобокс
     *
     * @param target
     * @returns {boolean}
     */
    static declareControl_RegionsBox(target = 'section-regions') {
        let $target = $(`#${target}`);
        if ($target.length < 1) {
            return false;
        }

        L.Control.RegionsBox = L.Control.extend({
            is_content_visible: false,
            options: {
                position: $target.data('leaflet-control-position')
            },
            onAdd: function(map) {
                let div = L.DomUtil.get(target);
                L.DomUtil.removeClass(div, 'invisible');
                L.DomUtil.enableTextSelection();
                L.DomEvent.disableScrollPropagation(div);
                L.DomEvent.disableClickPropagation(div);
                return div;
            },
            onRemove: function(map) {}
        });
        return true;
    }

    /**
     * Создает в объекте L Control элемент: информация о регионе
     *
     * @param target
     * @returns {boolean}
     */
    static declareControl_InfoBox(target = 'section-infobox') {
        let $target = $(`#${target}`);
        if ($target.length < 1) {
            return false;
        }

        L.Control.InfoBox = L.Control.extend({
            is_content_visible: false,
            options: {
                position: $target.data('leaflet-control-position')
            },
            onAdd: function(map) {
                let div = L.DomUtil.get(target);
                L.DomUtil.removeClass(div, 'invisible');
                L.DomUtil.enableTextSelection();
                L.DomEvent.disableScrollPropagation(div);
                L.DomEvent.disableClickPropagation(div);
                return div;
            },
            onRemove: function(map) {}
        });
        return true;
    }

    /**
     * Создает в объекте L Control элемент: кнопка "назад"
     *
     * @param target
     * @returns {boolean}
     */
    static declareControl_Backward(target = 'section-backward') {
        let $target = $(`#${target}`);
        if ($target.length < 1) {
            return false;
        }

        L.Control.Backward = L.Control.extend({
            options: {
                position: $target.data('leaflet-control-position') || 'bottomleft'
            },
            onAdd: function(map) {
                let div = L.DomUtil.get(target);
                L.DomUtil.removeClass(div, 'invisible');
                L.DomEvent.disableScrollPropagation(div);
                L.DomEvent.disableClickPropagation(div);
                return div;
            },
            onRemove: function(map){}
        });
        return true;
    }

    /**
     * Переключатель видимости контейнера Backward control
     *
     * @param el
     */
    static toggle_Backward(el) {
        let state = $(el).data('content-is-visible');
        let text = (state == false) ? '&nbsp;&nbsp;&lt;&nbsp;&nbsp;' : '&nbsp;&nbsp;&gt;&nbsp;&nbsp;';
        $(el).html(text);

        let data = $(el).data('content');
        $(`#${data}`).toggle();
        $(el).data('content-is-visible', !state);
    }

    /**
     * Переключатель видимости контейнера регионов
     *
     * @param el
     */
    static toggle_Regions(el) {
        let state = $(el).data('content-is-visible');
        let text = (state == false) ? '&nbsp;Скрыть&nbsp;' : 'Показать';
        $(el).html(text);

        let data = $(el).data('content');
        $(`#${data}`).toggle();
        $('#sort-select').toggle();
        $(el).data('content-is-visible', !state);
    }

    /**
     * Переключатель видимости контейнера инфо
     *
     * @param el
     */
    static toggle_Info(el) {
        let state = $(el).data('content-is-visible');
        let text = (state == false) ? '&nbsp;Скрыть&nbsp;' : 'Показать';
        $(el).html(text);

        let data = $(el).data('content');
        $(`#${data}`).toggle();
        $(el).data('content-is-visible', !state);
    }

    /**
     * Проверяет, загружена ли страница в ифрейм?
     *
     * @returns {boolean}
     */
    static isLoadedToIFrame() {
        return (window != window.top || document != top.document || self.location != top.location);
    }


}