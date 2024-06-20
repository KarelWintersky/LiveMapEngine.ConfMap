## Хранить разметку карты в БД?

То есть сначала её надо импортировать

id_map
id_region
layer
SVG-path строка (longtext)
JS-строка разметки
Параметры выделения региона (из SVG)
dt_timestamp - это таймштамп файла, из которого импортировали контент!

(map_layers_regions или как-то так с дополнениями!)

## Кастомизация шаблонов

"Интересные места на карте" - нужно перенести в json-конфиг карты, в "display_defaults"

Наверное, в отдельный блок "sections"

display_defaults->sections->regions->title

## Кастомизация-кастомизация

Подумать на тему того, что шаблоны отображения регионов, например `/var/www.livemap/livemap.confmap/templates/view.region/view.region.html.tpl` может быть перекрыт
кастомным шаблоном, лежащим в

```
/var/www.livemap/livemap.confmap/public/storage/spring.confederation/templates.public/view.region.html.tpl
```

Так поддерживаются, конечно, не все шаблоны!
(а в будущем дойдем до того, что эти шаблоны хранятся в БД/редисе)

## edit_templates

Эту папку надо переименовать и переложить шаблоны в `templates.edit`




## PHP 8+ и файлменеджер

https://github.com/trippo/ResponsiveFilemanager/issues/734
https://github.com/trippo/ResponsiveFilemanager/issues/709
https://github.com/trippo/ResponsiveFilemanager/issues/708
https://github.com/trippo/ResponsiveFilemanager/issues/700
https://github.com/trippo/ResponsiveFilemanager/issues/694
https://github.com/trippo/ResponsiveFilemanager/issues/683
https://github.com/trippo/ResponsiveFilemanager/issues/703


GET http://confmap.local/frontend/favicons/apple-touch-icon.png 404
GET http://confmap.local/frontend/favicons/favicon-16x16.png 404 


