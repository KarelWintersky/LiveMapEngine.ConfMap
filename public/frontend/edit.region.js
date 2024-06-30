/*
Код отличается от LiveMap Engine
Код определений filemanager и старта tinyMCE надо скопировать в основной проект

Скрипты для подключения tinyMCE
*/
class EditRegion {

    used_tinymce_version = 7;

    tinymce_defaults = {
        TINYMCE_VERSION: version,

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
        menubar: "file edit insert view format table tools"
    };

    tinymce_common_options = {
        theme: "modern",
        skin: "lightgray",
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

        plugins: [
            "advlist lists autolink link image anchor responsivefilemanager charmap insertdatetime paste ",
            "searchreplace contextmenu code textcolor template hr pagebreak table print preview wordcount",
            "visualblocks visualchars legacyoutput"
        ],

        /*menubar: false,
        contextmenu: false,
        toolbar: false,*/

        charmap_append: [
            ["0x27f7", 'LONG LEFT RIGHT ARROW'],
            ["0x27fa", 'LONG LEFT RIGHT DOUBLE ARROW'],
            ["0x2600", 'sun'],
            ["0x2601", 'cloud']
        ],

        paste_as_text: true,

        // responsive filemanager
        relative_urls: false,
        document_base_url: "/",
        external_filemanager_path: "/frontend/filemanager/",

        // Кастомные настройки для размера и заголовка окна плагина responsive filemanager
        filemanager_title: "Responsive Filemanager",
        filemanager_width: 980,
        filemanager_height: window.innerHeight - 200,
    };



    constructor(version = 7) {
        this.used_tinymce_version = version;
    }


}
