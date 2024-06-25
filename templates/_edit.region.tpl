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
    <script type="text/javascript" src="/frontend/tinymce/tinymce.min.js"></script>
    <script type="text/javascript" id="define">
        window.editor_config = {
            success_edit_timeout: 1000
        };
        const tiny_config = {
            theme: "modern",
            skin: "lightgray",
            language: 'ru',

            forced_root_block: "",
            force_br_newlines: true,
            force_p_newlines: false,

            plugins: ["advlist lists autolink link image anchor responsivefilemanager charmap insertdatetime paste searchreplace contextmenu code textcolor template hr pagebreak table print preview wordcount visualblocks visualchars legacyoutput"],
            formats: {
                strikethrough: {
                    inline: 'del'
                },
                underline: {
                    inline: 'span',
                    'classes': 'underline',
                    exact: true
                }
            },

            insertdatetime_formats: [
                "%d.%m.%Y", "%H:%m", "%d/%m/%Y"
            ],

            // menubar: 'file edit insert view format table tools',
            menubar: false,
            contextmenu: "link image responsivefilemanager | inserttable cell row column deletetable | charmap",

            // toolbar1: "undo redo | bold italic underline subscript superscript strikethrough | fontsizeselect styleselect | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist | ",
            // toolbar2: "responsivefilemanager image | template table charmap | link unlink anchor | pastetext removeformat | preview",
            toolbar1: "bold italic underline strikethrough | fontsizeselect | bullist numlist | responsivefilemanager | image charmap | link unlink anchor | | pastetext removeformat | preview",

            // charmap https://www.tinymce.com/docs/plugins/charmap/
            // https://stackoverflow.com/a/22156412/5127037
            charmap_append: [
                ["0x27f7", 'LONG LEFT RIGHT ARROW'],
                ["0x27fa", 'LONG LEFT RIGHT DOUBLE ARROW'],
                ["0x2600", 'sun'],
                ["0x2601", 'cloud']
            ],

            // responsive filemanager
            relative_urls: false,
            document_base_url: "/",
            external_filemanager_path: "/frontend/filemanager/",

            // Кастомные настройки для размера и заголовка окна плагина responsive filemanager
            filemanager_title: "Responsive Filemanager",
            filemanager_width: 980,
            filemanager_height: window.innerHeight - 200,

            /*external_plugins: {
                // не требуется, потому что грузится и так, но требует файла plugin.min.js по тому же пути. Следует это помнить!
                "filemanager": "/frontend/tinymce/plugins/responsivefilemanager/plugin.js"
            },*/

            paste_as_text: true,
            height: 300,
        };

        // add markdown and simple configs

        /**
         * @todo: код перенести в LiveMapEngine
         *
         * @param config
         * @param target
         * @param is_active
         * @param options
         */
        function start_tinymce_instance(config, target, options = { }, is_active = true)         {
            tinyMCE.settings = config;

            let $target = $('#' + target);
            let action = (typeof is_active != 'undefined') ? is_active : true;

             // custom height
            let height = $target.data('height') || tinyMCE.settings.height || 300;
            tinyMCE.settings.height = height;

            //@todo: передавать значение toolbar через data-атрибут?
            /*let toolbar = options.hasOwnProperty('toolbar') ? options.toolbar : false;
            if (toolbar) {
                tinyMCE.settings.toolbar1 = toolbar;
            } else {
                tinyMCE.settings.toolbar1 = "undo redo | bold italic underline subscript superscript strikethrough | fontsizeselect styleselect | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist | ";
                tinyMCE.settings.toolbar2 = "responsivefilemanager image | template table charmap | link unlink anchor | pastetext removeformat | preview";
            }*/

            // как бы работает, НО - system-wide, на все инстансы tinyMCE. А как сделать для каждого инстанса редактора свое меню и настройки?
            /*let menubar = $target.data('menubar') ? $target.data('menubar') : ( options.hasOwnProperty('menubar') ? options.menubar : 'file edit insert view format table tools' );
            if (menubar) {
                tinyMCE.settings.menu = menubar;
            } else {
                tinyMCE.settings.menu = false;
            }*/

            action  ? tinyMCE.execCommand('mceAddEditor', true, target)
                    : tinyMCE.execCommand('mceRemoveEditor', false, target);
            /*tinymce.init({
                selector: target,
                inline: true,
                menubar: menubar,
                toolbar: toolbar,
                height: height
            });*/
        }

        let saving_in_progress = false;

        $(document).ready(function(){
            start_tinymce_instance(tiny_config, 'editor_summary', {
                toolbar: "bold italic underline strikethrough | fontsizeselect | responsivefilemanager | image charmap | link unlink anchor | | pastetext removeformat | preview"
            });
            start_tinymce_instance(tiny_config, 'editor_history', {
                toolbar: "bold italic underline strikethrough | fontsizeselect | responsivefilemanager | image charmap | link unlink anchor | | pastetext removeformat | preview"
            });
            start_tinymce_instance(tiny_config, 'editor_trade_export');
            start_tinymce_instance(tiny_config, 'editor_trade_import');

            start_tinymce_instance(tiny_config, 'editor_assets_natural');
            start_tinymce_instance(tiny_config, 'editor_assets_financial');
            start_tinymce_instance(tiny_config, 'editor_assets_industrial');
            start_tinymce_instance(tiny_config, 'editor_assets_social');
            start_tinymce_instance(tiny_config, 'editor_assets_oldmoney');

            start_tinymce_instance(tiny_config, 'editor_other_local_heroes');
            start_tinymce_instance(tiny_config, 'editor_legacy_description');


            setTimeout(function(){
                $('input#title').focus()
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
                        $("#actor-submit").prop('disabled', true);
                        $("#actor-back").prop('disabled', true);
                    },
                    success     : function(answer) {
                        console.log(answer);

                        if (answer['is_success']) {
                            $("#ajax-result").show();
                            setTimeout(function(){
                                window.location.replace("{$html_callback}")
                            }, window.editor_config.success_edit_timeout);
                        } else {
                            saving_in_progress = false;
                            $("#ajax-result").show().html( answer['message'] );
                            // enable buttons
                            $("#actor-submit").removeAttr('disabled');
                            $("#actor-back").removeAttr('disabled');
                        }
                    }
                });
                return false;
            });

        });
    </script>
