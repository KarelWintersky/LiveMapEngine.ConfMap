<!DOCTYPE html>
<html lang="ru">
<head>
    <title>{$title}</title>

    {*<script src="/frontend/jquery/jquery-3.2.1_min.js"></script>*}
    {*<script src="/frontend/livemap/NotifyMessages.js"></script>*}
    <style>
        .content-center {
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: center;
            padding-top: 10%;
            text-align: center;
        }
        .left-align {
            text-align: left;
        }
        input[required] {
            border: 1px solid teal ;
            border-radius: 5px;
        }
    </style>

    <script type="module" data-comment="XZ Notify (via helper) + XZ Notify CSS (ver 2025-02-21)">
        import { XZNotifyHelper } from "/frontend/xz-notify/xz-notify-helper.js";

        const flash_messages = {$flash_messages|json_encode|default:"{ }"};
        document.addEventListener('DOMContentLoaded', function() {
            XZNotifyHelper(flash_messages);
        });
    </script>

    <script>
        /**
         * Attach action-redirect actions (from dash-dash)
         */
        document.addEventListener("DOMContentLoaded", function() {
            const elements = document.querySelectorAll("[data-action='redirect']");
            elements.forEach(function(element) {
                element.addEventListener('click', function(event) {
                    event.preventDefault();
                    event.stopPropagation();

                    let url = element.getAttribute('data-url');
                    if (!url) {
                        return false;
                    }

                    let confirmMessage = element.getAttribute('data-confirm-message') || '';
                    if (confirmMessage.length > 0 && !confirm(confirmMessage)) {
                        return false;
                    }

                    let target = element.getAttribute('data-target') || '';
                    if (target === "_blank") {
                        const newWindow = window.open(url, '_blank');
                        if (newWindow) newWindow.focus(); // Проверяем, что новое окно успешно открылось
                    } else {
                        window.location.assign(url);
                    }
                    return false;
                });
            });
        });
    </script>
</head>
<body>
<div class="content-center">
    <div>
        {if $_config.auth.is_logged_in}
        Вы уже залогинены <br><br> <strong>{$_config.auth.username} ({$_config.auth.email})<strong> <br><br>

        <button
                type="button"
                data-action="redirect"
                data-url="/"
                style="font-size: large">К карте Конфедерации</button>

        {else}

        <form method="post" action="{Arris\AppRouter::getRouter('callback.form.login')}" class="left-align">
            <input type="hidden" name="action" value="auth" />
            <table>
                <tr>
                    <td>E-Mail: </td>
                    <td><input type="text" placeholder="E-Mail" name="email" value="" required tabindex="1" autofocus/></td>
                </tr>
                <tr>
                    <td>Password:&nbsp;&nbsp;&nbsp;</td>
                    <td><input type="password" placeholder="пароль" name="password" value="" required tabindex="2" /></td>
                </tr>
                <tr>
                    <td colspan="2">
                        <br>
                        <input type="submit" value="Login >>>" tabindex="3">
                    </td>
                </tr>
            </table>
        </form>
        {/if}
    </div>
</div>
</body>
</html>
