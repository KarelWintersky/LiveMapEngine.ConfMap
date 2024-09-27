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
    public function render(string $source_data):mixed;

}