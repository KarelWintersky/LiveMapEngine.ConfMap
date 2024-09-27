{* сюда вкладываем отображение всех остальных полей *}
<hr>

{if $content_extra->history->year && $content_extra->history->text}
    <fieldset>
        <legend>История</legend>
        <table>
            {if $content_extra->history->year}
                <tr>
                    <td width="{$first_dt_width}">Открыт</td>
                    <td style="color: #035bc4; font-weight: bold;">
                        {$content_extra->history->year->found|default:0} год
                    </td>
                </tr>
                <tr>
                    <td>Начата колонизация</td>
                    <td style="color: #035bc4; font-weight: bold;">
                        {$content_extra->history->year->colonization|default:0}  год
                    </td>

                </tr>
            {/if}
            {if $content_extra->history->text}
                <tr>
                    <td colspan="2">
                        {$content_extra->history->text|default:''}
                    </td>
                </tr>
            {/if}
        </table>
    </fieldset>
{/if}

<fieldset>
    <legend>Life Support Index</legend>
    <table class="second_td_padded">
        <tr>
            <td width="{$first_dt_width}">Класс:</td>
            <td>{$content_extra->lsi->index|default:0}</td>
        </tr>
        <tr>
            <td>Тип:</td>
            <td>{$content_extra->lsi->type|default:'n/a'}</td>
        </tr>

        {if $content_extra->lsi->atmosphere}
            <tr>
                <td>Атмосфера:</td>
                <td>{$content_extra->lsi->atmosphere}</td>
            </tr>
        {/if}
        {if $content_extra->lsi->hydrosphere}
            <tr>
                <td>Гидросфера:</td>
                <td>{$content_extra->lsi->hydrosphere}</td>
            </tr>
        {/if}
        {if $content_extra->lsi->climate}
            <tr>
                <td>Климат регионов:</td>
                <td>{$content_extra->lsi->climate}</td>
            </tr>
        {/if}
    </table>
</fieldset>

<fieldset>
    <legend>Население: </legend>
    <table class="second_td_padded">
        {if $content_extra->population->count}
            <tr>
                <td width="{$first_dt_width}">Численность: </td>
                <td>{$content_extra->population->count|default:'нет данных'}</td>
            </tr>
        {else}
            <tr>
                <td colspan="2">Нет данных о численности</td>
            </tr>
        {/if}
        {if $content_extra->population->ethnic}
            <tr>
                <td>Национальный состав:</td>
                <td>{$content_extra->population->ethnic}</td>
            </tr>
        {/if}
        {if $content_extra->population->religion}
            <tr>
                <td>Основные религии: </td>
                <td>{$content_extra->population->religion}</td>
            </tr>
        {/if}
        {if $content_extra->population->features}
            <tr>
                <td>Этнопсихологические <br>особенности: </td>
                <td>{$content_extra->population->features}</td>
            </tr>
        {/if}
    </table>
</fieldset>

<fieldset>
    <legend>Государственный статус</legend>
    <table>
        {if $content_extra->statehood->type}
            <tr>
                <td>Тип и подчинение</td>
                <td>
                    {$content_extra->statehood->type}
                    {if $content_extra->statehood->dependency}
                        ({$content_extra->statehood->dependency})
                    {/if}

                </td>
            </tr>
            {if $content_extra->statehood->radius}
                <tr>
                    <td>Радиус</td>
                    <td>{$content_extra->statehood->radius}</td>
                </tr>
            {/if}
        {/if}
        {if $content_extra->statehood->ss}
            <tr>
                <td>Security Status:</td>
                <td>
                    {$content_extra->statehood->ss|default:''}
                </td>
            </tr>

        {/if}

        {if $content_extra->statehood->confstatus}
            <tr>
                <td>Конфедеративный статус</td>
                <td>
                    {$content_extra->statehood->confstatus|default:''}
                </td>
            </tr>
        {/if}
        {if $content_extra->statehood->local_governance}
            <tr>
                <td>Местное управление</td>
                <td>
                    {$content_extra->statehood->local_governance|default:''}
                </td>
            </tr>
        {/if}
        {if $content_extra->statehood->terr_guards}
            <tr>
                <td>Территориальная гвардия</td>
                <td>
                    {$content_extra->statehood->terr_guards|default:''}
                </td>
            </tr>
        {/if}
        <tr>
            <td colspan="2">
                <strong>Представители структур Конфедерации:<br></strong>
            </td>
        </tr>
        <tr>
            <td width="{$first_dt_width}">КСБ</td>
            <td>
                {$content_extra->statehood->agency->css|default:'нет данных'}
            </td>
        </tr>
        <tr>
            <td>ОРК</td>
            <td>
                {$content_extra->statehood->agency->drc|default:'нет данных'}
            </td>
        </tr>
        <tr>
            <td>ИМБ</td>
            <td>
                {$content_extra->statehood->agency->psi|default:'нет данных'}
            </td>
        </tr>
        <tr>
            <td>ВКС</td>
            <td>
                {$content_extra->statehood->agency->starfleet|default:'нет данных'}
            </td>
        </tr>
    </table>
</fieldset>