</head>
<body>
<form action="{Arris\AppRouter::getRouter('update.region.info')}" method="post" id="form-edit-region">
    <input type="hidden" name="edit:id:map"     value="{$id_map}">
    <input type="hidden" name="edit:id:region"  value="{$id_region}">
    <input type="hidden" name="edit:html_callback" value="{$html_callback}" />
    <input type="hidden" name="edit:layout:type" value="svg">

    <fieldset>
        <legend> {if $is_present}Название объекта:{else}Это новый регион с ID {$id_region}, нужно задать ему имя:{/if} </legend>
        <label for="title">
            <input type="text" name="edit:region:title" id="title" size="90" value="{$content_title}" tabindex="1" required style="font-size: x-large; color: blue"/>
        </label>
        <br><br>
        <label for="editor_summary" class="label_textarea label_fullwidth">
            <textarea name="edit:region:content" id="editor_summary" cols="10" tabindex="4" data-height="100">{$content}</textarea>
        </label>
    </fieldset>

    {* ======================================================================================= *}
    {* Теперь попробуем сделать множественные кастомные поля и хранение в JSON *}
    {* ======================================================================================= *}

    <fieldset>
        <legend>Индекс жизнеобеспечения (Life Support Index)</legend>
        <label>
            КЖП: <input type="text" name="json:lsi-index" size="10" placeholder="0..12" value="{$json.lsi.index|default:'0'}">
        </label>
        <label>
            Тип экосферы: <input type="text" name="json:lsi-type" size="40" placeholder="Тип планеты, тип атмосферы и аквасферы" value="{$json.lsi.type|default:''}">
        </label>
        <label>
            Климат заселенных регионов: <input type="text" name="json:lsi-climate" size="40" placeholder="Климат заселенных регионов" value="{$json.lsi.climate|default:''}">
        </label>
    </fieldset>

    <fieldset>
        <legend>История:</legend>
        <label>
            Год колонизации: <input type="text" name="json:history-year" value="{$json.history.year|default:0}">
        </label>
        <label class="label_textarea label_fullwidth">
            Краткая история колонизации:
            <textarea name="json:history-text" id="editor_history" data-height="100" data-menubar="">{$json.history.text|default:''}</textarea>
        </label>
    </fieldset>

    <fieldset>
        <legend>Население</legend>
        <table width="100%" class="aligned-center">
            <tr>
                <td>Численность (млн)</td>
                <td>
                    Национальный состав:
                </td>
                <td>
                    Этнопсихологические особенности:
                </td>
            </tr>
            <tr>
                <td>
                    <input type="text" name="json:population-count" size="10" value="{$json.population.count|default:0}">
                </td>
                <td>
                    <textarea cols="40" rows="5" name="json:population-ethnic">{$json.population.ethnic|default:''}</textarea>
                </td>
                <td>
                    <textarea cols="40" rows="5" name="json:population-features">{$json.population.features|default:''}</textarea>
                </td>
            </tr>
        </table>
    </fieldset>

    <fieldset>
        <legend>Экономика</legend>
        <label>
            Тип экономики:  <input type="text" name="json:economy-type" size="70" value="{$json.economy.type|default:''}">
        </label>
        <br>
        <hr width="40%">
        <table width="100%" class="aligned-center">
            <tr>
                <td colspan="4">Доли капитала (?/12)</td>
            </tr>
            <tr>
                <td>
                    Природный
                </td>
                <td>
                    Финансовый
                </td>
                <td>
                    Реальный <br>
                    <small>(промышленный)</small>
                </td>
                <td>
                    Социальный
                </td>
            </tr>
            <tr>
                <td>
                    <input type="text" name="json:economy-shares-natural" size="10" value="{$json.economy.shares.natural|default:''}">
                </td>
                <td>
                    <input type="text" name="json:economy-shares-financial" size="10" value="{$json.economy.shares.financial|default:''}">
                </td>
                <td>
                    <input type="text" name="json:economy-shares-industrial" size="10" value="{$json.economy.shares.industrial|default:''}">
                </td>
                <td>
                    <input type="text" name="json:economy-shares-social" size="10" value="{$json.economy.shares.social|default:''}">
                </td>
            </tr>
        </table>
    </fieldset>
    <fieldset>
        <legend>Торговля (статьи экспорта и импорта)</legend>
        <table>
            <tr>
                <td>Статьи экспорта:</td>
                <td>Статьи импорта:</td>
            </tr>
            <tr>
                <td>
                    <textarea name="json:trade-export" id="editor_trade_export" data-height="100" data-menubar="">{$json.trade.export|default:''}</textarea>
                </td>
                <td>
                    <textarea name="json:trade-import" id="editor_trade_import" data-height="100" data-menubar="">{$json.trade.import|default:''}</textarea>
                </td>
            </tr>
        </table>
    </fieldset>
    <fieldset>
        <legend>Крупные представители капитала:</legend>
        <table>
            <tr>
                <td>Природный капитал: </td>
                <td>
                    <textarea name="json:economy-assets-natural" id="editor_assets_natural" data-height="100" data-menubar="">{$json.economy.assets.natural|default:''}</textarea>
                </td>
            </tr>
            <tr>
                <td>Финансовый капитал: </td>
                <td>
                    <textarea name="json:economy-assets-financial" id="editor_assets_financial" data-height="100" data-menubar="">{$json.economy.assets.financial|default:''}</textarea>
                </td>
            </tr>
            <tr>
                <td>Реальный капитал: <br>
                    <small>(промышленный)</small>
                </td>
                <td>
                    <textarea name="json:economy-assets-industrial" id="editor_assets_industrial" data-height="100" data-menubar="">{$json.economy.assets.industrial|default:''}</textarea>
                </td>
            </tr>
            <tr>
                <td>
                    Социальный капитал<br>
                    <small>(образование, медицина, <br>интеллектуальная собственность)</small>
                </td>
                <td>
                    <textarea name="json:economy-assets-social" id="editor_assets_social" data-height="100" data-menubar="">{$json.economy.assets.social|default:''}</textarea>
                </td>
            </tr>
            <tr>
                <td>
                    Старые семьи
                </td>
                <td>
                    <textarea name="json:economy-assets-oldmoney" id="editor_assets_oldmoney" data-height="100" data-menubar="">{$json.economy.assets.oldmoney|default:''}</textarea>
                </td>
            </tr>
        </table>
    </fieldset>

    <fieldset>
        <legend>Прочее</legend>
        <label>
            Известные личности: <br>
            <textarea name="json:other.local_heroes" id="editor_other_local_heroes" data-height="100" data-menubar="">{$json.other.local_heroes|default:''}</textarea>
        </label>
    </fieldset>

    <fieldset>
        <legend>Legacy-описание</legend>
        <textarea name="json:legacy.description" id="editor_legacy_description" data-height="100" data-menubar="">{$json.legacy.description|default:''}</textarea>
    </fieldset>



    <hr>
    <fieldset>
        <legend>Content Restrictions:</legend>
        <table width="90%" style="text-align: left" border="0">
            <tr>
                <td>
                    <label for="edit-restricted">Access Denied Message:
                        <input type="text" name="edit:region:content_restricted" size="70%" value="{$content_restricted}" id="edit-restricted" tabindex="2" autocomplete="off"/>
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
    </fieldset>

    <hr>

    <fieldset class="fields_area">
        <legend>Техническое</legend>
        <div class="field">
            <label for="edit-reason">Комментарий редактора:
                <input type="text" name="edit:region:comment" size="90" value="" id="edit-reason" tabindex="6" autocomplete="off"/>
            </label>
        </div>
    </fieldset>
    <div class="clear"></div>

    <fieldset>
        <div class="label_fullwidth">
            <button type="submit" id="actor-submit" tabindex="7">Сохранить</button>
            <span id="ajax-process" style="display: none">
                Сохраняю... &nbsp;
                <img src="/frontend/images/spinner_saving.svg" height="18" alt="ready"/>
            </span>
            <span id="ajax-result" style="display: none;">Сохранение успешно! Через несколько секунд возвращаемся на карту.</span>
            <button type="button" id="actor-back" style="float:right" tabindex="8" data-href="{Arris\AppRouter::getRouter('view.frontpage')}">Назад на карту</button>
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
