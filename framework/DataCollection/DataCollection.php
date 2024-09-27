<?php

namespace LiveMapEngine\DataCollection;

use RuntimeException;

/**
 * Исключение
 */
class DataCollectionException extends RuntimeException { }

/**
 * DataCollection class
 *
 * Оперирует с набором данных, для обработки которого можно применять пути с разделителями вида `foo->bar->baz`
 * или `foo.bar.baz`. Пустой разделитель означает, что переданный ключ является полным путём к переменной. Это позволяет,
 * например, использовать DataCollection вместе с $_REQUEST для доступа, с учетом значений по-умолчанию и тайп-кастингом
 * с использованием кастомной функции.
 *
 * @version 2024-09-27
 */
class DataCollection
{
    /**
     * @var string|null
     */
    private ?string $source;

    /**
     * @var callable
     */
    private $parser;

    /**
     * @var bool
     */
    private bool $is_associative = false;

    /**
     * @var mixed|\stdClass
     */
    private mixed $data;

    /**
     * @var mixed Global Default data (для всех полей ниже)
     */
    private mixed $default = '';

    /**
     * @var bool
     */
    private bool $is_parsed = false;

    /**
     * @var string
     */
    private string $separator = '';

    /**
     * Создает коллекцию данных
     *
     * @param string|null $data - данные
     * @param callable|null $parser - парсер (коллбэк или имя функции)
     */
    public function __construct(string $data = null, callable $parser = null)
    {
        $this->source = $data;
        if (!is_null($parser)) {
            $this->parser = $parser;
        }
    }

    /**
     * Импортирует данные в коллекцию. Это могут быть:
     * - array
     * - object
     * - string - в этом случае нужно будет вызвать после метод parse()
     *
     * @param mixed|null $data
     * @return $this
     */
    public function import(mixed $data = null):self
    {
        if (is_null($data)) {
            $this->is_parsed = false;
            return $this;
        }

        switch (gettype($data)) {
            case 'array': {
                $this->data = $data;
                $this->is_associative = true;
                $this->is_parsed = true;
                break;
            }
            case 'string': {
                $this->source = $data;
                $this->is_associative = false;
                $this->is_parsed = false;
                break;
            }
            case 'object': {
                $this->data = $data;
                $this->is_parsed = true;
                $this->is_associative = false;
                break;
            }
            default: {
                throw new DataCollectionException("Invalid data type given for DataCollection class");
            }
        }
        return $this;
    }

    /**
     * Устанавливает парсер данных
     *
     * @param callable $parser
     * @return DataCollection
     */
    public function setParser(callable $parser):self
    {
        $this->parser = $parser;
        return $this;
    }

    /**
     * Устанавливает признак ассоциативности распарсенного набора данных
     *
     * @param bool $is_associative
     * @return DataCollection
     */
    public function setIsAssociative(bool $is_associative = true):self
    {
        $this->is_associative = $is_associative;
        return $this;
    }

    /**
     * Устанавливает разделитель в "путях" к данным
     *
     * @param string $separator
     * @return $this
     */
    public function setSeparator(string $separator = '->'):self
    {
        $this->separator = $separator;
        return $this;
    }

    /**
     * Устанавливает новое дефолтное значение для всех запросов ниже
     *
     * @param mixed $default
     * @return $this
     */
    public function setDefault(mixed $default = ''):self
    {
        $this->default = $default;
        return $this;
    }

    /**
     * Парсит данные
     *
     * @param $source
     * @return mixed
     */
    public function parse($source = null):mixed
    {
        if (!is_null($source)) {
            $this->source = $source;
        }

        if (is_null($this->source)) {
            throw new DataCollectionException("Empty source data given to DataCollection class!");
        }

        if (is_null($this->parser)) {
            $this->parser = 'json_decode';
        }

        $this->data = \call_user_func_array($this->parser, [
            'json'          =>  $this->source,
            'associative'   =>  $this->is_associative
        ]);

        $this->is_parsed = true;

        return $this->data;
    }

