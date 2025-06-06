/**
 * Хэлпер для XZNotify
 */
/*
<script type="module" data-comment="XZ Notify (via helper) + XZ Notify CSS (ver 2025-02-21)">
    import { XZNotifyHelper } from "/assets/xz-notify-helper.js";

    const flash_messages = {$flash_messages|json_encode|default:"{ }"};
    document.addEventListener('DOMContentLoaded', function() {
        XZNotifyHelper(flash_messages);
    });
</script>
*/
import XZNotify from '/frontend/xz-notify/xz-notify.min.js';
export function XZNotifyHelper(dataset) {
    const xz_default_options = {
        expire: 3000,
        position: 'ne',
        closeable: true
    };
    if (!Array.isArray(dataset) || dataset.length < 1) {
        return false;
    }
    const css = document.createElement('link');
    css.rel = 'stylesheet';
    css.href = '/frontend/xz-notify/xz-notify.css';
    document.head.appendChild(css);

    for (const message of dataset) {
        for (const [key, value] of Object.entries(message)) {
            let notification = null;
            switch (key) {
                case 'success': {
                    notification = XZNotify.create(value, Object.assign({}, xz_default_options, {type: 'info'}));
                    break;
                }
                case 'error': {
                    notification = XZNotify.create(value, Object.assign({}, xz_default_options, {type: 'error'}));
                    break;
                }
                default: {
                    notification = XZNotify.create(value, Object.assign({}, xz_default_options, {type: 'debug'}));
                    break;
                }
            }
            document.body.appendChild(notification);
        }
    }
}
