<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=2">
    <title>{$mediafilesSubdir} - Media Browser</title>
    <link rel="stylesheet" href="{$templateUrl}/css/jquery-ui.min.css">
    <link rel="stylesheet" href="{$templateUrl}/css/jquery-ui.theme.min.css">
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow: hidden;
        }
    </style>
    <script data-main="{$templateUrl}/js/elfinder.client.js" src="{$templateUrl}/js/require.js"></script>
    <script>
        define('elFinderConfig', {
            elementId: 'elfinder',
            // Documentation for elFinder client options:
            // https://github.com/Studio-42/elFinder/wiki/Client-configuration-options
            options: {
                // connector URL (REQUIRED)
                url : 'elfinder.php',
                defaultView: 'icons',
                height: '100%',
                resizable: false,
                customData: {
                    token: '{$smarty.session.jtl_token}',
                    jtl_token: '{$smarty.session.jtl_token}',
                    mediafilesType: '{$mediafilesType}',
                },
                commandsOptions : {
                    upload: {
                        ui: 'uploadbutton',
                    },
                },
                uiOptions: {
                    toolbar: [
                        ['info', 'quicklook', 'upload'],
                        ['rm', 'duplicate', 'rename'],
                        ['view'],
                        ['help']
                    ]
                },
                contextmenu: {
                    navbar: [],
                    cwd: ['reload', 'back', '|', 'upload', 'paste', '|', 'info'],
                    files: [
                        'getfile', 'quicklook', '|', 'download', '|', 'duplicate', 'rm', 'rename', '|', 'info'
                    ],
                },
                getFileCallback: function(file, fm) {
                    {if $isCKEditor}
                    window.opener.CKEDITOR.tools.callFunction({$CKEditorFuncNum}, file.url);
                    {else}
                    window.opener.elfinder.getFileCallback(file.url);
                    {/if}
                    window.close();
                },
            },
        });
    </script>
</head>
<body>
<!-- Element where elFinder will be created (REQUIRED) -->
<div id="elfinder"></div>
</body>
</html>
