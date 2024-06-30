/*
Код отличается от LiveMap Engine
Код определений filemanager и старта tinyMCE надо скопировать в основной проект

Скрипты для подключения tinyMCE
*/
class EditRegion {

    /**
     * Некоторые настройки tinyMCE по-умолчанию
     *
     * @todo: add markdown and simple configs
     */
    static tinymce_defaults = {
        height: 300,
        toolbar: {
            simple: [
                "bold italic underline strikethrough | fontsizeselect | bullist numlist | responsivefilemanager | image charmap | link unlink anchor | | pastetext removeformat | preview"
            ],
            advanced: [
                "undo redo | bold italic underline subscript superscript strikethrough | fontsizeselect styleselect | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist | ",
                "responsivefilemanager image | template table charmap | link unlink anchor | pastetext removeformat | preview"
            ]
        },
        contextmenu: "link image responsivefilemanager | inserttable cell row column deletetable | charmap",
        menubar: "file edit insert view format table tools",
        // разные списки плагинов для разных версий
        plugins: {
            4: [
                "advlist lists autolink link image anchor responsivefilemanager charmap insertdatetime paste ",
                "searchreplace contextmenu code textcolor template hr pagebreak table print preview wordcount",
                "visualblocks visualchars legacyoutput"
            ],
            7: [
                "advlist", "lists", "autolink", "link", "image", "anchor", "responsivefilemanager", "charmap", "insertdatetime",
                "searchreplace", "code", "pagebreak", "table", "preview", "wordcount", "visualblocks", "visualchars", "emoticons"
            ]
        },
    };

    /**
     * Шаблон для конфигурации инстанса tinyMCE
     */
    static tinymce_common_options = {
        language: 'ru',

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

        forced_root_block: "",
        force_br_newlines: true,
        force_p_newlines: false,

        insertdatetime_formats: [
            "%d.%m.%Y", "%H:%m", "%d/%m/%Y"
        ],

        // там есть какая-то разница между списками плагинов для 4 и 7 версий. Но ключевая - в седьмой нужен массив строк,
        // а в 4-ой можно строкой через пробелы

        /*plugins: [
            "advlist lists autolink link image anchor responsivefilemanager charmap insertdatetime paste ",
            "searchreplace contextmenu code textcolor template hr pagebreak table print preview wordcount",
            "visualblocks visualchars legacyoutput"
        ],*/
        /*plugins: [
            "advlist", "lists", "autolink", "link", "image", "anchor", "responsivefilemanager", "charmap", "insertdatetime",
            "searchreplace", "code", "pagebreak", "table", "preview", "wordcount", "visualblocks", "visualchars", "emoticons"
        ],*/

        charmap_append: [
            ["0x27f7", 'LONG LEFT RIGHT ARROW'],
            ["0x27fa", 'LONG LEFT RIGHT DOUBLE ARROW'],
            ["0x2600", 'sun'],
            ["0x2601", 'cloud']
        ],

        paste_as_text: true,
    };


    constructor() {
        this.test = '123';
    }


}
