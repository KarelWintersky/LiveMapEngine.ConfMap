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
        // Еще, _кажется_, для версии 4.2.4 используется кастомная сборка с рядом встроенных плагинов, а 7* - чистая, с плагинами отдельно
        plugins: {
            // в блоке 0 перечислены общие для обеих версий плагины
            '*': [
                "advlist",
                "anchor",
                "autolink",
                "charmap",
                "code",
                "insertdatetime",
                "image",
                "link",
                "lists",
                "pagebreak",
                "preview",
                "responsivefilemanager",
                "searchreplace",
                "table",
                "visualblocks",
                "visualchars",
                "wordcount"
            ],
            4: [
                "contextmenu", // не требуется в 7.*, встроенный
                "hr", // для 7* нет?
                "legacyoutput",  // для 7* нет?
                "paste", // бесплатного аналога для 7.* нет
                "print", // для 7* нет?
                "textcolor", // в 7* вероятно, встроенный
                "template", // есть в 7*, отдельно, помечен как deprecated
            ],
            7: [
                "emoticons"
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

        charmap_append: [
            ["0x27f7", 'LONG LEFT RIGHT ARROW'],
            ["0x27fa", 'LONG LEFT RIGHT DOUBLE ARROW'],
            ["0x2600", 'sun'],
            ["0x2601", 'cloud']
        ],

        paste_as_text: true,
    };


    constructor() {
    }

    /**
     * Вычисляет высоту контейнера редактора
     *
     * @param $target
     * @returns {*|number|number}
     */
    calculateHeight($target) {
        if (tinyMCE.majorVersion == 7) {
            return 300; // тут нужны эксперименты!
        } else {
            return $target.data('height') || EditRegion.tinymce_defaults.height || 300;
        }
    }

    /**
     * Строит список плагинов в зависимости от версии
     *
     * @returns {{}}
     */
    makePluginsList() {
        return []
            .concat(
                EditRegion.tinymce_defaults.plugins[ '*' ]
            ).concat(
                EditRegion.tinymce_defaults.plugins[ tinyMCE.majorVersion ]
            );
    }



    /**
     * Создает инстанс редактора на основе параметров, переданных через data-атрибуты или опции.
     *
     * @param target
     * @param options
     */
    createInstance(target, options = { }) {
        let tinymce_defaults = EditRegion.tinymce_defaults;
        let tinymce_common_options = EditRegion.tinymce_common_options;

        let $target = $('#' + target);

        let height = this.calculateHeight($target);

        let toolbar
            = options.hasOwnProperty('toolbar')
            ? options.toolbar
            : tinymce_defaults.toolbar.simple;

        let contextmenu
            = options.hasOwnProperty('contextmenu')
            ? options.contextmenu
            : tinymce_defaults.contextmenu;

        let plugins = this.makePluginsList();

        let menubar
            = $target.data('menubar')
            ? $target.data('menubar')
            : (
                options.hasOwnProperty('menubar')
                    ? options.menubar
                    : tinymce_defaults.menubar
            );

        let filemanager_options = {
            relative_urls: false,
            document_base_url: "/",
            external_filemanager_path: "/frontend/filemanager/",

            title: "Responsive Filemanager",
            width: 980,
            height: window.innerHeight - 200,
        };

        let instance_options = Object.assign({
            selector: "#" + target,
            menubar: menubar,
            toolbar: toolbar,
            contextmenu: contextmenu,
            plugins: plugins,
            height: height,
        }, tinymce_common_options);

        if (tinyMCE.majorVersion < 5) {
            Object.assign(instance_options, {
                theme: "modern",
                skin: "lightgray",
                filemanager_options: filemanager_options
            });
        } else {
            Object.assign(instance_options, {
                setup: (editor) => {
                    editor.options.register('filemanager_options', {
                        processor: 'object',
                        default: filemanager_options
                    })
                }
            });
        }

        return tinymce.init(instance_options);
    }


}
