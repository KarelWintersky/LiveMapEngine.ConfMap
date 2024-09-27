{*
Код отличается от LiveMap Engine
Код определений filemanager и старта tinyMCE надо скопировать в основной проект
*}
<!DOCTYPE html>
<html lang="ru">
<head>
    <title>Карта {$title_map}, редактирование региона {$id_region}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

    {include file="_common/favicon_defs.tpl"}

    <link rel="stylesheet" type="text/css" href="/frontend/edit.region.css" />

    <script type="text/javascript" src="/frontend/html5shiv.js"></script>
    <script type="text/javascript" src="/frontend/jquery/jquery-3.2.1_min.js"></script>
    <script type="text/javascript" src="/frontend/tinymce-7.2.0/tinymce.min.js"></script>
    <script type="text/javascript" src="/frontend/livemap/EditorConnector.js"></script>
    <script type="text/javascript" id="define">
        window.editor_config = {
            success_edit_timeout: 500,
        };

        let _editRegion = new EditorConnector({ menubar: false });

        let saving_in_progress = false;

        $(document).ready(function() {
            // Инициализируем только основной редактор. Остальные описаны в шаблоне редактирования content_extra
            _editRegion.createInstance('editor_summary', { menubar: true, height: 300 });

            setTimeout(function(){
                document.getElementById('title').focus();
            }, 300);

            $("#actor-back").on('click', function(){
                document.location.href = $(this).data('href');
            });

            // Аякс-обработчик сохранения, спиннер, вывод результата. Не забываем на время обработки дизейблить кнопки, а при ошибке - энэйблить.
            $("#form-edit-region").on('submit', function(){
                let url = $(this).attr('action');

                if (saving_in_progress) {
                    return false;
                }

                $.ajax({
                    async       : false,
                    cache       : false,
                    type        : 'POST',
                    url         : url,
                    dataType    : 'json',
                    data        : $(this).serialize(),
                    beforeSend  : function(){
                        saving_in_progress = true;
                        $("#ajax-process").show();

                        // disable buttons
                        $(".action-submit").prop('disabled', true);
                        $("#actor-back").prop('disabled', true);
                    },
                    success     : function(answer) {
                        if (answer['is_success']) {
                            $("#ajax-result").show();
                            setTimeout(function(){
                                window.location.replace("{$html_callback}")
                            }, window.editor_config.success_edit_timeout);
                        } else {
                            saving_in_progress = false;
                            $("#ajax-result").show().html( answer['message'] );
                            // enable buttons
                            // $("#actor-submit").removeAttr('disabled');
                            $(".action-submit").removeAttr('disabled');
                            $("#actor-back").removeAttr('disabled');
                        }
                    }
                });
                return false;
            });

            let $actor_toggle_extra_content = $('#actor-toggle-extra-content');

            // onload
            if ($actor_toggle_extra_content.is(':checked') == false) {
                $(`#fieldset-extra-content > fieldset`).hide();
            }

            // toggle checkbox
            $actor_toggle_extra_content.on('change', function (){
                let checked = $(this).is(':checked');
                if (!checked) {
                    $(`#fieldset-extra-content > fieldset`).hide();
                } else {
                    $(`#fieldset-extra-content > fieldset`).show();
                }
            });


        }); // jQuery
    </script>

    <script src="/frontend/livemap/RangeInputGroup.js"></script>
    <script src="/frontend/livemap/SelectUpdater.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            new RangeInputGroup('lsi_slider');
            new RangeInputGroup('statehood_ss');

            // устанавливаем значения селектов согласно атрибуту data-selected
            (new SelectUpdater('.action-onload-update-select')).update();
        });
    </script>

    <style>
        fieldset {
            margin-top: 0.7em;
            border: 1px solid lightgray; /* for default fieldset */
        }
        fieldset.common {
            border: 1px solid navy;
        }
        legend {
            color: forestgreen;
            font-weight: normal;
        }
    </style>
</head>
<body>
<small>
    <strong>Важно:</strong> во всех случаях мы отмечаем значимые или известные на уровне Конфедерации явления (экспорт, праздники итд)
</small>

