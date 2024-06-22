<?php

namespace Confmap\Units;

use Arris\Database\DBWrapper;
use Arris\Entity\Result;
use Arris\Path;
use ColinODell\Json5\SyntaxError;
use PDO;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Map implements MapInterface
{
    const allowed_cursors = [
        'auto', 'default', 'none', 'context-menu', 'help', 'pointer', 'progress', 'wait', 'cell', 'crosshair',
        'text', 'vertical-text', 'alias', 'copy', 'move', 'no-drop', 'not-allowed', 'all-scroll', 'col-resize',
        'row-resize', 'n-resize', 's-resize', 'e-resize', 'w-resize', 'ns-resize', 'ew-resize', 'ne-resize',
        'nw-resize', 'se-resize', 'sw-resize', 'nesw-resize', 'nwse-resize'
    ];

    const valid_view_modes = [
        'colorbox', 'tabled:colorbox',
        'folio',
        'iframe', 'iframe:colorbox',
        'wide:infobox>regionbox', 'wide:regionbox>infobox',
        'infobox>regionbox', 'regionbox>infobox'
    ];

    const regions_common_fields = ['id_map', 'id_region', 'edit_date', 'is_publicity', 'is_excludelists'];

    /**
     * imploded-строка айдишников регионов с информацией
     *
     * @var string
     */
    public string $mapRegionsWithInfo_IDS;

    /**
     * @var array
     */
    public array $mapRegionWithInfoOrderByTitle;

    /**
     * @var array
     */
    public array $mapRegionWithInfoOrderByDate;

    /**
     * @var array
     */
    public array $mapRegionsWithInfo;

    /**
     * @var string
     */
    public string $mapViewMode;

    /**
     * @var \stdClass
     */
    public \stdClass $mapConfig;

    /**
     * @var string
     */
    public string $id_map = '';

    /**
     * @var string
     */
    public string $json_config_filename = '';

    /**
     * @var \stdClass
     */
    private \stdClass $dbTables;

    /**
     * DB Connector
     *
     * @var PDO|DBWrapper
     */
    private mixed $pdo;

    /**
     * Состояние обработки
     *
     * @var Result
     */
    public Result $state;

    /**
     * Logger
     *
     * @var LoggerInterface|NullLogger
     */
    private LoggerInterface|NullLogger $logger;

    /**
     * Путь к конфигам карты
     *
     * @var string|Path
     */
    private mixed $config_path;

    /**
     * Файлы конфига
     *
     * @var string[]
     */
    private array $config_files;

    /**
     * @param null $PDO
     * @param string $id_map
     * @param array $options
     * @param LoggerInterface|null $logger
     */
    public function __construct($PDO = null, string $id_map = '', array $options = [], ?LoggerInterface $logger = null)
    {
        $this->id_map = $id_map;
        $this->pdo = $PDO;

        $this->logger = is_null($logger) ? new NullLogger() : $logger;

        $this->dbTables = new \stdClass();

        $this->state = new Result();

        $this->dbTables->map_data_regions
            = array_key_exists('map_data_regions', $options)
            ? $options['map_data_regions']
            : 'map_data_regions';

        $this->dbTables->users
            = array_key_exists('users', $options)
            ? $options['users']
            : 'users';

        $this->config_path
            = array_key_exists('config_path', $options)
            ? $options['config_path']
            : '';

        $this->config_files
            = array_key_exists('config_files', $options)
            ? $options['config_files']
            : ['index.json5', 'index.json'];
    }

    /**
     * Загружает JSON5-конфиг в поле $this->mapConfig
     *
     * @param null $path
     * @return Result
     * @throws SyntaxError
     */
    public function loadConfig($path = null): Result
    {
        if (empty($this->config_path) && empty($path)) {
            $this->state->error("Config path not defined");
            return $this->state;
        }

        $path = $path ?: $this->config_path;

        if (empty($this->config_files)) {
            $this->state->error("[JS Builder] No config files for map {$this->id_map} declared");
            return $this->state;
        }

        if (is_string($path)) {
            $path = Path::create($path);
        }

        foreach ($this->config_files as $cf) {
            $fn = $path->join($cf)->toString();
            if (is_file($fn) && is_readable($fn)) {
                $this->json_config_filename = $fn;
                break;
            }
        }
        // какой-из следующих трех throw лишний
        if (empty($this->json_config_filename)) {
            $this->state->error("Файл конфигурации не задан для карты {$this->id_map}");
            return $this->state;
        }

        $json_config_content = \file_get_contents( $this->json_config_filename );

        if (false === $json_config_content) {
            $this->state->error("Ошибка чтения файла {$this->json_config_filename} для карты {$this->id_map}");
            return $this->state;
        }

        $json = json5_decode($json_config_content);

        if (null === $json) {
            $this->state->error("{$this->json_config_filename} json file is invalid");
            return $this->state;
        }

        $this->mapConfig = $json;

        return $this->state;
    }

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
    public function getConfig($path = '', $default = '', $separator = '->'): mixed
    {
        if (!empty($path)) {
            if (property_exists_recursive($this->mapConfig, $path, $separator)) {
                return property_get_recursive($this->mapConfig, $path, $separator, $default);
            } else {
                return $default;
            }
        }
        return $this->mapConfig;
    }

    /**
     * Загружает из БД базовую информацию о регионах на карте для JS-билдера и списков.
     * Сохраняет её в полях экземпляра класса.
     *
     * @return Result
     */
    public function loadMap():Result
    {
        $viewmode = 'folio';

        if (!empty($this->mapConfig->display->viewmode)) {
            $viewmode = $this->mapConfig->display->viewmode;
        }

        $viewmode = filter_array_for_allowed($_GET, 'viewmode', self::valid_view_modes, $viewmode);
        $viewmode = filter_array_for_allowed($_GET, 'view',     self::valid_view_modes, $viewmode);

        $this->mapViewMode = $viewmode;

        // извлекаем все регионы с информацией
        $this->mapRegionsWithInfo = $this->getRegionsWithInfo([]);

        // фильтруем по доступности пользователю (is_publicity)
        //@todo
        // $this->mapRegionsWithInfo = Map::checkRegionsVisibleByCurrentUser($this->mapRegionsWithInfo, $map_alias);

        // фильтруем по visibility ????
        $this->mapRegionsWithInfo = self::removeExcludedFromRegionsList($this->mapRegionsWithInfo);

        $this->mapRegionsWithInfo_IDS = self::convertRegionsWithInfo_to_IDs_String($this->mapRegionsWithInfo);

        $this->mapRegionWithInfoOrderByTitle = $this->mapRegionsWithInfo;
        \usort($this->mapRegionWithInfoOrderByTitle, static function($value1, $value2){
            return ($value1['title'] <=> $value2['title']);
        });

        $this->mapRegionWithInfoOrderByDate = $this->mapRegionsWithInfo;
        \usort($this->mapRegionWithInfoOrderByDate, static function($value1, $value2){
            return ($value1['edit_date'] <=> $value2['edit_date']);
        });

        return $this->state;
    }

    /**
     * Загружает из БД основную информацию по регионам для текущей карты.
     * Передается список ID регионов (на слое) или пусто для всех регионов со всех слоёв.
     *
     * @param array $ids_list
     * @return array
     */
    public function getRegionsWithInfo(array $ids_list = []): array
    {
        $table_map_data_regions = $this->dbTables->map_data_regions;

        if (!empty($ids_list)) {
            $paths_at_layers_ids = \implode(", ", \array_map( static function($item){
                return "'{$item}'";
            }, \array_keys($ids_list)));
            $in_subquery = " AND id_region IN ({$paths_at_layers_ids}) ";
        } else {
            $in_subquery = "";
        }

        // на самом деле на данном этапе нам не нужна сортировка по last date, мы же извлекаем регионы с информацией ВООБЩЕ
        // нет, это не так. Нужна, потому что у регионов могут различаться title , а нам нужно актуальное значение!!!

        // по идее это минимальная информация о регионах, исключая контент
        $query = "
        SELECT
            mdr.id_region, mdr.title, mdr.edit_date,
            mdr.is_publicity, mdr.is_excludelists,

            mdr.region_stroke AS stroke,
            mdr.region_border_color AS borderColor,
            mdr.region_border_width AS borderWidth,
            mdr.region_border_opacity AS borderOpacity,
            
            mdr.region_fill AS fill,
            mdr.region_fill_color AS fillColor,
            mdr.region_fill_opacity AS fillOpacity
        FROM
           ( SELECT id_region, max(edit_date) as max_edit_date
             FROM {$table_map_data_regions} 
             WHERE id_map = :id_map
             {$in_subquery}
             GROUP BY id_region
            ) subQuery
        INNER JOIN {$table_map_data_regions} mdr
        ON 
            mdr.id_region = subQuery.id_region AND 
            mdr.edit_date = subQuery.max_edit_date
        ";

        $sth = $this->pdo->prepare($query);
        $sth->bindValue('id_map', $this->id_map, PDO::PARAM_STR);
        $sth->execute();

        $all_regions = [];

        //@todo: HINT (преобразование PDO->fetchAll() в асс.массив, где индекс - значение определенного столбца каждой строки)
        \array_map( static function($row) use (&$all_regions) {
            $all_regions[ $row['id_region'] ] = $row;
        }, $sth->fetchAll());

        /*
        В оригинале этот код закомментирован. Вероятно, он реализован иначе

        $current_role = $this->ACL_getRole($map_alias);
        array_map(function($row) use (&$all_regions, $current_role) {
            // проверка прав: может ли текущий пользователь иметь инфу по этому региону?

            if ($this->ACL_isValidRole($current_role, $row['is_publicity'])) {
                $all_regions[ $row['id_region'] ] = $row;
            }

            $all_regions[ $row['id_region'] ] = $row;

        }, $sth->fetchAll());*/


        return $all_regions;
    }



    /**
     * Извлекает из БД информацию по региону. Кроме общих полей загружает и поля контента, переданные вторым параметром.
     *
     * Важно: ТОЛЬКО извлекает данные.
     *
     * @param $id_region
     * @param array $requested_content_fields
     * @return array
     */
    public function getMapRegionData($id_region, array $requested_content_fields = ['title', 'content', 'content_restricted']):array
    {
        $role_can_edit = $this->simpleCheckCanEdit();

        $common_fields = self::regions_common_fields;

        $sql_select_fields = \implode(', ',  \array_unique(\array_merge($common_fields, $requested_content_fields) ));

        $query = "
            SELECT {$sql_select_fields}
            FROM {$this->dbTables->map_data_regions}
            WHERE
                id_region     = :id_region
            AND id_map        = :id_map
            ORDER BY edit_date DESC
            LIMIT 1
            ";

        $sth = $this->pdo->prepare($query);
        $sth->execute([
            'id_region' =>  $id_region,
            'id_map'    =>  $this->id_map
        ]);
        $row = $sth->fetch();

        if ($row) {
            $info = \array_merge($row, [
                'is_present'        =>  1,
                'can_edit'          =>  $role_can_edit,
            ]);

            // Делает "доступ ограничен" для всех, кому не хватает права доступа на просмотр контента
            if (!self::isRoleGreater('ANYONE', $row['is_publicity'])) {
                foreach ($requested_content_fields as $field) {
                    /*
                     * Проблема: а как узнать, к какому слою относится регион? Если бы мы хранили регионы в БД - то можно было бы...
                     */
                    $info[ $field ] = $row['content_restricted'] ?: "Доступ ограничен";  // "Доступ ограничен" - на самом деле должно быть записано в JSON-конфиге карты/слоя
                }
            }

        } else {
            $info = [
                'is_present'    =>  0,
                'title'         =>  $id_region,
                'can_edit'      =>  $role_can_edit
            ];
            foreach ($requested_content_fields as $field) {
                $info[ $field ] = '';
            }
        }

        return $info;
    }

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
    public function storeMapRegionData(array $data):Result
    {
        if (!\array_key_exists("id_map", $data)) {
            $this->state->error(__METHOD__ . " Field ID_MAP not found in given dataset");
            return $this->state;
        }

        if (!\array_key_exists("id_region", $data)) {
            $this->state->error(__METHOD__ . " Field ID_REGION not found in given dataset");
            return $this->state;
        }

        $fields = [];
        $fields_p = [];
        foreach ($data as $i => $value) {
            $fields[] = $i;
            $fields_p[] = ':' . $i;
        }

        $query_set = [
            "INSERT INTO {$this->dbTables->map_data_regions}",
            "(",
            \implode(", ", $fields),
            ")",
            "VALUES",
            "(",
            \implode(", ", $fields_p),
            ")"
        ];

        $query = implode(" ", $query_set);

        $sth = $this->pdo->prepare($query);
        $result = $sth->execute($data);

        return (
            new Result($result))
            /*->set("sql_query", $query)
            ->set("sql_data", $data)*/
            ;
    }

    /**
     * @todo
     *
     * @param $region_id
     * @param int $revisions_depth
     * @return array|false
     */
    public function getRegionRevisions($region_id, int $revisions_depth = 0)
    {
        $query_limit = ($revisions_depth !== 0) ? " LIMIT {$revisions_depth} " : "";

        $query = "
SELECT
  table_data.id_region AS id_region,
  table_data.edit_date AS edit_date,
  table_users.username AS edit_name,
  INET_NTOA(edit_ipv4) AS ipv4,
  table_data.title AS title,
  table_data.edit_comment AS edit_comment
FROM
  {$this->dbTables->map_data_regions} AS table_data,
  {$this->dbTables->users} AS table_users
WHERE
    alias_map = :alias_map
AND table_data.id_region = :id_region
AND table_data.edit_whois = table_users.id
ORDER BY edit_date {$query_limit};
        ";

        try {
            $sth = $this->pdo->prepare($query);

            $sth->execute([
                'alias_map' =>  $this->id_map,
                'id_region' =>  $region_id
            ]);

            $all_revisions = $sth->fetchAll();
        } catch (\Exception | \PDOException $e) {
            $this->logger->debug(__METHOD__ . " reports : " . $e->getMessage());
            $all_revisions = FALSE;
        }

        return $all_revisions;
    }






    /**
     * Временная функция, фильтрующая массив регионов с данными.
     * Фильтр не проходят регионы, имеющие is_excludelists отличный от NEVER
     *
     * @param $regions_list
     * @return array
     */
    public static function removeExcludedFromRegionsList($regions_list): array
    {
        return \array_filter($regions_list, function($row) {
            return ($row[ 'is_excludelists' ] === 'N');
        });
    }

    /**
     * Конвертирует массив ID-шников в строку с запятыми
     *
     * @param $regions_array
     * @return string
     */
    public static function convertRegionsWithInfo_to_IDs_String($regions_array): string
    {
        return \implode(", ", \array_map( function($item) {
            return "'{$item['id_region']}'";
        }, $regions_array));
    }


    /**
     * Проходит по массиву регионов и фильтрует регионы на основе видимости для текущего пользователя на основе прав доступа к контенту
     *
     * Не реализовано
     *
     * @param array $regions_list
     * @return mixed
     */
    public static function checkRegionsVisibleByCurrentUser(array $regions_list):array
    {
        /*$user_id = Auth::getCurrentUser();
        $user_id
            = $user_id
            ? $user_id['uid']
            : ACL::USERID_SUPERADMIN;

        $current_role = ACL::getRole($user_id, $map_alias);

        return array_filter($regions_list, static function ($row) use ($current_role){
            return (bool)ACL::isValidRole( $current_role, $row[ 'is_publicity' ] );
        });*/
        return $regions_list;
    }

    /**
     *
     * @param string $role
     * @param string $is_publicity
     * @return bool
     */
    public static function isRoleGreater(string $role = 'ANYONE', string $is_publicity = 'ANYONE'): bool
    {
        $MAP_ROLE_TO_INT = [
            'ANYONE'    =>  0,
            'VISITOR'   =>  16,
            'EDITOR'    =>  256,
            'OWNER'     =>  1024,
            'ROOT'      =>  16384
        ];

        $MAP_INT_TO_ROLE = [
            0       =>  'ANYONE',
            16      =>  'VISITOR',
            256     =>  'EDITOR',
            1024    =>  'OWNER',
            16384   =>  'ROOT'
        ];

        return $MAP_ROLE_TO_INT[ $role ] >= $MAP_ROLE_TO_INT[ $is_publicity ];
    }

    /**
     * Элементарная проверка на допустимость редактирования карты. Вычисляется из админских емейлов и списка емейлов,
     * указанных в конфиге карты как "имеющие права".
     *
     * Легаси вариант, в будущем должен быть заменён на полноценный механизм ACL (через DI)
     *
     * @return bool
     */
    public function simpleCheckCanEdit():bool
    {
        $admin_emails = getenv('AUTH.ADMIN_EMAILS') ? explode(' ', getenv('AUTH.ADMIN_EMAILS')) : [];

        $allowed_editors = array_merge($this->mapConfig->can_edit ?? [], $admin_emails);

        return !is_null(config('auth.email')) && in_array(config('auth.email'), $allowed_editors);
    }


}