{if $view_mode == 'infobox'}
    <fieldset>
        <legend>Экономика</legend>
        <strong>Тип:</strong> {$content_extra->economy->type}<br> <br>

        {if $content_extra->pie_chart.present}
            <canvas id="economy_pie_chart" width="400" height="200"></canvas>
            <script>
                var chart_data = {$content_extra->pie_chart.full};
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

    {if $content_extra->trade->export || $content_extra->trade->import}
        <fieldset>
            <legend>Торговля (статьи экспорта и импорта)</legend>
            <table width="100%" border="0">
                <tr>
                    <td width="{$first_dt_width}" style="padding-left: 0em; text-align: left;font-weight: bold;">
                        Статьи экспорта:
                    </td>
                    <td style="padding-left: 0em;">
                        {$content_extra->trade->export|default:'<ul>нет данных</ul>'}
                    </td>
                </tr>
                <tr>
                    <td style="padding-left: 0em; text-align: left;font-weight: bold;">
                        Статьи импорта:
                    </td>
                    <td style="padding-left: 0em;">
                        {$content_extra->trade->import|default:'<ul>нет данных</ul>'}
                    </td>
                </tr>
            </table>
        </fieldset>
    {/if}

{else}
    {* colorbox*}
    {* попробуем вложить статьи экспорта/импорта и круговую диаграмму в один контейнер *}
    <fieldset>
        <legend>Тип экономики, экспорт и импорт</legend>
        <table border="1" width="100%">
            <tr>
                <th width="400" style="text-align: center">Тип экономики</th>
                <td colspan="2">
                </td>
            </tr>
            <tr>
                <th style="text-align: center">{$content_extra->economy->type}</th>
                <th width="50%" style="text-align: center">Статьи экспорта</th>
                <th width="50%" style="text-align: center">Статьи импорта</th>
            </tr>
            <tr>
                <td>
                    {if $content_extra->pie_chart.present}
                        <canvas id="economy_pie_chart" width="350" height="200"></canvas>
                        <script>
                            var chart_data = {$content_extra->pie_chart.full};
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
                </td>
                <td valign="top">
                    {$content_extra->trade->export}
                </td>
                <td valign="top">
                    {$content_extra->trade->import}
                </td>
            </tr>
        </table>
    </fieldset>
{/if}

<fieldset>
    <legend>Крупные представители капитала:</legend>
    <table width="100%" border="1px solid gray">
        {if $content_extra->economy->assets->natural}
            <tr>
                <td width="{$first_dt_width}">Природный капитал:</td>
                <td>{$content_extra->economy->assets->natural}</td>
            </tr>
        {/if}

        {if $content_extra->economy->assets->financial}
            <tr>
                <td width="{$first_dt_width}">Финансовый капитал:</td>
                <td>{$content_extra->economy->assets->financial}</td>
            </tr>
        {/if}

        {if $content_extra->economy->assets->industrial}
            <tr>
                <td width="{$first_dt_width}">Реальный капитал:<br>
                    <small>(промышленный)</small>
                </td>
                <td>{$content_extra->economy->assets->industrial}</td>
            </tr>
        {/if}

        {if $content_extra->economy->assets->social}
            <tr>
                <td width="{$first_dt_width}">Социальный капитал:
                    <br>
                    <small>(образование, медицина,<br>
                        интеллектуальная собственность)</small>
                </td>
                <td>{$content_extra->economy->assets->social}</td>
            </tr>
        {/if}

        {if $content_extra->economy->assets->oldmoney}
            <tr>
                <td width="{$first_dt_width}">Старые семьи:</td>
                <td>{$content_extra->economy->assets->oldmoney}</td>
            </tr>
        {/if}
    </table>
</fieldset>

<fieldset>
    <legend>Законы и нормы</legend>
    <table>
        {if $content_extra->laws->passport}
            <tr>
                <td title="Отношение властей к документам граждан (или их отсутствию)">Паспортный режим:</td>
                <td>
                    {$content_extra->laws->passport|default:''}
                </td>
            </tr>
        {/if}

        {if $content_extra->laws->visa}
            <tr>
                <td title="Отношение властей к пришельцам с других планет">Визовый режим:</td>
                <td>
                    {$content_extra->laws->visa|default:''}
                </td>
            </tr>
        {/if}

        {if $content_extra->laws->gun_rights}
            <tr>
                <td title="Оружие у гражданского населения, законы и ограничения">Правила ношения оружия:</td>
                <td>
                    {$content_extra->laws->gun_rights|default:''}
                </td>
            </tr>
        {/if}

        {if $content_extra->laws->private_property}
            <tr>
                <td title="Принцип 'мой дом - моя крепость', защита частной собственности любыми способами">Частная собственность:</td>
                <td>
                    {$content_extra->laws->private_property|default:''}
                </td>
            </tr>
        {/if}

        {if $content_extra->laws->gencard->info}
            <tr>
                <td title="Используется ли, публичная ли это информация, впечатывается ли в паспорт?">Генкарта: общее:</td>
                <td>
                    {$content_extra->laws->gencard->info|default:''}
                </td>
            </tr>
        {/if}


        {if $content_extra->laws->gencard->restrictions}
            <tr>
                <td title="Генкарта: социальные ограничения">Генкарта: ограничения:</td>
                <td>
                    {$content_extra->laws->gencard->restrictions|default:''}
                </td>
            </tr>
        {/if}
    </table>
</fieldset>

{if $content_extra->other->local_heroes}
    <fieldset>
        <legend>Известные личности:</legend>
        {$content_extra->other->local_heroes}
    </fieldset>
{/if}

{if $content_extra->legacy->description}
    <fieldset>
        <legend>Legacy</legend>
        {$content_extra->legacy->description}
    </fieldset>
{/if}

{if $content_extra->tags}

    <fieldset>
        <legend>Теги</legend>
        <small>
            {$content_extra->tags}
        </small>
    </fieldset>
{/if}