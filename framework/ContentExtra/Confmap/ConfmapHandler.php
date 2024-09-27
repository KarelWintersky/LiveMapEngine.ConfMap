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
}