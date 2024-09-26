## Круговые диаграммы на HTML+CSS +

Это для отображения соотношения типов капитала в окне информации по региону:

Элементарный вариант через `conic-gradient`, изящно, но без подписей:
https://www.geeksforgeeks.org/how-to-create-a-pie-chart-using-html-css/

https://blog.logrocket.com/build-interactive-pie-charts-css-html/
Более сложный, много кода

https://stackoverflow.com/questions/10028182/how-to-make-a-pie-chart-in-css
Несколько вариантов, в том числе на канвасе (что довольно интересное решение)

И мне больше всего нравится вариант на канвасе!


## FileManager changes + 

https://github.com/trippo/ResponsiveFilemanager

Какую версию скачивать? 
Скачивать последнюю версию релиза (из неё брать JS и CSS, потому что их не скомпилировать, проблема пакетами ноды, что-то устарело и сломалось)
А PHP-файлы скачивать гита напрямую.

На данный момент скачана и подправлена версия "9.14.0", какие-то другие её доделки не требуются

Вопрос, не переложить ли её в ПАКЕТ, который ставить композером... но возникнет вопрос - как ставить конкретно этот пакет в другой каталог, не в vendor ?


```php
// $lme_map = $_COOKIE['kw_livemap_filemanager_storagepath'] ?? '';
$lme_map = 'spring.confederation';

// path from base_url to base of upload folder (with start and final /)
$upload_dir = "/storage/{$lme_map}/images/";

// relative path from filemanager folder to upload folder (with final /)
$current_path = "../../storage/{$lme_map}/images/";

//thumbs folder can't put inside upload folder
// relative path from filemanager folder to thumbs folder (with final /)
$thumbs_base_path = "../../storage/{$lme_map}/thumbs/";
```


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

+ "Интересные места на карте" - нужно перенести в json-конфиг карты, в "display_defaults"

+/- Наверное, в отдельный блок "sections"

+/- display_defaults->sections->regions->title

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


## User select on click

https://developer.mozilla.org/en-US/docs/Web/CSS/user-select