<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>{$html_title}</title>

    {include file="_common/favicon_defs.tpl"}
    {include file="_common/opengraph.tpl"}

    <script src="/js/confmap.js" data-comment="the-map-data"></script>
    <style>
        /* pt-sans-regular - cyrillic_latin */
        @font-face {
            font-display: swap; /* Check https://developer.mozilla.org/en-US/docs/Web/CSS/@font-face/font-display for other options. */
            font-family: 'PT Sans';
            font-style: normal;
            font-weight: 400;
            src: url('/frontend/fonts/pt-sans-v17-cyrillic_latin-regular.woff2') format('woff2'); /* Chrome 36+, Opera 23+, Firefox 39+, Safari 12+, iOS 10+ */
        }

        /* pt-sans-italic - cyrillic_latin */
        @font-face {
            font-display: swap; /* Check https://developer.mozilla.org/en-US/docs/Web/CSS/@font-face/font-display for other options. */
            font-family: 'PT Sans';
            font-style: italic;
            font-weight: 400;
            src: url('/frontend/fonts/pt-sans-v17-cyrillic_latin-italic.woff2') format('woff2'); /* Chrome 36+, Opera 23+, Firefox 39+, Safari 12+, iOS 10+ */
        }

        /* pt-sans-700 - cyrillic_latin */
        @font-face {
            font-display: swap; /* Check https://developer.mozilla.org/en-US/docs/Web/CSS/@font-face/font-display for other options. */
            font-family: 'PT Sans';
            font-style: normal;
            font-weight: 700;
            src: url('/frontend/fonts/pt-sans-v17-cyrillic_latin-700.woff2') format('woff2'); /* Chrome 36+, Opera 23+, Firefox 39+, Safari 12+, iOS 10+ */
        }

        /* pt-sans-700italic - cyrillic_latin */
        @font-face {
            font-display: swap; /* Check https://developer.mozilla.org/en-US/docs/Web/CSS/@font-face/font-display for other options. */
            font-family: 'PT Sans';
            font-style: italic;
            font-weight: 700;
            src: url('/frontend/fonts/pt-sans-v17-cyrillic_latin-700italic.woff2') format('woff2'); /* Chrome 36+, Opera 23+, Firefox 39+, Safari 12+, iOS 10+ */
        }


        body {
            font-family: 'PT Sans', serif;
        }
        fieldset {
            float: right;
        }
        h2, h4 {
            text-align: center;

        }

    </style>
</head>
<body>
<fieldset>
    <legend>Auth</legend>
    {if $_config.auth.is_logged_in}
    Вы уже залогинены <br><br> <strong>{$_config.auth.username} ({$_config.auth.email})<strong> <br><br>
            <a href="{Arris\AppRouter::getRouter('view.form.logout')}">Logout</a>
            {else}
            <a href="{Arris\AppRouter::getRouter('view.form.login')}">Вход</a>
            {/if}
</fieldset>

<h2>Карта Конфедерации Человечества</h2>
<h4>второй сезон</h4>

<div>
    Тут какой-то текст
</div>


</body>
</html>
{* -eof- *}