<form action="{Arris\AppRouter::getRouter('update.region.info')}" method="post" id="form-edit-region">
    <input type="hidden" name="edit:id:map"     value="{$id_map}">
    <input type="hidden" name="edit:id:region"  value="{$id_region}">
    <input type="hidden" name="edit:html_callback" value="{$html_callback}" />
    <input type="hidden" name="edit:layout:type" value="svg">

    <fieldset class="common">
        <legend> {if $is_present}Название объекта:{else}Это новый регион с ID {$id_region}, нужно задать ему имя:{/if} </legend>
        <label for="title">
            <input type="text" name="edit:region:title" id="title" size="90" value="{$content_title}" required style="font-size: x-large; color: blue"/>
        </label>
        <button type="submit" class="action-submit" style="float: right; font-size: x-large">Сохранить</button>
        <br><br>
        <label for="editor_summary" class="label_textarea label_fullwidth">
            <textarea name="edit:region:content" id="editor_summary" cols="10" data-height="100">{$content}</textarea>
        </label>
    </fieldset>

    <fieldset class="common" id="fieldset-extra-content">
        <legend>
            <label style="user-select: none; padding-right: initial">
                <input id="actor-toggle-extra-content" type="checkbox" name="is_display_extra_content" {if $is_display_extra_content}checked{/if}>
                Показывать расширенную информацию?&nbsp;
            </label>
        </legend>

        {include file="edit.region/_edit.region_extra.tpl"}
{*
Но каким образом указать кастомный экстра-шаблон?
Самый простой вариант - при установке пакета копировать шаблоны в
- templates/
-- _content_extra/XXX/view.region_extra.tpl
-- _content_extra/XXX/edit.region_extra.tpl
И передавать путь соотв. шаблону через переменную: {include file=$content_extra_template}
Но...
*}
    </fieldset>

    <fieldset class="common">
        <legend>Content Restrictions + Editorial Notes:</legend>
        <table width="90%" style="text-align: left" border="0">
            <tr>
                <td>
                    <label for="edit-restricted">Access Denied Message:
                        <input type="text" name="edit:region:content_restricted" size="70%" value="{$content_restricted}" id="edit-restricted" autocomplete="off"/>
                    </label>
                </td>
                <td>
                    <label>
                        В списках:
                        <select name="edit:is:excludelists">
                            <option value="N"{if $is_exludelists eq "N"} selected{/if}>Во всех</option>
                            <option value="F"{if $is_exludelists eq "F"} selected{/if}>Только слоя</option>
                            <option value="A"{if $is_exludelists eq "A"} selected{/if}>Нигде</option>
                        </select>
                    </label>
                </td>
                <td>
                    <label>
                        Видимость:
                        <select name="edit:is:publicity">
                            <option value="ANYONE"{if $is_publicity eq "ANYONE"} selected{/if}>Всем</option>
                            <option value="VISITOR"{if $is_publicity eq "VISITOR"} selected{/if}>Участникам</option>
                            <option value="EDITOR"{if $is_publicity eq "EDITOR"} selected{/if}>Редакторам</option>
                            <option value="OWNER"{if $is_publicity eq "OWNER"} selected{/if}>Владельцу</option>
                        </select>
                    </label>
                </td>
            </tr>
        </table>
        <br>
        <div class="field">
            <label for="edit-reason">Комментарий редактора:
                <input type="text" name="edit:region:comment" size="90" value="" id="edit-reason" autocomplete="off"/> <br><br>
            </label>
        </div>
    </fieldset>

    {*<fieldset class="common">
        <legend>Техническое</legend>
        <div class="field">
            <label for="edit-reason">Комментарий редактора:
                <input type="text" name="edit:region:comment" size="90" value="" id="edit-reason" autocomplete="off"/> <br><br>
            </label>
        </div>
    </fieldset>*}
    <div class="clear"></div>

    <fieldset style="border: none">
        <div class="label_fullwidth">
            <button type="button" id="actor-back" style="float:left; color: darkred" data-href="{Arris\AppRouter::getRouter('view.frontpage')}">Назад на карту</button>

            <span id="ajax-process" style="display: none">
                Сохраняю... &nbsp;
                <img src="/frontend/images/spinner_saving.svg" height="18" alt="ready"/>
            </span>
            <span id="ajax-result" style="display: none;">Сохранение успешно! Через несколько секунд возвращаемся на карту.</span>
            <button type="submit" class="action-submit" style="color: forestgreen; float: right">Сохранить</button>
        </div>
    </fieldset>
</form>
{if !empty($region_revisions)}
    <fieldset id="revisions_fieldset">
        <ul>
            {foreach $region_revisions as $region}
                <li>
                    {$region.edit_name} edited `{$region.title}` at {$region.edit_date} from IP {$region.ipv4}
                    {if !empty($region.edit_comment)}
                        <small>{$region.edit_comment}</small>
                    {/if}
                </li>
            {/foreach}
        </ul>
    </fieldset>
{/if}
<div class="clear"></div>
<hr>
<small style="float: left">Logged as <strong>{$is_logged_user}</strong> from <strong>{$is_logged_user_ip}</strong></small>
<small style="float: right"><em>{$copyright}</em></small>
</body>

</html>
