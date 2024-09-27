<fieldset>
    <legend>Системная лоция</legend>
    <label>
        <textarea name="json:system_chart" id="editor_system_chart" cols="10" data-height="100">{$json.system_chart}</textarea>
    </label>
</fieldset>

<fieldset>
    <legend>Интегральные индексы</legend>
    <table border="0">
        <tr>
            <td>
                <label>
                    Класс жизнепригодности: <input type="text" data-ranged="lsi_slider" name="json:lsi-index" size="10" placeholder="0..12" value="{$json.lsi.index|default:'0'}"><br>
                    <input data-ranged="lsi_slider" type="range" min="0" max="12" name="json:lsi-index-range" value="{$json.lsi.index|default:'0'}">
                </label>
            </td>
            <td>
                <label>
                    Security Status:
                    <input type="text" data-ranged="statehood_ss" name="json:statehood-ss" size="10" placeholder="0..1" value="{$json.statehood.ss|default:'0'}">
                    <br>
                    <input data-ranged="statehood_ss" type="range" min="0" max="1" step="0.1" name="json:statehood-ss" value="{$json.statehood.ss|default:'0'}">
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
                <select name="json:statehood-sector" class="action-onload-update-select" data-selected="{$json.statehood.sector|default:''}">
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
                <select name="json:statehood-type" class="action-onload-update-select" data-selected="{$json.statehood.type|default:''}">
                    <option value="">...</option>
                    <option value="автономия">автономия</option>
                    <option value="колония">колония</option>
                    <option value="протекторат">протекторат</option>
                    <option value="особый">особый</option>
                    <option value="переходный">переходный</option>
                    <option value="криминальный">криминальный</option>
                    <option value="столица">столица</option>
                </select>
                | Подчинение: <input type="text" name="json:statehood-dependency" size="40" value="{$json.statehood.dependency|default:''}">
                | Радиус: <input type="text" name="json:statehood-radius" size="40" value="{$json.statehood.radius|default:'1'}">
            </td>
        </tr>
        <tr>
            <td>
                Принцип государственного управления:
            </td>
            <td>
                <textarea name="json:statehood:administration_principle" id="editor_statehood_administration_principle" data-height="100" data-menubar="">{$json.statehood.administration_principle|default:''}</textarea>
            </td>
        </tr>

        <tr>
            <td>Местное управление</td>
            <td>
                <input type="text" name="json:statehood-local_governance" size="100" value="{$json.statehood.local_governance|default:''}">
                <br>
                <span style="font-size: x-small">(todo: удалить после переноса данных)</span>
            </td>
        </tr>
        <tr>
            <td>Территориальная гвардия</td>
            <td>
                <input type="text" name="json:statehood-terr_guards" size="100" value="{$json.statehood.terr_guards|default:''}">
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
                <input type="text" name="json:statehood-agency-css" size="100" value="{$json.statehood.agency.css|default:''}">
            </td>
        </tr>
        <tr>
            <td>ОРК</td>
            <td>
                <input type="text" name="json:statehood-agency-drc" size="100" value="{$json.statehood.agency.drc|default:''}">
            </td>
        </tr>
        <tr>
            <td>ИМБ</td>
            <td>
                <input type="text" name="json:statehood-agency-psi" size="100" value="{$json.statehood.agency.psi|default:''}">
            </td>
        </tr>
        <tr>
            <td>ВКС</td>
            <td>
                <input type="text" name="json:statehood-agency-starfleet" size="100" value="{$json.statehood.agency.starfleet|default:''}">
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
                       value="{$json.laws.language|default:''}"
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
                       value="{$json.laws.passport|default:''}"
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
                       value="{$json.laws.visa|default:''}">
            </td>
        </tr>
        <tr>
            <td>Правила ношения оружия</td>
            <td>
                <input type="text"
                       name="json:laws-gun_rights"
                       size="100"
                       placeholder="Оружие у гражданского населения, законы и ограничения"
                       value="{$json.laws.gun_rights|default:''}">
            </td>
        </tr>
        <tr>
            <td>Примат частной собственности</td>
            <td>
                <input type="text"
                       name="json:laws-private_property"
                       size="100"
                       placeholder="Принцип 'мой дом - моя крепость', защита частной собственности любыми способами"
                       value="{$json.laws.private_property|default:''}">
            </td>
        </tr>
        <tr>
            <td>Генкарта: общее</td>
            <td>
                <input type="text"
                       name="json:laws-gencard-info"
                       size="100"
                       placeholder="Используется ли, публичная ли это информация, впечатывается ли в паспорт?"
                       value="{$json.laws.gencard.info|default:''}">
            </td>
        </tr>
        <tr>
            <td>Генкарта: ограничения</td>
            <td>
                <input type="text"
                       name="json:laws-gencard-restrictions"
                       size="100"
                       placeholder="Генкарта: социальные ограничения"
                       value="{$json.laws.gencard.restrictions|default:''}">
            </td>
        </tr>

    </table>
