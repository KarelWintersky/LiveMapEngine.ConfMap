<?php

namespace LiveMapEngine\Map;

use Arris\Entity\Result;
use Psr\Log\LoggerInterface;

interface MapMakerInterface
{
    /**
     * @param null $PDO
     * @param string $id_map
     * @param array $options
     * @param LoggerInterface|null $logger
     */
    public function __construct($PDO = null, string $id_map = '', array $options = [], ?LoggerInterface $logger = null);

    /**
     * Загружает JSON5-конфиг из файла в поле $this->mapConfig
     *
     * @param null $explicit_config_filepath
     * @return Result
     */
    public function loadConfig($explicit_config_filepath = null): Result;

    /**
     * Возвращает конфиг карты
     *
     * ИЛИ один из ключей конфига!
     *
     * @param string $path
     * @param string $default
     * @param string $separator
     * @return \stdClass|mixed
     */
    public function getConfig(string $path = '', string $default = '', string $separator = '->'): mixed;

    /**
     * Загружает из БД базовую информацию о регионах на карте для JS-билдера и списков.
     * Сохраняет её в полях экземпляра класса.
     *
     * @return Result
     */
    public function loadMap():Result;

    /**
     * Извлекает из БД информацию по региону. Кроме общих полей загружает и поля контента, переданные вторым параметром.
     *
     * Важно: ТОЛЬКО извлекает данные.
     *
     * @param $id_region
     * @param array $requested_content_fields
     * @return array
     */
    public function getMapRegionData($id_region, array $requested_content_fields = ['title', 'content', 'content_restricted']):array;

    /**
     * Загружает из БД основную информацию по регионам для текущей карты.
     * Передается список ID регионов (на слое) или пусто для всех регионов со всех слоёв.
     *
     * @param array $ids_list
     * @return array
     */
    public function getRegionsWithInfo(array $ids_list = []): array;

    /**
     * ТОЛЬКО сохраняет переданные данные.
     * Обязательные поля: 'id_region', 'id_map', 'edit_date', 'is_publicity', 'is_excludelists'
     * Дополнительные поля: 'title', 'content', 'content_restricted'
     *
     * Проверяется только наличие полей id_map и id_region
     *
     * Проверка права редактирования должна делаться вне
     *
     * @param array $data
     * @return Result
     */
    public function storeMapRegionData(array $data):Result;

    /**
     * Возвращает список ревизий регионов
     *
     * @param $region_id
     * @param int $revisions_depth
     * @return Result
     */
    public function getRegionRevisions($region_id, int $revisions_depth = 0):Result;

    /**
     * Элементарная проверка на допустимость редактирования карты. Вычисляется из админских емейлов и списка емейлов,
     * указанных в конфиге карты как "имеющие права".
     *
     * Легаси вариант, в будущем должен быть заменён на полноценный механизм ACL (через DI)
     *
     * @return bool
     */
    public function simpleCheckCanEdit():bool;

}