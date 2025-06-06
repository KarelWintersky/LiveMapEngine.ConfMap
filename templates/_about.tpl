<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>{$html_title}</title>
    <meta name="version" content="VERSION_PLACEHOLDER">

    {include file="_common/favicon_defs.tpl"}
    {include file="_common/opengraph.tpl"}

    {*<script src="/js/confmap.js" data-comment="the-map-data"></script>*}
    <style>
        @font-face {
            font-display: swap;
            font-family: 'Lato';
            font-style: normal;
            font-weight: 400;
            src: url('/frontend/fonts/lato-v24-latin-regular.woff2') format('woff2');
        }
        body {
            font-family: 'Lato', serif;
            background-color: #7ab5d3;
        }
        .float-right {
            float: right;
        }
        h2, h4 {
            text-align: center;

        }
        .centered-block {
            width: 50%;
            max-width: 800px;
            margin: 0 auto;
            background-color: oldlace;
            padding: 20px;
            text-align: justify;
        }

    </style>
</head>
<body>
<div class="float-right">
    {if $_config.auth.is_logged_in}
            <a href="{Arris\AppRouter::getRouter('view.form.logout')}">
                <img src="/frontend/images/logout.svg" width="64" alt="logout">
            </a>
            {else}
            <a href="{Arris\AppRouter::getRouter('view.form.login')}">
                <img src="/frontend/images/login.svg" width="64" alt="login">
            </a>
            {/if}
</div>

<div class="centered-block">
    {if $_config.auth.is_logged_in}
        <div style="text-align: right;">
            Logged as: {$_config.auth.username} ({$_config.auth.email})
            <br>
        </div>
        <hr>
    {/if}

    <h2>Конфедерация Человечества</h2>
    <h4>(второй сезон)</h4>
    <hr>

    <p>Добро пожаловать на нашу веб-страницу, посвященную звездной карте Конфедерации Человечества!</p>

    <p>На этой карте показаны звездные системы, которые были открыты исследовательскими кораблями Службы Геологической Разведки,
        а потом колонизированы звездными кораблями, снаряженными Советом Колонизации.
    </p>
    <p>
        Наша карта представляет собой динамическую 2D-модель звездного кластера и позволяет вам исследовать звезды, планеты и другие
        объекты колонизированного космоса с невероятной детализацией.
    </p>

    <p>
        Наша звездная карта - это дань упорному труду и отваге исследователей и колонистов, которые расширяют границы человеческого
        присутствия во Вселенной. Это важный инструмент для любого, кто интересуется историей Колонизации,
        исследованием космоса или просто хочет узнать больше о нашем месте в этой необъятной, удивительной вселенной.
    </p>

    <p>
        Присоединяйтесь к нам в этом увлекательном путешествии.<br><br><br>
    </p>
    <h3 style="text-align: center;">

        <a href="/" style=" text-decoration: none;border-bottom: 1px dotted #0d88c1">
        Добро пожаловать в Конфедерацию Человечества!
        </a>
    </h3>
    <br><br><br>

    <div style="float: right; font-size: smaller; font-style: italic">
        Мне было лень выдумывать, я попросил нейросеть и чуть-чуть подправил результат.
    </div>
    <br>

</div>

</body>
</html>

{* -eof- *}