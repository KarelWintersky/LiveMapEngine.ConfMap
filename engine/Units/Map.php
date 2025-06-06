<?php

namespace App\Units;

use LiveMapEngine\Helpers;

/**
 * Кастомный механизм парсинга
 */
class Map
{
    private $data;

    public function __construct()
    {
    }

    /**
     * Кастомный парсер, используемый для парсинга конфига
     *
     * @param $fn
     * @return mixed
     * @throws \ColinODell\Json5\SyntaxError
     */
    public function parseJSONFile($fn)
    {
        return json5_decode($fn);
    }

    /**
     * Кастомный парсер, используемый для парсинга данных, принятых из БД
     *
     * @param $data
     * @return mixed
     * @throws \ColinODell\Json5\SyntaxError
     */
    public function parseJSON($data)
    {
        $this->data = json_decode($data);
        return $this->data;
    }

    /**
     * Получение поля данных из распарсенного выше блока JSON-данных.
     * Используется для упрощения работы с JSON-данными в проекте LiveMap.Confmap.
     * Вряд ли требуется перенос во фреймворк. Только если мы во фреймворке не допустим JSON-поля "искаропки" (а можем!)
     *
     * @param string $path
     * @param mixed $default
     * @param string $separator
     * @return mixed
     */
    public function getData(string $path = '', mixed $default = '', string $separator = '->'): mixed
    {
        if (!empty($path)) {
            if (Helpers::property_exists_recursive($this->data, $path, $separator)) {
                return Helpers::property_get_recursive($this->data, $path, $separator, $default);
            } else {
                return $default;
            }
        }
        return $this->data;
    }

    /**
     * Устанавливает значение по пути
     *
     * @param string $path
     * @param mixed $data
     * @param string $separator
     * @return bool
     */
    public function setData(string $path, mixed $data, string $separator = '->'):bool
    {
        $keys = \explode($separator, $path);
        $temp = &$this->data;

        foreach ($keys as $key) {
            if (!isset($temp->{$key})) {
                $temp->{$key} = new \stdClass();
            }
            $temp = &$temp->{$key};
        }

        $temp = $data;

        return true;
    }

}

# -eof- #