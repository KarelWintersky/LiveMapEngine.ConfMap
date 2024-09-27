<?php

namespace LiveMapEngine\ContentExtra;

interface ContentExtraInterface
{
    /**
     * Рендерит представление экстра-контента для вывода в информацию по региону
     *
     * @param string $source_data
     * @return mixed
     */
    public function renderView(string $source_data):mixed;

    /**
     * Рендерит представление экстра-контента для вывода информации в редактор
     *
     * @param string $source_data
     * @return mixed
     */
    public function renderEdit(string $source_data = ''):mixed;

    /**
     * Из входящего набора данных ($_REQUEST) генерит строку для поля `content_extra`
     *
     * @return string
     */
    public function parseEditData():string;

}