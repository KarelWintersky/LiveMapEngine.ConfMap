window.theMap = {
    {if $JSBuilderError}"JSBuilderError": "{$JSBuilderError}",{/if}

    "map": {
        "id"                :   "{$map.alias}",
        "title"             :   "{$map.title}",
        "description"       :   "{$map.description|default:''}",
        "type"              :   "{$map.type}",
        "imagefile"         :   "/storage/{$map.alias}/{$map.imagefile}",
        "width"             :   {$map.width},
        "height"            :   {$map.height},
        "orig_x"            :   {$map.ox},
        "orig_y"            :   {$map.oy},
    },

    {if $source_image}
    "image": {

        "file"              :   "/storage/{$map.alias}/{$source_image.file}",
        "width"             :   {$source_image.width},
        "height"            :   {$source_image.height},
        "orig_x"            :   {$source_image.ox},
        "orig_y"            :   {$source_image.oy},
    },
    {/if}

    "display": {
        "zoom"              :   {$display.zoom},
        "zoom_min"          :   {$display.zoom_min},
        "zoom_max"          :   {$display.zoom_max},
        "zoom_mode"         :   "{$display.zoom_mode|default:'slider'}",
        "background_color"  :   "{$display.background_color}",

        {if $display.custom_css}"custom_css" : "{$display.custom_css}", {/if}

        "maxbounds"         :   {$maxbounds|json_encode},

        "focus": {
            "animate_duration"  : {$display.focus_animate_duration},
            "highlight_color"   : "{$display.focus_highlight_color}",
            "timeout"           : {$display.focus_timeout},
        },




    },

    "display_defaults": {
        "region": {
            "empty"         : {$display_defaults.region.empty|json_encode},
            "empty_hover"   : {$display_defaults.region.empty_hover|json_encode},
            "present"       : {$display_defaults.region.present|default:[]|json_encode},
            "present_hover" : {$display_defaults.region.present_hover|default:[]|json_encode},
        },
        "poi": {
            "any"           : {$display_defaults.poi.any|json_encode},
        }
    },

    "layers": {

    {foreach $layers as $layer}

        "{$layer.id}": {
            "id" : "{$layer.id}",
            "hint" : "{$layer.hint}",
            "zoom" : {$layer.zoom},
            "zoom_min" : {$layer.zoom_min},
            "zoom_max" : {$layer.zoom_max},
        },

    {/foreach}
    },

    "regions": {

    {foreach $regions as $region}

        "{$region.id}": {
            "id"        : "{$region.id}",
            "type"      : "{$region.type}",
            "coords"    : {$region.js},
            "layer"     : "{$region.layer}",
            "label"     : "{$region.label}",

            {* вот эти стили должны присваиваться региону на основании: дефолтных настроек карты, переопределений слоя и наличия контента в регионе *}
            {* сейчас используется общая настройка для карты *}

            {*"style_default": { },
            "style_hover": { },
            "style_icon": { },*}

            {if $region.fillColor}"fillColor" : "{$region.fillColor}",{/if}

            {if $region.fillOpacity}"fillOpacity": {$region.fillOpacity}, {/if}

            {if $region.fillRule}"fillRule": "{$region.fillRule}",{/if}

            {if $region.borderColor}"borderColor": "{$region.borderColor}",{/if}

            {if $region.borderWidth}"borderWidth": "{$region.borderWidth}",{/if}

            {if $region.borderOpacity}"borderOpacity": "{$region.borderOpacity}",{/if}

            {if $region.title}"title"     : "{$region.title}",{/if}

            {if $region.edit_date}"edit_date" : "{$region.edit_date}",{/if}

            {if $region.desc}"desc": "{$region.desc}",{/if}

            {if $region.radius}"radius"    : "{$region.radius}",{/if}

            {if $region.is_excludelists}"is_excludelists": "{$region.is_excludelists}",{/if}

            {if $region.interactive}"interactive": {$region.interactive|json_encode}{/if}

        },

    {/foreach}

    }
};