    /**
     * Возвращает данные по пути, с учетом дефолтного значения и приведения типа.
     *
     * NB: Сначала проверяется наличие данных, при отсутствии - используется дефолтное значение,
     * а типы приводятся в самом конце (в том числе и к дефолтному значению)
     *
     * @param string|null $path - если строго NULL - далее обрабатываем дефолтное значение.
     * @param mixed|null $default - значение по-умолчанию, если не найдено в структуре или данные не распарсены.
     * @param string|null $separator - смысл передавать сепаратор здесь непонятен. Наверное, я что-то имел в виду при написании, но забыл что.
     * @param mixed|null $casting - приведение к типу. Если не null и
     * - строка ['bool', 'int', 'float', 'string', 'array', 'object', 'null'] - приводим к соотв. типу.
     * - callable (closure) - вызываем соотв. функцию, передавая ей значение и возвращаем результат.
     * - null - результат возвращается как есть, без приведения типа (обычно, как строка)
     *
     * @return mixed
     */
    public function getData(?string $path = '', mixed $default = null, ?string $separator = null, mixed $casting = null):mixed
    {
        $separator = is_null($separator) ? $this->separator : $separator;
        $default = is_null($default) ? $this->default : $default;

        //@todo: ? сделать опцию "возвращать дефолтное значение если данные не распарсены" ?
        if (!$this->is_parsed) {
            return $default;
        }

        /*
        $result =
            $this->is_associative
                ? self::getDataFromArray($this->data, $path, $default, $separator)
                : self::getDataFromObject($this->data, $path, $default, $separator);
        */

        // если путь пуст - возвращаем дефолтное значение
        // НЕТ, так делать нельзя. Если путь пуст - должны возвращаться ВСЕ данные.
        if (is_null($path)) {
            $result = $default;
        } else {
            // иначе разбираем путь и возвращаем данные в зависимости от ассоциативности
            $result =
                $this->is_associative
                ? self::getDataFromArray($this->data, $path, $default, $separator)
                : self::getDataFromObject($this->data, $path, $default, $separator);
        }

        // если приведение типов задано
        if (!is_null($casting)) {
            // и это коллбэк - вызываем функцию, передавая туда результат
            if (is_callable($casting)) {
                $result = call_user_func($casting, $result);
            } else {
                // иначе просто устанавливаем тип
                settype($result, $casting);
            }
        }

        // и возвращаем результат
        return $result;
    }

    /**
     * Устанавливает данные с учетом пути.
     *
     * @param string $path
     * @param mixed|null $value
     * @param string|null $separator
     * @return bool
     */
    public function setData(string $path = '', mixed $value = null, ?string $separator = null): bool
    {
        $separator = is_null($separator) ? $this->separator : $separator;

        if (!$this->is_parsed) {
            return false;
        }

        return
            $this->is_associative
            ? self::setDataToArray($this->data, $path, $value, $separator)
            : self::setDataToObject($this->data, $path, $value, $separator);
    }

    /**
     * Проверяет наличие данных по ключу "пути"
     *
     * @param string $path
     * @param string|null $separator
     * @return bool
     */
    public function hasKey(string $path, ?string $separator = null):bool
    {
        $separator = is_null($separator) ? $this->separator : $separator;

        return
            $this->is_associative
            ? self::checkKeyInArray($this->data, $path, $separator)
            : self::checkKeyInObject($this->data, $path, $separator);
    }

    /* Private implementations */

    private static function checkKeyInObject($dataset, string $path, string $separator = '->'):bool
    {
        if (empty($path)) {
            return false;
        }

        $keys = \explode($separator, $path);
        $data = $dataset;

        foreach ($keys as $key) {
            if (isset($data->{$key})) {
                $data = $data->{$key};
            } else {
                return false;
            }
        }
        return true;
    }

    private static function checkKeyInArray($dataset, string $path, string $separator = '->'):bool
    {
        if (empty($path)) {
            return false;
        }

        $keys = \explode($separator, $path);
        $data = $dataset;

        foreach ($keys as $key) {
            if (isset($data[$key])) {
                $data = $data[$key];
            } else {
                return false;
            }
        }
        return true;
    }

    private static function getDataFromObject($dataset, string $path = '', mixed $default = '', string $separator = '->'):mixed
    {
        if (empty($path)) {
            return $dataset;
        }

        if (! $dataset instanceof \stdClass) {
            return $default;
        }

        $keys
            = empty($separator)
            ? [$path]
            : \explode($separator, $path);
        $data = clone $dataset;

        foreach ($keys as $key) {
            if (isset($data->$key)) {
                $data = $data->$key;
            } else {
                return $default;
            }
        }

        return $data;
    }

    private static function getDataFromArray($dataset, string $path = '', mixed $default = '', string $separator = '->'):mixed
    {
        if (empty($path)) {
            return $dataset;
        }

        $keys
            = empty($separator)
            ? [$path]
            : \explode($separator, $path);

        $data = $dataset;

        foreach ($keys as $key) {
            if (isset($data[$key])) {
                $data = $data[$key];
            } else {
                return $default;
            }
        }

        return $data;
    }

    private static function setDataToObject(&$dataset, string $path, mixed $data, string $separator = '->'):bool
    {
        if (empty($path)) {
            return false;
        }
        if (! $dataset instanceof \stdClass) {
            return false;
        }

        $keys = \explode($separator, $path);
        $temp = &$dataset;

        foreach ($keys as $key) {
            if (!isset($temp->{$key})) {
                $temp->{$key} = new \stdClass();
            }
            $temp = &$temp->{$key};
        }

        $temp = $data;

        return true;
    }

    private static function setDataToArray(array $source, string $path, $data, string $separator = '->'):bool
    {
        if (empty($path)) {
            return false;
        }

        $keys = \explode($separator, $path);
        $temp = &$source;

        foreach ($keys as $key) {
            if (!isset($temp[$key])) {
                $temp[$key] = [];
            }
            $temp = &$temp[$key];
        }
        $temp = $data;

        return true;
    }

}

# -eof- #
