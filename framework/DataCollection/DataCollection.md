# DataCollection

Оперирует с набором данных, для обработки которого можно применять пути с разделителями вида `foo->bar->baz`
или `foo.bar.baz`. Пустой разделитель означает, что переданный ключ является полным путём к переменной. Это позволяет,
например, использовать DataCollection вместе с $_REQUEST для доступа, с учетом значений по-умолчанию и тайп-кастингом
с использованием кастомной функции.

# Примеры использования

## Для работы с $_REQUEST

```php
$request = new DataCollection();

// separator пустой, данные - асс.массив, парсинг не требуется
$request->import($_REQUEST); 

$id_map = $request->getData('edit:id:map');

$ipv4 = ip2long(\Arris\Helpers\Server::getIP());

// но можно сказать и иначе:

$ipv4 = $request->getData(path: null, default: \Arris\Helpers\Server::getIP(), casting: function ($ip) {
    // значение path === NULL означает, что далее обрабатываем дефолтное значение, которое равно getIP()
    return ip2long($ip);
});

// следующий пример интереснее

$is_extra_content = $request->getData('is_display_extra_content', default: 'no', casting: function ($data){
    // фишка в том, что с фронта установленный чекбокс приходит строкой `on` (по умолчанию, если не задан input val)
    // значение по-умолчанию задано 'no' (чекбокс снят, на бэк ничего не отправляется)
    // и проверяем, что передано. Либо 'on' (1), либо 'no' (0)
    return (strtolower($data) == 'on') ? 1 : 0;
});

// это заменяет менее длинный, но и менее наглядный код:
$is_extra_content = array_key_exists('is_display_extra_content', $_REQUEST) && strtolower($_REQUEST['is_display_extra_content']) == 'on' ? 1 : 0;

// здесь путь передается без разделителей; так как separator установлен в пустое значение - парсинг пути не используется
```

## Для преобразования данных

То, для чего класс делался изначально:
```php
// это JSON-данные
$content_extra = $region_data['content_extra'] ?? '{}';

$json = new DataCollection($content_extra);

// Так как это JSON, я, для наглядности работы с полями объекта, буду указывать path через '->',
// а здесь укажу separator '->'  
$json->setSeparator('->');

// парсим исходные данные в stdObject (так как isAssociative стоит по-умолчанию FALSE) 
$json->parse(); 

// извлекаем данные из датасета по пути 'population->count'
// дефолтное значение 0
// используем кастомное "приведение типа", число переводится в строку с форматированием
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

// ИЛИ, лучше, с использованием match:
$population = $json->getData(path: 'population->count', default: 0, casting: function($population) {
    return match (true) {
        $population >= 1000 =>  "~" . number_format($population / 1000, 0, '.', ' ') . ' млрд.',
        $population >= 1    =>  "~" . number_format($population, 0, '.', ' ') . ' млн.',
        $population > 0     =>  "~" . number_format($population * 1000, 0, '.', ' ') . ' тыс.',
        default             =>  0
    };
});


// теперь мы передаем в $json по тому же пути отформатированные данные
$json->setData('population->count', $population);

// но путь может быть и другой, тогда старый ключ не затрётся, например:
$json->setData('population->count_formatted', $population);

// вот теперь в $json часть данных модифицирована правилами. Да, можно было бы работать с $JSON структурой как асс.массивом
// напрямую, без обвязки, но... так красивее! 

// Теперь мы отдаем все данные в шаблон.
// ВАЖНО, В ШАБЛОНЕ ХОДИМ ТАК: {$json->economy->type}, А НЕ ЧЕРЕЗ ТОЧКУ!!!!
// Если мы хотим ходить через точку - надо перед этим сказать
// $json->setIsAssociative();

$template->assign('json', $json->getData());

// итд
```

# Exceptions

В случае ошибки кидает исключение `DataCollectionException`. Исключение кидается в двух случаях:

- в метод `import()` передали не массив (array), не объект (object), не строку (string) и не NULL. Другие данные распарсить мы не можем.
- при вызове `parse()` исходные данные - NULL. То есть либо в конструктор, либо в `import()` передали в качестве данных NULL 