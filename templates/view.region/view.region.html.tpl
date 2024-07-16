<!DOCTYPE html>
{if !$is_present}
    {if $is_can_edit}
        <button type="button"
                data-action="redirect"
                data-region-id="{$region_id}"
                data-map-alias="{$map_alias}"
                data-url="{$edit_button_url}?map={$map_alias}&id={$region_id}">Добавить</button>
    {/if}
    <br/>По объекту <code><strong>{$region_id}</strong></code> нет информации.
{else}
    {if $is_can_edit}
        <button type="button"
                data-action="redirect"
                data-region-id="{$region_id}"
                data-map-alias="{$map_alias}"
                data-url="{$edit_button_url}?map={$map_alias}&id={$region_id}">Редактировать</button>
    {/if}
    <div class="region-content">

        <h2>{$region_title}</h2>
        {$region_text}
        <hr>

        <fieldset>
            <legend>Открыт в {$json->history->year|default:'n/a'} году от начала колонизации:</legend>
            {if $json->history->text}
                {$json->history->text|default:''}
            {/if}
        </fieldset>

        <fieldset>
            <legend>Life Support Index</legend>
            <table class="second_td_padded">
                <tr>
                    <td>Класс:</td>
                    <td>{$json->lsi->index|default:0}</td>
                </tr>
                <tr>
                    <td>Тип:</td>
                    <td>{$json->lsi->type|default:'n/a'}</td>
                </tr>

                {if $json->lsi->atmosphere}
                <tr>
                    <td>Атмосфера:</td>
                    <td>{$json->lsi->atmosphere}</td>
                </tr>
                {/if}
                {if $json->lsi->hydrosphere}
                    <tr>
                        <td>Гидросфера:</td>
                        <td>{$json->lsi->hydrosphere}</td>
                    </tr>
                {/if}
                {if $json->lsi->climate}
                    <tr>
                        <td>Климат регионов:</td>
                        <td>{$json->lsi->climate}</td>
                    </tr>
                {/if}
            </table>
        </fieldset>



        <fieldset>
            <legend>Население: </legend>
            <table class="second_td_padded">
                {if $json->population->count}
                    <tr>
                        <td>Численность (млн): </td>
                        <td>{$json->population->count}</td>
                    </tr>
                {/if}
                {if $json->population->ethnic}
                    <tr>
                        <td>Национальный состав:</td>
                        <td>{$json->population->ethnic}</td>
                    </tr>
                {/if}
                {if $json->population->religion}
                    <tr>
                        <td>Основные религии: </td>
                        <td>{$json->population->religion}</td>
                    </tr>
                {/if}
                {if $json->population->features}
                    <tr>
                        <td>Этнопсихологические <br>особенности: </td>
                        <td>{$json->population->features}</td>
                    </tr>
                {/if}
            </table>
        </fieldset>

        <fieldset>
            <legend>Государственные механизмы</legend>
            <table>
                {if $json->statehood->ss}
                <tr>
                    <td>Security Status:</td>
                    <td>
                        {$json->statehood->ss|default:''}
                    </td>
                </tr>
                {/if}
                {if $json->statehood->gun_rights}
                <tr>
                    <td>Правила ношения оружия:</td>
                    <td>
                        {$json->statehood->gun_rights|default:''}
                    </td>
                </tr>
                {/if}
                {if $json->statehood->confstatus}
                <tr>
                    <td>Конфедеративный статус</td>
                    <td>
                        {$json->statehood->confstatus|default:''}
                    </td>
                </tr>
                {/if}
                {if $json->statehood->local_governance}
                <tr>
                    <td>Местное управление</td>
                    <td>
                        {$json->statehood->local_governance|default:''}
                    </td>
                </tr>
                {/if}
                {if $json->statehood->terr_guards}
                <tr>
                    <td>Территориальная гвардия</td>
                    <td>
                        {$json->statehood->terr_guards|default:''}
                    </td>
                </tr>
                {/if}
                <tr>
                    <td colspan="2">
                        <strong>Представители структур Конфедерации:</strong>
                    </td>
                </tr>
                <tr>
                    <td>КСБ</td>
                    <td>
                        {$json->statehood->agency->css|default:'нет данных'}
                    </td>
                </tr>
                <tr>
                    <td>ОРК</td>
                    <td>
                        {$json->statehood->agency->drc|default:'нет данных'}
                    </td>
                </tr>
                <tr>
                    <td>ИМБ</td>
                    <td>
                        {$json->statehood->agency->psi|default:'нет данных'}
                    </td>
                </tr>
                <tr>
                    <td>ВКС</td>
                    <td>
                        {$json->statehood->agency->starfleet|default:'нет данных'}
                    </td>
                </tr>
            </table>
        </fieldset>

        <fieldset>
            <legend>Экономика</legend>
            Тип: {$json->economy->type}<br> <br>

            {if $pie_chart.present}
                <canvas id="economy_pie_chart" width="400" height="200"></canvas>
                <script>
                    var chart_data = {$pie_chart.full};
                    var canvas = document.getElementById("economy_pie_chart");
                    var ctx = canvas.getContext("2d");
                    var lastend = 0;
                    var sum_of_data_values = 0;

                    for (let e = 0; e < chart_data.length; e++) { sum_of_data_values += chart_data[e].data; }

                    var offset = 10; // make the chart 10 px smaller to fit on canvas
                    var h = (canvas.height - offset) / 2;
                    var w = h; // (canvas.width - off) / 2;
                    for (let i = 0; i < chart_data.length; i++) {
                        ctx.fillStyle = chart_data[i].color;
                        ctx.strokeStyle ='white';
                        ctx.lineWidth = 2;
                        ctx.beginPath();
                        ctx.moveTo(w,h);
                        let len = (chart_data[i].data / sum_of_data_values) * 2 * Math.PI;
                        let r = h - offset / 2;
                        ctx.arc(w , h, r, lastend, lastend + len,false);
                        ctx.lineTo(w,h);
                        ctx.fill();
                        ctx.stroke();
                        ctx.fillStyle = 'white';
                        ctx.font = "16px Arial";
                        ctx.textAlign = "center";
                        ctx.textBaseline = "middle";
                        let mid = lastend + len / 2;
                        ctx.fillText(chart_data[i].label, w + Math.cos(mid) * (r/2) , h + Math.sin(mid) * (r/2));
                        lastend += Math.PI*2*(chart_data[i].data / sum_of_data_values);
                    }
                    // рисуем подписи
                    var rectX = h + 100;
                    var rectY = 6;
                    var rectHeight = 40;
                    var rectWidth = 100;
                    var padding_vertical = 5;
                    for (let i = 0; i < chart_data.length; i++) {
                        // ctx.lineWidth = 1;
                        // ctx.strokeStyle = "#000000";
                        ctx.fillStyle = chart_data[i].color;
                        roundRect(ctx, rectX, rectY, rectWidth, rectHeight, 10, true);
                        ctx.font = "14px Georgia";
                        ctx.textAlign = "center";
                        ctx.textBaseline = "middle";
                        ctx.fillStyle = "#000000";
                        ctx.fillText(chart_data[i].hint, rectX + (rectWidth/2), rectY + (rectHeight/2));
                        rectY += rectHeight + padding_vertical;
                    }

                    function roundRect(context, x, y, width, height, radius, fill, stroke) {
                        if (typeof stroke == "undefined" ) {
                            stroke = true;
                        }
                        if (typeof radius === "undefined") {
                            radius = 5;
                        }
                        context.beginPath();
                        context.moveTo(x + radius, y);
                        context.lineTo(x + width - radius, y);
                        context.quadraticCurveTo(x + width, y, x + width, y + radius);
                        context.lineTo(x + width, y + height - radius);
                        context.quadraticCurveTo(x + width, y + height, x + width - radius, y + height);
                        context.lineTo(x + radius, y + height);
                        context.quadraticCurveTo(x, y + height, x, y + height - radius);
                        context.lineTo(x, y + radius);
                        context.quadraticCurveTo(x, y, x + radius, y);
                        context.closePath();
                        if (stroke) {
                            context.stroke();
                        }
                        if (fill) {
                            context.fill();
                        }
                    }
                </script>
            {/if}
        </fieldset>

        {* ? получится ли вложить статьи экспорта и импорта в контейнер "экономика" ? по ширине, конечно! *}

        {if $json->trade->export || $json->trade->import}
        <fieldset>
            <legend>Торговля (статьи экспорта и импорта)</legend>
                <table width="100%" border="1">
                    <tr>
                        <td style="padding-left: 0em; text-align: center;font-weight: bold;">Статьи экспорта:</td>
                        <td style="padding-left: 0em; text-align: center;font-weight: bold;">Статьи импорта:</td>
                    </tr>
                    <tr>
                        <td style="padding-left: 1em;">{$json->trade->export}</td>
                        <td style="padding-left: 1em">{$json->trade->import}</td>
                    </tr>
                </table>
        </fieldset>
        {/if}

        <fieldset>
            <legend>Крупные представители капитала:</legend>
            <table width="100%" border="1px solid gray">
                {if $json->economy->assets->natural}
                    <tr>
                        <td>Природный капитал:</td>
                        <td>{$json->economy->assets->natural}</td>
                    </tr>
                {/if}

                {if $json->economy->assets->financial}
                    <tr>
                        <td>Финансовый капитал:</td>
                        <td>{$json->economy->assets->financial}</td>
                    </tr>
                {/if}

                {if $json->economy->assets->industrial}
                    <tr>
                        <td>Реальный капитал:<br>
                            <small>(промышленный)</small>
                        </td>
                        <td>{$json->economy->assets->industrial}</td>
                    </tr>
                {/if}

                {if $json->economy->assets->social}
                    <tr>
                        <td>Социальный капитал:
                            <br>
                            <small>(образование, медицина,<br>
                                интеллектуальная собственность)</small>
                        </td>
                        <td>{$json->economy->assets->social}</td>
                    </tr>
                {/if}

                {if $json->economy->assets->oldmoney}
                    <tr>
                        <td>Старые семьи:</td>
                        <td>{$json->economy->assets->oldmoney}</td>
                    </tr>
                {/if}

            </table>
        </fieldset>

        {if $json->other->local_heroes}
            <fieldset>
                <legend>Известные личности:</legend>
                {$json->other->local_heroes}
            </fieldset>
        {/if}

        {if $json->legacy->description}
            <fieldset>
                <legend>Legacy</legend>
                {$json->legacy->description}
            </fieldset>
        {/if}

        {if $json->tags}

            <fieldset>
                <legend>Теги</legend>
                <small>
                {$json->tags}
                </small>
            </fieldset>
        {/if}



        {* сюда вкладываем отображение всех остальных полей *}
    </div>
    {* ===== СТИЛИ ===== *}
    <style>
        fieldset {
            margin-top: 0.5em;
        }
        fieldset legend {
            color: dodgerblue;
        }
        table.second_td_padded td:nth-child(2) {
            padding-left: 1em;
        }
    </style>
{/if}
