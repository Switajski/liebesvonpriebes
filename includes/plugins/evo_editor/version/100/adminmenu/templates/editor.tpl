<link type="text/css" rel="stylesheet" href="{$URL}templates/style.css" />
<div id="evo-editor">
    <div id="main-actions">
        <select id="theme">
            <option value="">--- Theme ausw&auml;hlen ---</option>
            {foreach from=$themes item=theme}
                {if isset($theme.theme)}
                    <option value="{$theme.theme}" data-template="{$theme.template}">{$theme.theme|ucfirst}</option>
                {/if}
            {/foreach}
        </select>
        <a href="#" id="refresh"><span class="glyphicon glyphicon-refresh"></span></a>
        {*<span class="check"><input type="checkbox" id="switch-skins" name="show-all-themes" value="0"><label for="switch-skins">Alle Themes anzeigen</label></span>*}
        <div class="pull-right">
            <button id="compile" class="btn btn-success">
                <i id="loader" class="fa fa-spin fa-spinner"></i>
                Theme kompilieren
            </button>
            {*<button id="minify" class="btn btn-success">JavaScript kompilieren</button>*}
        </div>
    </div>

    <div id="sidebar">
        <ul id="files"></ul>
    </div>

    <div id="editor"></div>

    <div id="actions">
        <button id="save" class="btn btn-primary">Datei speichern</button>
        <button id="reset" class="btn btn-danger pull-right">Datei auf Bootstrap-Standard zur&uuml;cksetzen</button>
    </div>

    <div id="messages" class="text-center">
        <span id="msg" class="label"></span>
    </div>
</div>

<div id="dialog" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div id="dialog-content" class="modal-body"></div>
            <div class="modal-footer">
                <span class="btn-group">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Abbrechen</button>
                    <button id="dialog-action" type="button" class="btn btn-danger"></button>
                </span>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.1.3/ace.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.1.3/theme-monokai.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.1.3/mode-less.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.1.3/ext-searchbox.js"></script>
<script type="text/javascript">
    var URL = '{$URL}api.php';
</script>
<script type="text/javascript" src="{$URL}script.js"></script>