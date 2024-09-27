<script>
    $(document).ready(function() {
        let _editRegion = new EditorConnector({ menubar: false });

        _editRegion.createInstance('editor_history');
        _editRegion.createInstance('editor_trade_export');
        _editRegion.createInstance('editor_trade_import');
        _editRegion.createInstance('editor_assets_natural');
        _editRegion.createInstance('editor_assets_financial');
        _editRegion.createInstance('editor_assets_industrial');
        _editRegion.createInstance('editor_assets_social');
        _editRegion.createInstance('editor_assets_oldmoney');
        _editRegion.createInstance('editor_other_local_heroes');
        _editRegion.createInstance('editor_legacy_description');
        _editRegion.createInstance('editor_statehood_administration_principle', { height: 300 });
        _editRegion.createInstance('editor_system_chart', { /*toolbar: false, width: 600, height: 300*/ });

        _editRegion.createInstance('editor_other_unverified_data');

        _editRegion.createInstance('editor_culture_holydays');
        _editRegion.createInstance('editor_culture_showplaces');
    }); // jQuery
</script>
<fieldset>
    <legend>Системная лоция</legend>
    <label>
        <textarea name="json:system_chart" id="editor_system_chart" cols="10" data-height="100">{$content_extra->system_chart|default:''}</textarea>
    </label>
</fieldset>

<fieldset>
    <legend>Интегральные индексы</legend>
    <table border="0">
        <tr>
            <td>
                <label>
                    Класс жизнепригодности: <input type="text" data-ranged="lsi_slider" name="json:lsi-index" size="10" placeholder="0..12" value="{$content_extra->lsi->index|default:'0'}"><br>
                    <input data-ranged="lsi_slider" type="range" min="0" max="12" name="json:lsi-index-range" value="{$content_extra->lsi->index|default:'0'}">
                </label>
            </td>
            <td>
                <label>
                    Security Status:
                    <input type="text" data-ranged="statehood_ss" name="json:statehood-ss" size="10" placeholder="0..1" value="{$content_extra->statehood->ss|default:'0'}">
                    <br>
                    <input data-ranged="statehood_ss" type="range" min="0" max="1" step="0.1" name="json:statehood-ss" value="{$content_extra->statehood->ss|default:'0'}">
                </label>
            </td>
        </tr>
    </table>
</fieldset>

<fieldset>
    <legend>Государственный статус</legend>
    <table>
        <tr>
            <td>Сектор</td>
            <td>
                <select name="json:statehood-sector" class="action-onload-update-select" data-selected="{$content_extra->statehood->sector|default:''}">
                    <option value="">...</option>
                    <option value="Сириус">Сириус</option>
                    <option value="Рингтейла">Рингтейла</option>
                    <option value="Фомальгаута">Фомальгаута</option>
                    <option value="Веги">Веги</option>
                    <option value="Найтвиша">Найтвиша</option>
                    <option value="Ориона">Ориона</option>
                    <option value="Внеземелье">Дальнее внеземелье</option>
                </select>
            </td>
        </tr>
        <tr>
            <td>Тип и подчинение</td>
            <td>
                <select name="json:statehood-type" class="action-onload-update-select" data-selected="{$content_extra->statehood->type|default:''}">
                    <option value="">...</option>
                    <option value="автономия">автономия</option>
                    <option value="колония">колония</option>
                    <option value="протекторат">протекторат</option>
                    <option value="особый">особый</option>
                    <option value="переходный">переходный</option>
                    <option value="криминальный">криминальный</option>
                    <option value="столица">столица</option>
                </select>
                | Подчинение: <input type="text" name="json:statehood-dependency" size="40" value="{$content_extra->statehood->dependency|default:''}">
                | Радиус: <input type="text" name="json:statehood-radius" size="40" value="{$content_extra->statehood->radius|default:'1'}">
            </td>
        </tr>
        <tr>
            <td>
                Принцип государственного управления:
            </td>
            <td>
                <textarea name="json:statehood:administration_principle" id="editor_statehood_administration_principle" data-height="100" data-menubar="">{$content_extra->statehood->administration_principle|default:''}</textarea>
            </td>
        </tr>

        <tr>
            <td>Местное управление</td>
            <td>
                <input type="text" name="json:statehood-local_governance" size="100" value="{$content_extra->statehood->local_governance|default:''}">
                <br>
                <span style="font-size: x-small">(todo: удалить после переноса данных)</span>
            </td>
        </tr>
        <tr>
            <td>Территориальная гвардия</td>
            <td>
                <input type="text" name="json:statehood-terr_guards" size="100" value="{$content_extra->statehood->terr_guards|default:''}">
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <br>
                <strong>Представители структур Конфедерации:</strong>
            </td>
        </tr>
        <tr>
            <td>КСБ</td>
            <td>
                <input type="text" name="json:statehood-agency-css" size="100" value="{$content_extra->statehood->agency->css|default:''}">
            </td>
        </tr>
        <tr>
            <td>ОРК</td>
            <td>
                <input type="text" name="json:statehood-agency-drc" size="100" value="{$content_extra->statehood->agency->drc|default:''}">
            </td>
        </tr>
        <tr>
            <td>ИМБ</td>
            <td>
                <input type="text" name="json:statehood-agency-psi" size="100" value="{$content_extra->statehood->agency->psi|default:''}">
            </td>
        </tr>
        <tr>
            <td>ВКС</td>
            <td>
                <input type="text" name="json:statehood-agency-starfleet" size="100" value="{$content_extra->statehood->agency->starfleet|default:''}">
            </td>
        </tr>
    </table>
