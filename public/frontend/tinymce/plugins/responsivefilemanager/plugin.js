/**
 * 2024-06-24 - добавлен новый код в openmanager()
 * 2024-06-25 - добавлена иконка для кнопки (её показ)
 */
tinymce.PluginManager.add('responsivefilemanager', function (editor) {
    function responsivefilemanager_onMessage(event) {

        if (editor.settings.external_filemanager_path.toLowerCase().indexOf(event.origin.toLowerCase()) === 0) {
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

    function openmanager()
	{
        let width = window.innerWidth - 20;
        let height = window.innerHeight - 40;
        if (width > 1800) width = 1800;
        if (height > 1200) height = 1200;
        if (width > 600) {
            let width_reduce = (width - 20) % 138;
            width = width - width_reduce + 10;
        }

        if (typeof editor.settings.filemanager_width !== "undefined" && editor.settings.filemanager_width) {
            width = editor.settings.filemanager_width;
        }
        if (typeof editor.settings.filemanager_height !== "undefined" && editor.settings.filemanager_height) {
            height = editor.settings.filemanager_height;
        }

        editor.focus(true);
        var title = "RESPONSIVE FileManager";
        if (typeof editor.settings.filemanager_title !== "undefined" && editor.settings.filemanager_title) {
            title = editor.settings.filemanager_title;
        }
        var akey = "key";
        if (typeof editor.settings.filemanager_access_key !== "undefined" && editor.settings.filemanager_access_key) {
            akey = editor.settings.filemanager_access_key;
        }
        var sort_by = "";
        if (typeof editor.settings.filemanager_sort_by !== "undefined" && editor.settings.filemanager_sort_by) {
            sort_by = "&sort_by=" + editor.settings.filemanager_sort_by;
        }
        var descending = "false";
        if (typeof editor.settings.filemanager_descending !== "undefined" && editor.settings.filemanager_descending) {
            descending = editor.settings.filemanager_descending;
        }
        var fldr = "";
        if (typeof editor.settings.filemanager_subfolder !== "undefined" && editor.settings.filemanager_subfolder) {
            fldr = "&fldr=" + editor.settings.filemanager_subfolder;
        }
        var crossdomain = "";
        if (typeof editor.settings.filemanager_crossdomain !== "undefined" && editor.settings.filemanager_crossdomain) {
            crossdomain = "&crossdomain=1";

            // Add handler for a message from ResponsiveFilemanager
            if (window.addEventListener) {
                window.addEventListener('message', responsivefilemanager_onMessage, false);
            } else {
                window.attachEvent('onmessage', responsivefilemanager_onMessage);
            }
        }

        const fileUrl
			= editor.settings.external_filemanager_path
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

    } // handler `openmanager()`

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
    //@todo: сделать это при наличии заданной опции типа `editor.settings.filemanager_close_outside_click`
    (function($) {
        'use strict';
        $(document).ready(function() {
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
        });
    })(jQuery);
});
