<?php

namespace Confmap\Units;

use Arris\Core\Dot;
use Arris\Database\DBWrapper;
use Arris\Entity\Result;
use Arris\Exceptions\AppRouterNotFoundException;
use Arris\Path;
use ColinODell\Json5\SyntaxError;
use Confmap\AbstractClass;
use Confmap\ACL;
use Confmap\DBConfigTables;
use Confmap\Exceptions\AccessDeniedException;
use PDO;
use Psr\Log\LoggerInterface;

class Map
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
     * @var array
     */
    public $mapRegionsWithInfo_IDS;

    /**
     * @var array
     */
    public $mapRegionWithInfoOrderByTitle;

    /**
     * @var array
     */
    public $mapRegionWithInfoOrderByDate;

    /**
     * @var array
     */
    public $mapRegionsWithInfo;

    /**
     * @var string
     */
    public $mapViewMode;

    /**
     * @var \stdClass
     */
    public $mapConfig;

    public string $map_alias = '';

    public string $json_config_filename = '';

    /**
     * @var \stdClass
     */
    private \stdClass $dbTables;

    /**
     * @var PDO|DBWrapper
     */
    private mixed $pdo;

    /**
     * @param null $PDO
     * @param string $map_alias
     * @param array $options
     * @param LoggerInterface|null $logger
     */
    public function __construct($PDO = null, string $map_alias = '', array $options = [], LoggerInterface $logger = null)
    {
        $this->map_alias = $map_alias;
        $this->pdo = $PDO;

        $this->dbTables = new \stdClass();

        $this->dbTables->map_data_regions
            = array_key_exists('map_data_regions', $options)
            ? $options['map_data_regions']
            : 'map_data_regions';

        $this->dbTables->users
            = array_key_exists('users', $options)
            ? $options['users']
            : 'users';
    }

    /**
     * Загружает JSON5-конфиг в поле
     * $this->mapConfig
     *
     * @param string|Path $path
     * @param array $config_files, по умолчанию: ['index.json5', 'index.json'], первым рекомендуется json5
     * @return void
     * @throws SyntaxError
     */
    public function loadConfig($path, array $config_files = ['index.json5', 'index.json']): void
    {
        if (empty($config_files)) {
            throw new \RuntimeException("[JS Builder] No config files for map {$this->map_alias} declared");
        }

        if (is_string($path)) {
            $path = Path::create($path);
        }

        foreach ($config_files as $cf) {
            $fn = $path->join($cf)->toString();
            if (is_file($fn)) {
                $this->json_config_filename = $fn;
                break;
            }
        }
        // какой-из следующих трех throw лишний
        if (empty($this->json_config_filename)) {
            throw new AppRouterNotFoundException("[JS Builder] Файл конфигурации карты не задан", 404, null, [
                'method'    =>  'GET',
                'map'       =>  $this->map_alias
            ]);
        }

        if (!is_file($this->json_config_filename)) {
            throw new \RuntimeException( "[JS Builder] {$this->json_config_filename} not found", 2 );
        }

        if (!is_readable($this->json_config_filename)) {
            throw new \RuntimeException("[JS Builder]  {$this->json_config_filename} not readable", 3);
        }

        // файл точно существует

        $json_config_content = \file_get_contents( $this->json_config_filename );

        if (false === $json_config_content) {
            throw new \RuntimeException( "[JS Builder] Can't get content of {$this->json_config_filename} file." );
        }

        $json = json5_decode($json_config_content);

        if (null === $json) {
            throw new \RuntimeException( "[JS Builder] {$this->json_config_filename} json file is invalid", 3 );
        }

        $this->mapConfig = $json;
    }

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

    public function getRegionsWithInfo($ids_list = [])
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
             WHERE alias_map = :alias_map
             {$in_subquery}
             GROUP BY id_region
            ) subQuery
        INNER JOIN {$table_map_data_regions} mdr
        ON 
            mdr.id_region = subQuery.id_region AND 
            mdr.edit_date = subQuery.max_edit_date
        ";

        $sth = $this->pdo->prepare($query);
        $sth->bindValue('alias_map', $this->map_alias, PDO::PARAM_STR);
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
     * @param $requested_content_fields
     * @return array
     *
     * @throws SyntaxError
     */
    public function getMapRegionData($id_region, $requested_content_fields = ['title', 'content']):array
    {
        $role_can_edit = ACL::simpleCheckCanEdit($this->map_alias);

        $common_fields = ['id_region', 'alias_map', 'content_restricted', 'edit_date', 'is_publicity', 'is_excludelists'];

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
            'alias_map' =>  $this->map_alias
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

    public function storeMapRegionData(string $region_id, array $request):Result
    {
        $result = new Result();

        $role_can_edit = ACL::simpleCheckCanEdit($this->map_alias);

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
                'alias_map' =>  $this->map_alias,
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