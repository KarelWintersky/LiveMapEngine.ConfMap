/*
Код отличается от LiveMap Engine
Код определений filemanager и старта tinyMCE надо скопировать в основной проект

Скрипты для подключения tinyMCE
*/
class EditRegion {
    V7_TEXTAREA_ADDITIONAL_HEIGHT = 120; // на самом деле где-то 117 для одного ряда кнопок, но пускай будет размер ПРИМЕРНЫЙ

    /**
     * Некоторые настройки tinyMCE по-умолчанию
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
        // https://www.tiny.cloud/docs/tinymce/6/plugins/
        plugins: {
            0: [
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
                // "responsivefilemanager",
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

        /*
        Custom plugins can be added to a TinyMCE instance by either:
        - Using external_plugins: Use the external_plugins option to specify the URL-based location of the entry point file for the plugin.
        - Copy code into plugins folder: Copy the entry point file (and any other files) into the plugins folder of the distributed TinyMCE code. The plugin can then be used by including it in the list of plugins specified by the plugins option.
        see: https://www.tiny.cloud/docs/tinymce/latest/creating-a-plugin/#using-custom-plugins-with-tinymce

        Поэтому при использовании плагина вот так его нужно отключить в списке tinymce_defaults.plugins[0]
         */
        external_plugins: {
            "responsivefilemanager": "/frontend/filemanager/plugin/plugin.js"
        }
    };


    constructor() {
    }

    /**
     * Вычисляет высоту контейнера редактора
     *
     * @param $target
     * @param selected_toolbar = 'simple'
     * @returns {*|number|number}
     */
    calculateHeight($target, selected_toolbar = 'simple') {
        if (tinyMCE.majorVersion < 5) {
            return $target.data('height') || EditRegion.tinymce_defaults.height || 300;
        } else {
            // Для версии 7 высота контейнера считается не как высота textArea, а высота всего редактора.
            // Поэтому добавляем еще ~120 пикселей с учетом одного ряда кнопок.
            //@todo: нужно учитывать количество строк в тулбаре
            let h = $target.data('height') || EditRegion.tinymce_defaults.height || 300; // тут нужны эксперименты!
            h += this.V7_TEXTAREA_ADDITIONAL_HEIGHT;
            return h;
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
                EditRegion.tinymce_defaults.plugins[ 0 ]
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
