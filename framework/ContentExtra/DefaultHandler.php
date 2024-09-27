<?php

namespace LiveMapEngine\ContentExtra;

/**
 * Дефолтные обработчики для поля `content_extra`
 */
class DefaultHandler implements ContentExtraInterface
{

    /**
     * @inheritDoc
     */
    public function renderView(string $source_data): mixed
    {
        return $source_data;
    }

    /**
     * @inheritDoc
     */
    public function renderEdit(string $source_data = ''):mixed
    {
        return $source_data;
    }

    /**
     * @inheritDoc
     */
    public function parseEditData():string
    {
        return $_REQUEST['content_extra'] ?? '';
    }
}