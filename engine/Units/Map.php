<?php

namespace Confmap\Units;

use Arris\Database\DBWrapper;
use Arris\Entity\Result;
use Arris\Path;
use ColinODell\Json5\SyntaxError;
use Confmap\ACL;
use Confmap\Exceptions\AccessDeniedException;
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
     * @return \stdClass
     */
    public function getConfig(): \stdClass
    {
        return $this->mapConfig;
    }

    /**
     * Загружает из БД базовую информацию о регионах на карте для JS-билдера и списков
     *
     * @return void
     */
    public function loadMap()
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
    }

    public function getRegionsWithInfo($ids_list = []): array
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
     * @param $id_region
     * @param array $requested_content_fields
     * @return array
     *
     * @throws SyntaxError
     */
    public function getMapRegionData($id_region, array $requested_content_fields = ['title', 'content', 'content_restricted']):array
    {
        $role_can_edit = ACL::simpleCheckCanEdit($this->id_map);

        $common_fields = ['id_region', 'alias_map', 'edit_date', 'is_publicity', 'is_excludelists'];

        $sql_select_fields = implode(', ',  \array_unique(\array_merge($common_fields, $requested_content_fields) ));

        $query = "
            SELECT {$sql_select_fields}
            FROM {$this->dbTables->map_data_regions}
            WHERE
                id_region     = :id_region
            AND alias_map     = :alias_map
            ORDER BY edit_date DESC
            LIMIT 1
            ";

        $sth = $this->pdo->prepare($query);
        $sth->execute([
            'id_region' =>  $id_region,
            'alias_map' =>  $this->id_map
        ]);
        $row = $sth->fetch();

        if ($row) {
            $info = array_merge($row, [
                'is_present'        =>  1,
                'can_edit'          =>  $role_can_edit,
            ]);

            if (!ACL::isRoleGreater('ANYONE', $row['is_publicity'])) {
                foreach ($requested_content_fields as $field) {
                    $info[ $field ] = $row['content_restricted'] ?? "Доступ ограничен";  // "Доступ ограничен" - на самом деле должно быть записано в JSON-конфиге карты/слоя
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

    //@todo: !!!
    // надо учесть, что есть дефолтные обязательные поля и есть кастомные поля контента
    public function storeMapRegionData(string $region_id, array $request):Result
    {
        $result = new Result();

        $role_can_edit = ACL::simpleCheckCanEdit($this->id_map);

        if (false == $role_can_edit) {
            throw new AccessDeniedException("Обновление региона недоступно, недостаточный уровень допуска");
        }

        $query = "
        INSERT INTO {$this->dbTables->map_data_regions}
         (
         id_map, alias_map, edit_whois, edit_ipv4,
         id_region, title, content, content_restricted,
         edit_comment, is_excludelists, is_publicity
         )
         VALUES
         (
         :id_map, :alias_map, :edit_whois, :edit_ipv4,
         :id_region, :title, :content, :content_restricted,
         :edit_comment, :is_excludelists, :is_publicity
         )
        ";
        $data = [
            'id_map'        =>  $request['edit:id:map'],
            'alias_map'     =>  $request['edit:alias:map'],
            'edit_whois'    =>  0,
            'edit_ipv4'     =>  ip2long(\Arris\Helpers\Server::getIP()),
            'id_region'     =>  $request['edit:id:region'],
            'title'         =>  $request['edit:region:title'],
            'content'       =>  $request['edit:region:content'],
            'content_restricted'    =>  $request['edit:region:content_restricted'],
            'edit_comment'  =>  $request['edit:region:comment'],
            'is_excludelists'   =>  $request['edit:is:excludelists'],
            'is_publicity'  =>  $request['edit:is:publicity']
        ];

        $sth = $this->pdo->prepare($query);
        $sth->execute($data);

        $result->success();

        return $result;
    }

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
    public static function removeExcludedFromRegionsList($regions_list)
    {
        return \array_filter($regions_list, function($row) {
            return ($row[ 'is_excludelists' ] === 'N');
        });
    }

    /**
     *
     * @param $regions_array
     * @return string
     */
    public static function convertRegionsWithInfo_to_IDs_String($regions_array)
    {
        return \implode(", ", \array_map( function($item) {
            return "'{$item['id_region']}'";
        }, $regions_array));
    }


    /**
     * Проходит по массиву регионов и видимость региона для текущего пользователя на основе прав доступа к контенту
     *
     * Не реализовано
     *
     * @param $regions_list
     * @param $map_alias
     * @return mixed
     */
    public static function checkRegionsVisibleByCurrentUser($regions_list, $map_alias)
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


}