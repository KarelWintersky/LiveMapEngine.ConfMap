<?php

namespace LiveMapEngine;

use RuntimeException;

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
     * @var bool
     */
    private bool $data_is_parsed = false;

    /**
     * @var string
     */
    private string $separator;

    /**
     * @param string|null $data
     */
    public function __construct(string $data = null, callable $parser = null)
    {
        $this->source = $data;
        if (!is_null($parser)) {
            $this->parser = $parser;
        }
    }

    /**
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
     * @param string $separator
     * @return $this
     */
    public function setSeparator(string $separator = '->'):self
    {
        $this->separator = $separator;
        return $this;
    }

    /**
     * @param $source
     * @return mixed
     */
    public function parse($source = null):mixed
    {
        if (!is_null($source)) {
            $this->source = $source;
        }

        if (is_null($this->source)) {
            throw new RuntimeException("Empty source data given to DataCollection class!");
        }

        if (is_null($this->parser)) {
            $this->parser = 'json_decode';
        }

        $this->data = \call_user_func_array($this->parser, [
            'json'          =>  $this->source,
            'associative'   =>  $this->is_associative
        ]);

        $this->data_is_parsed = true;

        return $this->data;
    }

    /**
     * @param string $path
     * @param mixed|null $default - значение по-умолчанию, если не найдено в структуре или данные не распарсены
     * @param string|null $separator - смысл передавать сепаратор здесь непонятен. Наверное, я что-то имел в виду при написании, но забыл что.
     * @param mixed|null $casting - приведение к типу. Если не null и
     * - строка ['bool', 'int', 'float', 'string', 'array', 'object', 'null'] - приводим к соотв. типу.
     * - callable (closure) - вызываем соотв. функцию, передавая ей значение и возвращаем результат.
     * - null - результат возвращается как есть, без приведения типа (обычно, как строка)
     *
     * @return mixed
     */
    public function getData(string $path = '', mixed $default = null, ?string $separator = null, mixed $casting = null):mixed
    {
        $separator = is_null($separator) ? $this->separator : $separator;

        if (!$this->data_is_parsed) {
            return $default;
        }

        $result =
            $this->is_associative
            ? self::getDataFromArray($this->data, $path, $default, $separator)
            : self::getDataFromObject($this->data, $path, $default, $separator);

        if (!is_null($casting)) {
            if (is_callable($casting)) {
                $result = call_user_func($casting, $result);
            } else {
                settype($result, $casting);
            }
        }

        return $result;
    }

    public function setData(string $path = '', mixed $value = null, ?string $separator = null): bool
    {
        $separator = is_null($separator) ? $this->separator : $separator;

        if (!$this->data_is_parsed) {
            return false;
        }

        return
            $this->is_associative
            ? self::setDataToArray($this->data, $path, $value, $separator)
            : self::setDataToObject($this->data, $path, $value, $separator);
    }

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

        $keys = \explode($separator, $path);
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

        $keys = \explode($separator, $path);
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