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

    /**
     * Возвращает имя кастомного шаблона для просмотра content_extra
     * НЕ ИСПОЛЬЗУЕТСЯ, ДОБАВЛЕНО НА ПЕРСПЕКТИВУ ЕСЛИ ИДЕЯ С ПАКЕТИРОВАНИЕМ TPL-шаблона будет принята в работу
     *
     * @return string
     */
    public function getTemplateFileView():string
    {
        return 'view.region/view.region_extra.tpl';
    }

    /**
     * Возвращает имя кастомного шаблона для редактирования content_extra
     * НЕ ИСПОЛЬЗУЕТСЯ, ДОБАВЛЕНО НА ПЕРСПЕКТИВУ ЕСЛИ ИДЕЯ С ПАКЕТИРОВАНИЕМ TPL-шаблона будет принята в работу
     *
     * @return string
     */
    public function getTemplateFileEdit():string
    {
        return 'edit.region/edit.region_extra.tpl';
    }
}