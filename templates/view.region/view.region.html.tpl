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
    </div>
{/if}
