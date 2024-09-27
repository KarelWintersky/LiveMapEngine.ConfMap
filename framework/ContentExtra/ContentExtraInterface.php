<?php

namespace LiveMapEngine\ContentExtra;

/*

Но каким образом указать кастомный экстра-шаблон в шаблонах просмотра/редактирования?

Самый простой вариант - при установке пакета копировать шаблоны в
- templates/
-- _content_extra/XXX/view.region_extra.tpl
-- _content_extra/XXX/edit.region_extra.tpl
И передавать путь соотв. шаблону через переменную: {include file=$content_extra_template}
Но...
 */

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

    /**
     * Возвращает имя кастомного шаблона для просмотра content_extra
     * НЕ ИСПОЛЬЗУЕТСЯ, ДОБАВЛЕНО НА ПЕРСПЕКТИВУ ЕСЛИ ИДЕЯ С ПАКЕТИРОВАНИЕМ TPL-шаблона будет принята в работу
     *
     * @return string
     */
    public function getTemplateFileView():string;

    /**
     * Возвращает имя кастомного шаблона для редактирования content_extra
     * НЕ ИСПОЛЬЗУЕТСЯ, ДОБАВЛЕНО НА ПЕРСПЕКТИВУ ЕСЛИ ИДЕЯ С ПАКЕТИРОВАНИЕМ TPL-шаблона будет принята в работу
     *
     * @return string
     */
    public function getTemplateFileEdit():string;

}

