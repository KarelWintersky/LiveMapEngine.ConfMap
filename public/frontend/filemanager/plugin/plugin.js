/**
 * 2024-06-24 - добавлен новый код в openmanager()
 * 2024-06-25 - добавлена иконка для кнопки (её показ)
 * 2024-06-30 - единая точка входа!!!
 */
tinymce.PluginManager.add('responsivefilemanager', function (editor) {

    /**
     * Вычисляет путь, как - зависит от версии tinyMCE
     *
     * @returns {string}
     */
    function getExternalFileManagerPath() {
        if (tinyMCE.majorVersion < 5) {
            return editor.settings.filemanager_options.external_filemanager_path;
        } else {
            let external_filemanager_path = editor.options.get('filemanager_options');
            return external_filemanager_path.external_filemanager_path;
        }
    }

    /**
     *
     *
     * @param event
     */
    function responsivefilemanager_onMessage(event) {
        let external_filemanager_path = getExternalFileManagerPath();

        if (external_filemanager_path.toLowerCase().indexOf(event.origin.toLowerCase()) === 0) {
            if (event.data.sender === 'responsivefilemanager') {
                tinymce.activeEditor.insertContent(event.data.html);
                tinymce.activeEditor.windowManager.close();

                // Remove event listener for a message from ResponsiveFilemanager
                if (window.removeEventListener) {
                    window.removeEventListener('message', responsivefilemanager_onMessage, false);
                } else {
                    window.detachEvent('onmessage', responsivefilemanager_onMessage);
                }
            }
        }
    }

    function openmanager_() {
        if (tinyMCE.majorVersion == 7) {
            openmanager_720()
        } else {
            openmanager_424();
        }
    }

    /**
     * Хэндлер открытия окна файлменеджера
     */
    function openmanager()
    {
        let options;
        if (tinyMCE.majorVersion < 5) {
            options = editor.settings.filemanager_options;
        } else {
            options = editor.options.get('filemanager_options') || { };
        }

        let width = window.innerWidth - 20;
        let height = window.innerHeight - 40;
        if (width > 1800) width = 1800;
        if (height > 1200) height = 1200;
        if (width > 600) {
            let width_reduce = (width - 20) % 138;
            width = width - width_reduce + 10;
        }

        if (options.width) {
            width = options.width;
        }
        if (options.height) {
            height = options.height;
        }

        editor.focus(true);

        let title = options.title || "RESPONSIVE FileManager";
        let akey = options.access_key || "key";
        let sort_by = options.sort_by ? "&sort_by=" + options.sort_by : "";
        let descending = options.descending || "false";
        let fldr = options.subfolder ? "&fldr=" + options.subfolder : "";

        let crossdomain = "";
        if (options.crossdomain !== "undefined" && options.crossdomain) {
            crossdomain = "&crossdomain=1";

            // Add handler for a message from ResponsiveFilemanager
            if (window.addEventListener) {
                window.addEventListener('message', responsivefilemanager_onMessage, false);
            } else {
                window.attachEvent('onmessage', responsivefilemanager_onMessage);
            }
        }

        const fileUrl
            = options.external_filemanager_path
            + 'dialog.php?type=4&descending='
            + descending
            + sort_by
            + fldr
            + crossdomain
            + '&lang=ru'
            + '&akey=' + akey
        ;

        if (tinymce.majorVersion < 5) {
            win = editor.windowManager.open({
                title: title,
                file: fileUrl,
                width: width,
                height: height,
                inline: 1,
                resizable: true,
                maximizable: true
            });
        } else {
            win = editor.windowManager.openUrl({
                title: title,
                url: fileUrl,
                width: width,
                height: height,
                inline: 1,
                resizable: true,
                maximizable: true
            });
        }
    }

    function openmanager_720() {
        let options = editor.options.get('filemanager_options') || { };

        let width = window.innerWidth - 20;
        let height = window.innerHeight - 40;
        if (width > 1800) width = 1800;
        if (height > 1200) height = 1200;
        if (width > 600) {
            let width_reduce = (width - 20) % 138;
            width = width - width_reduce + 10;
        }

        if (options.width) {
            width = options.width;
        }
        if (options.height) {
            height = options.height;
        }

        editor.focus(true);

        let title = options.title || "RESPONSIVE FileManager";
        let akey = options.access_key || "key";
        let sort_by = options.sort_by ? "&sort_by=" + options.sort_by : "";
        let descending = options.descending || "false";
        let fldr = options.subfolder ? "&fldr=" + options.subfolder : "";

        let crossdomain = "";
        if (options.crossdomain !== "undefined" && options.crossdomain) {
            crossdomain = "&crossdomain=1";

            // Add handler for a message from ResponsiveFilemanager
            if (window.addEventListener) {
                window.addEventListener('message', responsivefilemanager_onMessage, false);
            } else {
                window.attachEvent('onmessage', responsivefilemanager_onMessage);
            }
        }

        const fileUrl
            = options.external_filemanager_path
            + 'dialog.php?type=4&descending='
            + descending
            + sort_by
            + fldr
            + crossdomain
            + '&lang=ru'
            + '&akey=' + akey
        ;
        win = editor.windowManager.openUrl({
            title: title,
            url: fileUrl,
            width: width,
            height: height,
            inline: 1,
            resizable: true,
            maximizable: true
        });
    }

    function openmanager_424() {
        let width = window.innerWidth - 20;
        let height = window.innerHeight - 40;
        if (width > 1800) width = 1800;
        if (height > 1200) height = 1200;
        if (width > 600) {
            let width_reduce = (width - 20) % 138;
            width = width - width_reduce + 10;
        }

        if (typeof editor.settings.filemanager_options.width !== "undefined" && editor.settings.filemanager_options.width) {
            width = editor.settings.filemanager_options.width;
        }
        if (typeof editor.settings.filemanager_options.height !== "undefined" && editor.settings.filemanager_options.height) {
            height = editor.settings.filemanager_options.height;
        }

        editor.focus(true);
        var title = "RESPONSIVE FileManager";
        if (typeof editor.settings.filemanager_options.title !== "undefined" && editor.settings.filemanager_options.title) {
            title = editor.settings.filemanager_options.title;
        }
        var akey = "key";
        if (typeof editor.settings.filemanager_options.access_key !== "undefined" && editor.settings.filemanager_options.access_key) {
            akey = editor.settings.filemanager_options.access_key;
        }
        var sort_by = "";
        if (typeof editor.settings.filemanager_options.sort_by !== "undefined" && editor.settings.filemanager_options.sort_by) {
            sort_by = "&sort_by=" + editor.settings.filemanager_options.sort_by;
        }
        var descending = "false";
        if (typeof editor.settings.filemanager_options.descending !== "undefined" && editor.settings.filemanager_options.descending) {
            descending = editor.settings.filemanager_options.descending;
        }
        var fldr = "";
        if (typeof editor.settings.filemanager_options.subfolder !== "undefined" && editor.settings.filemanager_options.subfolder) {
            fldr = "&fldr=" + editor.settings.filemanager_options.subfolder;
        }
        var crossdomain = "";
        if (typeof editor.settings.filemanager_options.crossdomain !== "undefined" && editor.settings.filemanager_options.crossdomain) {
            crossdomain = "&crossdomain=1";

            // Add handler for a message from ResponsiveFilemanager
            if (window.addEventListener) {
                window.addEventListener('message', responsivefilemanager_onMessage, false);
            } else {
                window.attachEvent('onmessage', responsivefilemanager_onMessage);
            }
        }

        const fileUrl
            = editor.settings.filemanager_options.external_filemanager_path
            + 'dialog.php?type=4&descending='
            + descending
            + sort_by
            + fldr
            + crossdomain
            + '&lang=' + editor.settings.language
            + '&akey=' + akey
        ;

        if (tinymce.majorVersion < 5) {
            win = editor.windowManager.open({
                title: title,
                file: fileUrl,
                width: width,
                height: height,
                inline: 1,
                resizable: true,
                maximizable: true
            });
        } else {
            win = editor.windowManager.openUrl({
                title: title,
                url: fileUrl,
                width: width,
                height: height,
                inline: 1,
                resizable: true,
                maximizable: true
            });
        }

    }

    if (tinymce.majorVersion < 5) {
        editor.addButton('responsivefilemanager', {
            icon: 'browse',
            image: tinymce.baseURL + '/plugins/responsivefilemanager/img/insertfile.gif',
            tooltip: 'Insert file',
            shortcut: 'Ctrl+E',
            onClick: openmanager
        });

        editor.addShortcut('Ctrl+E', '', openmanager);

        editor.addMenuItem('responsivefilemanager', {
            icon: 'browse',
            text: 'Insert file',
            shortcut: 'Ctrl+E',
            onClick: openmanager,
            context: 'insert'
        });

    } else {
        editor.ui.registry.addButton('responsivefilemanager', {
            icon: 'browse',
            image: tinymce.baseURL + '/plugins/responsivefilemanager/img/insertfile.gif',
            tooltip: 'Insert file',
            shortcut: 'Ctrl+E',
            onAction: openmanager
        });

        editor.addShortcut('Ctrl+E', '', openmanager);

        editor.ui.registry.addMenuItem('responsivefilemanager', {
            icon: 'browse',
            text: 'Insert file',
            shortcut: 'Ctrl+E',
            onAction: openmanager,
            context: 'insert'
        });
    }

    (function($) {
        'use strict';
        $(document).ready(function() {
            // удаляем всякий рекламный мусор от tinyMCE 7+
            $('.tox-promotion').remove();
            $('.tox-statusbar__branding').remove();

            if (tinyMCE.majorVersion < 7) {
                // отслеживает событие клика ЗА пределы окна RespFileManager - закрывает попап
                // https://wordpress.stackexchange.com/questions/177843/close-tinymce-plugin-window-on-click-away
                $(document)
                    .on('click', '#mce-modal-block', function() {
                        tinyMCE.activeEditor.windowManager.close();
                    })
                /*.on("keyup", this, function (e) {
                    console.log("Escape pressed and released");
                    let keycode = ((typeof e.keyCode != 'undefined' && e.keyCode) ? e.keyCode : e.which);
                    if (keycode === 27) {
                        tinyMCE.activeEditor.windowManager.close();
                    }
                })*/
                ;
            }
        });
    })(jQuery);
});
