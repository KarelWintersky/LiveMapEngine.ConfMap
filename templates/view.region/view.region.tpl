<!DOCTYPE html>
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
    {assign var="first_dt_width" value=($view_mode == 'infobox') ? 200 : 250} {* конечно это лучше делать в шаблонизаторе *}

    <div class="region-content">

        <h2>{$region_title}</h2>
        {$region_text|default:'Нет данных'}

        {if $is_display_extra_content}
            {include file="view.region/view.region_extra.tpl"}
        {/if}
    </div>
    {* ===== СТИЛИ ===== *}

{/if}