</fieldset>

<fieldset>
    <legend>История:</legend>
    <label>
        Год открытия: <input type="text" name="json:history-year-found" value="{$json.history.year.found|default:0}">
    </label>
    <label>
        Начало колонизации: <input type="text" name="json:history-year-colonization" value="{$json.history.year.colonization|default:0}">
    </label>
    <br><br>
    <label class="label_textarea label_fullwidth">
        Краткая история колонизации:
        <textarea name="json:history-text" id="editor_history" data-height="100" data-menubar="">{$json.history.text|default:''}</textarea>
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
                <input type="text" name="json:lsi-type" size="40" placeholder="Тип планеты, тип атмосферы и аквасферы" value="{$json.lsi.type|default:''}">
            </td>
        </tr>
        <tr>
            <td>Атмосфера:</td>
            <td>
                <input type="text" name="json:lsi-atmosphere" size="40" placeholder="Атмосфера, % состав" value="{$json.lsi.atmosphere|default:''}">
            </td>
        </tr>
        <tr>
            <td>Гидросфера:&nbsp;&nbsp;&nbsp;&nbsp;</td>
            <td>
                <input type="text" name="json:lsi-hydrosphere" size="40" placeholder="Гидросфера, %" value="{$json.lsi.hydrosphere|default:''}">
            </td>
        </tr>
        <tr>
            <td>Климат:</td>
            <td>
                <input type="text" name="json:lsi-climate" size="40" placeholder="Климат заселенных регионов" value="{$json.lsi.climate|default:''}">
            </td>
        </tr>
    </table>
</fieldset>

<fieldset>
    <legend>Население</legend>
    <table class="aligned-center">
        <tr>
            <td width="300">Численность (млн)</td>
            <td style="text-align: left"><input type="text" name="json:population-count" size="10" value="{$json.population.count|default:0}"></td>
        </tr>
        <tr>
            <td>Национальный состав:</td>
            <td><textarea cols="40" rows="5" name="json:population-ethnic">{$json.population.ethnic|default:''}</textarea></td>
        </tr>
        <tr>
            <td>Основные религии:</td>
            <td><textarea cols="40" rows="5" name="json:population-religion">{$json.population.religion|default:''}</textarea></td>
        </tr>
        <tr>
            <td>Этнопсихологические особенности:</td>
            <td><textarea cols="40" rows="5" name="json:population-features">{$json.population.features|default:''}</textarea></td>
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
    <legend>Культура</legend>
    <table>
        <tr>
            <td>Местная валюта</td>
            <td>
                <input type="text" name="json:culture-currency" size="40" value="{$json.culture.currency|default:''}">
            </td>
        </tr>
        <tr>
            <td>
                Праздники <br>
            </td>
            <td>
                <textarea name="json:culture-holydays" id="editor_culture_holydays" data-height="100">{$json.culture.holydays|default:''}</textarea>
            </td>
        </tr>
        <tr>
            <td>Достопримечательности</td>
            <td>
                <textarea name="json:culture-showplaces" id="editor_culture_showplaces" data-height="100">{$json.culture.showplaces|default:''}</textarea>
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
                    <textarea name="json:other-local_heroes" id="editor_other_local_heroes" data-height="100" data-menubar="">{$json.other.local_heroes|default:''}</textarea>
                </label>
            </td>
        </tr>
        <tr>
            <td>
                Непроверенные данные и слухи:
            </td>
            <td>
                <label>
                    <textarea name="json:other-unverified_data" id="editor_other_unverified_data" data-height="100" data-menubar="">{$json.other.unverified_data|default:''}</textarea>
                </label>
            </td>
        </tr>
        <tr>
            <td>Legacy data:</td>
            <td>
                <label>
                    <textarea name="json:other-legacy" id="editor_legacy_description" data-height="100" data-menubar="">{$json.other.legacy|default:''}</textarea>
                </label>
            </td>
        </tr>
    </table>
</fieldset>

<fieldset>
    <legend>Теги для поиска (разделенные пробелами)</legend>
    <input type="text" name="json:tags" size="80" value="{$json.tags|default:''}">
</fieldset>
