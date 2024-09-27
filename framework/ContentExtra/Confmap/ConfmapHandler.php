<?php

namespace LiveMapEngine\ContentExtra\Confmap;

use LiveMapEngine\ContentExtra\ContentExtraInterface;
use LiveMapEngine\DataCollection;

/**
 * Класс для работы с кастомными экстра-данными для проекта ConfMap
 * Перспективная разработка
 */
class ConfmapHandler implements ContentExtraInterface
{
    const JSON_ENCODE_FLAGS = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK | JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR;

    /**
     * Рендерит представление экстра-контента для вывода в информацию по региону
     *
     * @param string $source_data
     * @return mixed
     */
    public function renderView(string $source_data = ''): mixed
    {
        if (empty($source_data)) {
            return $source_data;
        }

        $json = new DataCollection($source_data);
        $json->setSeparator('->');
        $json->parse();

        $pcd_natural = $json->getData("economy->shares->natural", 0, casting: 'int');
        $pcd_financial = $json->getData("economy->shares->financial", 0, casting: 'int');
        $pcd_industrial = $json->getData("economy->shares->industrial", 0, casting: 'int');
        $pcd_social = $json->getData("economy->shares->social", 0, casting: 'int');
        $pcd_sum = $pcd_natural + $pcd_financial + $pcd_industrial + $pcd_social;

        $pie_chart_data = [];
        if ($pcd_sum > 0) {
            // единичка экономики = сектору в 30 градусов
            $pie_chart_data[] = [
                'data'  =>  $pcd_natural * 30,
                'label' =>  round( $pcd_natural / $pcd_sum, 2 )*100 . '%',
                'color' =>  '#77e359',
                'hint'  =>  'Природная'
            ];
            $pie_chart_data[] = [
                'data'  =>  $pcd_financial * 30,
                'label' =>  round( $pcd_financial / $pcd_sum, 2 )*100 . '%',
                'color' =>  '#ff85ef',
                'hint'  =>  'Финансовая'
            ];
            $pie_chart_data[] = [
                'data'  =>  $pcd_industrial * 30,
                'label' =>  round( $pcd_industrial / $pcd_sum, 2 )*100 . '%',
                'color' =>  '#ed8d26',
                'hint'  =>  'Реальная'
            ];
            $pie_chart_data[] = [
                'data'  =>  $pcd_social * 30,
                'label' =>  round( $pcd_social / $pcd_sum, 2 )*100 . '%',
                'color' =>  '#8bd5f7',
                'hint'  =>  'Социальная'
            ];
        }

        // форматируем население (численность, не население!)
        // используем casting как closure
        $population = $json->getData(path: 'population->count', default: 0, casting: function($population) {
            return match (true) {
                $population >= 1000 =>  "~" . number_format($population / 1000, 0, '.', ' ') . ' млрд.',
                $population >= 1    =>  "~" . number_format($population, 0, '.', ' ') . ' млн.',
                $population > 0     =>  "~" . number_format($population * 1000, 0, '.', ' ') . ' тыс.',
                default             =>  0
            };
        });

        $json->setData('population->count', $population);

        // так как передается массив, к его полям ходим в шаблоне через точку: `{$content_extra->pie_chart.full}`
        $json->setData('pie_chart', [
            'present'   =>  $pcd_sum > 0,
            'full'      =>  json_encode($pie_chart_data, JSON_UNESCAPED_UNICODE)
        ]);
        // закончили с данными для круговой диаграммы

        return $json->getData();
    }

    /**
     * Рендерит представление экстра-контента для вывода информации в редактор
     *
     * @param string $source_data
     * @return mixed
     */
    public function renderEdit(string $source_data = ''):mixed
    {
        return json_decode($source_data, true);
    }