</fieldset>

<fieldset>
    <legend>Законы и нормы</legend>
    <table border="0" style="border-color: blue">
        <tr>
            <td width="300">Государственный язык</td>
            <td>
                <input type="text"
                       name="json:laws-language"
                       size="100"
                       placeholder="Государственный язык"
                       value="{$content_extra->laws->language|default:''}"
                >
            </td>
        </tr>
        <tr>
            <td>Паспортный режим</td>
            <td>
                <input type="text"
                       name="json:laws-passport"
                       size="100"
                       placeholder="Отношение властей к документам граждан (или их отсутствию)"
                       value="{$content_extra->laws->passport|default:''}"
                >
            </td>
        </tr>
        <tr>
            <td>Визовой режим</td>
            <td>
                <input type="text"
                       name="json:laws-visa"
                       size="100"
                       placeholder="Отношение властей к пришельцам с других планет"
                       value="{$content_extra->laws->visa|default:''}">
            </td>
        </tr>
        <tr>
            <td>Правила ношения оружия</td>
            <td>
                <input type="text"
                       name="json:laws-gun_rights"
                       size="100"
                       placeholder="Оружие у гражданского населения, законы и ограничения"
                       value="{$content_extra->laws->gun_rights|default:''}">
            </td>
        </tr>
        <tr>
            <td>Примат частной собственности</td>
            <td>
                <input type="text"
                       name="json:laws-private_property"
                       size="100"
                       placeholder="Принцип 'мой дом - моя крепость', защита частной собственности любыми способами"
                       value="{$content_extra->laws->private_property|default:''}">
            </td>
        </tr>
        <tr>
            <td>Генкарта: общее</td>
            <td>
                <input type="text"
                       name="json:laws-gencard-info"
                       size="100"
                       placeholder="Используется ли, публичная ли это информация, впечатывается ли в паспорт?"
                       value="{$content_extra->laws->gencard->info|default:''}">
            </td>
        </tr>
        <tr>
            <td>Генкарта: ограничения</td>
            <td>
                <input type="text"
                       name="json:laws-gencard-restrictions"
                       size="100"
                       placeholder="Генкарта: социальные ограничения"
                       value="{$content_extra->laws->gencard->restrictions|default:''}">
            </td>
        </tr>

    </table>
</fieldset>

<fieldset>
    <legend>История:</legend>
    <label>
        Год открытия: <input type="text" name="json:history-year-found" value="{$content_extra->history->year->found|default:0}">
    </label>
    <label>
        Начало колонизации: <input type="text" name="json:history-year-colonization" value="{$content_extra->history->year->colonization|default:0}">
    </label>
    <br><br>
    <label class="label_textarea label_fullwidth">
        Краткая история колонизации:
        <textarea name="json:history-text" id="editor_history" data-height="100" data-menubar="">{$content_extra->history->text|default:''}</textarea>
    </label>
</fieldset>

<fieldset>
    <legend>Биосфера</legend>
    <table>
        <tr>
            <td>
                Тип:
            </td>
            <td>
                <input type="text" name="json:lsi-type" size="40" placeholder="Тип планеты, тип атмосферы и аквасферы" value="{$content_extra->lsi->type|default:''}">
            </td>
        </tr>
        <tr>
            <td>Атмосфера:</td>
            <td>
                <input type="text" name="json:lsi-atmosphere" size="40" placeholder="Атмосфера, % состав" value="{$content_extra->lsi->atmosphere|default:''}">
            </td>
        </tr>
        <tr>
            <td>Гидросфера:&nbsp;&nbsp;&nbsp;&nbsp;</td>
            <td>
                <input type="text" name="json:lsi-hydrosphere" size="40" placeholder="Гидросфера, %" value="{$content_extra->lsi->hydrosphere|default:''}">
            </td>
        </tr>
        <tr>
            <td>Климат:</td>
            <td>
                <input type="text" name="json:lsi-climate" size="40" placeholder="Климат заселенных регионов" value="{$content_extra->lsi->climate|default:''}">
            </td>
        </tr>
    </table>
</fieldset>

