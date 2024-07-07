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

        {* сюда вкладываем отображение всех остальных полей *}
    </div>
{/if}
