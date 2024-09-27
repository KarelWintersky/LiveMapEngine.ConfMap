<?php

namespace LiveMapEngine\ContentExtra;

interface ContentExtraInterface
{
    /**
     * Рендерит представление экстра-контента для вывода в информацию по региону
     *
     * @param string $source
     * @return mixed
     */
    public function renderView(string $source_data):mixed;

    public function renderEdit(string $source_data = ''):mixed;

}