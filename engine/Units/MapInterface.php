<?php

namespace Confmap\Units;

use Arris\Entity\Result;
use ColinODell\Json5\SyntaxError;
use Psr\Log\LoggerInterface;

interface MapInterface
{
    /**
     * @param $PDO
     * @param string $id_map
     * @param array $options
     * @param LoggerInterface|null $logger
     */
    public function __construct($PDO = null, string $id_map = '', array $options = [], ?LoggerInterface $logger = null);

    /**
     * Загружает JSON5-конфиг в поле $this->mapConfig
     *
     * @param null $path
     * @return Result
     * @throws SyntaxError
     */
    public function loadConfig($path = null): Result;

    /**
     * Возвращает конфиг карты
     *
     * @return \stdClass|mixed
     */
    public function getConfig():mixed;

    /**
     * Загружает из БД базовую информацию о регионах на карте для JS-билдера и списков.
     * Сохраняет её в полях экземпляра класса.
     *
     * @return Result
     */
    public function loadMap():Result;

    /**
     * Загружает из БД основную информацию по регионам для текущей карты.
     * Передается список ID регионов (на слое) или пусто для всех регионов со всех слоёв.
     *
     * @param array $ids_list
     * @return array
     */
    public function getRegionsWithInfo(array $ids_list = []): array;

    /**
     * Извлекает из БД информацию по региону. Кроме общих полей загружает и поля контента, переданные вторым параметром
     *
     * @param $id_region
     * @param array $requested_content_fields
     * @return array
     *
     * @throws SyntaxError
     */
    public function getMapRegionData($id_region, array $requested_content_fields = ['title', 'content', 'content_restricted']):array;

    /**
     * Элементарная проверка на допустимость редактирования карты. Вычисляется из админских емейлов и списка емейлов,
     * указанных в конфиге карты как "имеющие права".
     *
     * Легаси вариант, в будущем должен быть заменён на полноценный механизм ACL (через DI)
     *
     * @return bool
     */
    public function simpleCheckCanEdit(): bool;


    /**
     * Временная функция, фильтрующая массив регионов с данными.
     * Фильтр не проходят регионы, имеющие is_excludelists отличный от NEVER
     *
     * @param $regions_list
     * @return array
     */
    public static function removeExcludedFromRegionsList($regions_list): array;

    /**
     * Конвертирует массив ID-шников в строку с запятыми
     *
     * @param $regions_array
     * @return string
     */
    public static function convertRegionsWithInfo_to_IDs_String($regions_array): string;



}