    /**
     * Из входящего набора данных ($_REQUEST) генерит строку для поля `content_extra`
     *
     * @return string
     */
    public function parseEditData():string
    {
        $json = [
            // @todo: версия latest ОБРАТНО НЕСОВМЕСТИМЫХ изменений структуры, например переименования полей, задается в формате "ГГГГММДД"
            // Используется (потенциально) нужна для скриптов миграции данных из версии в версию.
            'version'   =>  '20240723',

            'lsi'       =>  [
                'index'     =>  self::json('lsi-index'),
                'type'      =>  self::json('lsi-type'),
                'atmosphere'=>  self::json('lsi-atmosphere'),
                'hydrosphere'   =>  self::json('lsi-hydrosphere'),
                'climate'   =>  self::json('lsi-climate')
            ],
            'history'   =>  [
                'year'  =>  [
                    'found'         =>  self::json('history-year-found'),
                    'colonization'  =>  self::json('history-year-colonization')
                ],
                'text'          =>  self::json('history-text'),
            ],
            'population'=>  [
                'count'     =>  self::floatvalue(self::json('population-count')),
                'ethnic'    =>  self::json('population-ethnic'),
                'features'  =>  self::json('population-features'),
                'religion'  =>  self::json('population-religion')
            ],
            'economy'   =>  [
                'type'      =>  self::json('economy-type'),
                'shares'    =>  [
                    'natural'   =>  self::json('economy-shares-natural'),
                    'financial' =>  self::json('economy-shares-financial'),
                    'industrial'=>  self::json('economy-shares-industrial'),
                    'social'    =>  self::json('economy-shares-social')
                ],
                'assets'    =>  [
                    'natural'   =>  self::json('economy-assets-natural'),
                    'financial' =>  self::json('economy-assets-financial'),
                    'industrial'=>  self::json('economy-assets-industrial'),
                    'social'    =>  self::json('economy-assets-social'),
                    'oldmoney'  =>  self::json('economy-assets-oldmoney')
                ]
            ],
            'trade' =>  [
                'export'    =>  self::json('trade-export'),
                'import'    =>  self::json('trade-import'),
            ],
            'statehood' =>  [
                'ss'            =>  self::json('statehood-ss'),

                'type'          =>  self::json('statehood-type'),
                'dependency'    =>  self::json('statehood-dependency'),
                'radius'        =>  self::json('statehood-radius'),
                'sector'        =>  self::json('statehood-sector'),

                'administration_principle'  => self::json('statehood:administration_principle'),

                'local_governance'  =>  self::json('statehood-local_governance'),
                'terr_guards'   =>  self::json('statehood-terr_guards'),
                'agency'    =>  [
                    'css'       =>  self::json('statehood-agency-css'),
                    'drc'       =>  self::json('statehood-agency-drc'),
                    'psi'       =>  self::json('statehood-agency-psi'),
                    'starfleet' =>  self::json('statehood-agency-starfleet')
                ],
            ],
            'laws'  => [
                'language'          =>  self::json('laws-language'),
                'passport'          =>  self::json('laws-passport'),
                'visa'              =>  self::json('laws-visa'),
                'gun_rights'        =>  self::json('laws-gun_rights'),
                'private_property'  =>  self::json('laws-private_property'),
                'gencard'   =>  [
                    'info'          =>  self::json('laws-gencard-info'),
                    'restrictions'  =>  self::json('laws-gencard-restrictions'),
                ],
            ],
            'culture'   =>  [
                'currency'      =>  self::json('culture-currency'),
                'holydays'      =>  self::json('culture-holydays'),
                'showplaces'    =>  self::json('culture-showplaces')
            ],
            // 'infrastructure'    =>  [], //
            'other'     =>  [
                'unverified'    =>  self::json('other-unverified_data'),
                'local_heroes'  =>  self::json('other-local_heroes'),
                'legacy'        => self::json('other-legacy'),
            ],
            'system_chart'  =>  self::json('system_chart'),
            'tags'          =>  self::json('tags'),

            // но можно и так:
            // хотя не нужно
            // 'tags'          =>  $request->getData('json:tags')
        ];

        // пакуем контент в JSON
        return json_encode($json, self::JSON_ENCODE_FLAGS);
    }

    private static function json(string $field = '', string $prefix = 'json:'): string
    {
        if (empty($field)) {
            return '';
        }
        $rq_field = "{$prefix}{$field}";

        return  array_key_exists($rq_field, $_REQUEST)
            ? $_REQUEST[$rq_field]
            : '';
    }

    /**
     * @param string $val
     * @return float
     */
    private static function floatvalue(string $val): float
    {
        $val = \str_replace(",",".",$val);
        $val = \preg_replace('/\.(?=.*\.)/', '', $val);
        return \floatval($val);
    }
}