<fieldset>
    <legend>Население</legend>
    <table class="aligned-center">
        <tr>
            <td width="300">Численность (млн)</td>
            <td style="text-align: left"><input type="text" name="json:population-count" size="10" value="{$content_extra->population->count|default:0}"></td>
        </tr>
        <tr>
            <td>Национальный состав:</td>
            <td><textarea cols="40" rows="5" name="json:population-ethnic">{$content_extra->population->ethnic|default:''}</textarea></td>
        </tr>
        <tr>
            <td>Основные религии:</td>
            <td><textarea cols="40" rows="5" name="json:population-religion">{$content_extra->population->religion|default:''}</textarea></td>
        </tr>
        <tr>
            <td>Этнопсихологические особенности:</td>
            <td><textarea cols="40" rows="5" name="json:population-features">{$content_extra->population->features|default:''}</textarea></td>
        </tr>
    </table>
</fieldset>

<fieldset>
    <legend>Экономика</legend>
    <label>
        Тип экономики:  <input type="text" name="json:economy-type" size="70" value="{$content_extra->economy->type|default:''}">
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
                <input type="text" name="json:economy-shares-natural" size="10" value="{$content_extra->economy->shares->natural|default:''}">
            </td>
            <td>
                <input type="text" name="json:economy-shares-financial" size="10" value="{$content_extra->economy->shares->financial|default:''}">
            </td>
            <td>
                <input type="text" name="json:economy-shares-industrial" size="10" value="{$content_extra->economy->shares->industrial|default:''}">
            </td>
            <td>
                <input type="text" name="json:economy-shares-social" size="10" value="{$content_extra->economy->shares->social|default:''}">
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
                <textarea name="json:trade-export" id="editor_trade_export" data-height="100" data-menubar="">{$content_extra->trade->export|default:''}</textarea>
            </td>
            <td>
                <textarea name="json:trade-import" id="editor_trade_import" data-height="100" data-menubar="">{$content_extra->trade->import|default:''}</textarea>
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
                <textarea name="json:economy-assets-natural" id="editor_assets_natural" data-height="100" data-menubar="">{$content_extra->economy->assets->natural|default:''}</textarea>
            </td>
        </tr>
        <tr>
            <td>Финансовый капитал: </td>
            <td>
                <textarea name="json:economy-assets-financial" id="editor_assets_financial" data-height="100" data-menubar="">{$content_extra->economy->assets->financial|default:''}</textarea>
            </td>
        </tr>
        <tr>
            <td>Реальный капитал: <br>
                <small>(промышленный)</small>
            </td>
            <td>
                <textarea name="json:economy-assets-industrial" id="editor_assets_industrial" data-height="100" data-menubar="">{$content_extra->economy->assets->industrial|default:''}</textarea>
            </td>
        </tr>
        <tr>
            <td>
                Социальный капитал<br>
                <small>(образование, медицина, <br>интеллектуальная собственность)</small>
            </td>
            <td>
                <textarea name="json:economy-assets-social" id="editor_assets_social" data-height="100" data-menubar="">{$content_extra->economy->assets->social|default:''}</textarea>
            </td>
        </tr>
        <tr>
            <td>
                Старые семьи
            </td>
            <td>
                <textarea name="json:economy-assets-oldmoney" id="editor_assets_oldmoney" data-height="100" data-menubar="">{$content_extra->economy->assets->oldmoney|default:''}</textarea>
            </td>
        </tr>
    </table>
</fieldset>

<fieldset>
    <legend>Культура</legend>
    <table>
        <tr>
            <td>Местная валюта</td>
            <td>
                <input type="text" name="json:culture-currency" size="40" value="{$content_extra->culture->currency|default:''}">
            </td>
        </tr>
        <tr>
            <td>
                Праздники <br>
            </td>
            <td>
                <textarea name="json:culture-holydays" id="editor_culture_holydays" data-height="100">{$content_extra->culture->holydays|default:''}</textarea>
            </td>
        </tr>
        <tr>
            <td>Достопримечательности</td>
            <td>
                <textarea name="json:culture-showplaces" id="editor_culture_showplaces" data-height="100">{$content_extra->culture->showplaces|default:''}</textarea>
            </td>
        </tr>


    </table>
</fieldset>

<fieldset>
    <legend>Прочее</legend>

    <table>
        <tr>
            <td>Известные личности:</td>
            <td>
                <label>
                    <textarea name="json:other-local_heroes" id="editor_other_local_heroes" data-height="100" data-menubar="">{$content_extra->other->local_heroes|default:''}</textarea>
                </label>
            </td>
        </tr>
        <tr>
            <td>
                Непроверенные данные и слухи:
            </td>
            <td>
                <label>
                    <textarea name="json:other-unverified_data" id="editor_other_unverified_data" data-height="100" data-menubar="">{$content_extra->other->unverified_data|default:''}</textarea>
                </label>
            </td>
        </tr>
        <tr>
            <td>Legacy data:</td>
            <td>
                <label>
                    <textarea name="json:other-legacy" id="editor_legacy_description" data-height="100" data-menubar="">{$content_extra->other->legacy|default:''}</textarea>
                </label>
            </td>
        </tr>
    </table>
</fieldset>

<fieldset>
    <legend>Теги для поиска (разделенные пробелами)</legend>
    <input type="text" name="json:tags" size="80" value="{$content_extra->tags|default:''}">
</fieldset>
