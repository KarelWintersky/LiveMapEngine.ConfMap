<?php

namespace LiveMapEngine\ContentExtra;

/**
 * Дефолтные обработчики для поля `content_extra`
 */
class DefaultHandler implements ContentExtraInterface
{

    public function renderView(string $source_data): mixed
    {
        return $source_data;
    }

    public function renderEdit(string $source_data = ''):mixed
    {
        return $source_data;
    }
}