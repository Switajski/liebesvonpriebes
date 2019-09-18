{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="dbcheck"}
{include file='tpl_inc/seite_header.tpl' cTitel="Datenbank-Manager" cBeschreibung="<kbd>Tabellen und Views ({$tables|@count})</kbd>" cDokuURL=#dbcheckURL#}

{function table_scope_header table=null}
    <h2>Tabelle: {$table}
        <div class="btn-group btn-group-xs" role="group">
            <a href="dbmanager.php?table={$table}&token={$smarty.session.jtl_token}" class="btn btn-default"><span class="glyphicon glyphicon-equalizer"></span> Struktur</a>
            <a href="dbmanager.php?select={$table}&token={$smarty.session.jtl_token}" class="btn btn-default"><span class="glyphicon glyphicon-list"></span> Anzeigen</a>
        </div>
    </h2>
{/function}

{function filter_operator selected=null}
    {$ol = [ '=', '<', '>', '<=', '>=', '!=', 'LIKE', 'LIKE %%', 'REGEXP', 'IN', 'IS NULL', 'NOT LIKE', 'NOT REGEXP', 'NOT IN', 'IS NOT NULL', 'SQL' ]}
    {foreach $ol as $o}
        <option value="{$o|escape:'html'}"{if $selected && $selected == $o} selected="selected"{/if}>{$o|escape:'html'}</option>
    {/foreach}
{/function}

{function filter_row headers=[] col=null op=null val=null remove=true}
    <div class="fieldset-row" data-action="add-row">
        <select name="filter[where][col][]" class="form-control input-xs">
            <option value="">(beliebig)</option>
            {foreach $headers as $h}
                <option value="{$h}"{if $col == $h} selected="selected"{/if}>{$h}</option>
            {/foreach}
        </select>
        <select name="filter[where][op][]" class="form-control input-xs">
            {filter_operator selected=$op}
        </select>
        <input type="text" name="filter[where][val][]" class="form-control input-xs" value="{$val|escape:'html'}">
        <a href="#" data-dismiss="row">
            <i class="fa {if $remove}fa-trash text-danger{else}fa-plus text-success{/if}" aria-hidden="true"></i>
        </a>
    </div>
{/function}

{capture "filter_row_tpl" assign="filter_row_tpl_data"}
    {filter_row headers=array_keys($columns)}
{/capture}

<script>
var $add_row_tpl = $({$filter_row_tpl_data|strip|json_encode});
{if isset($info) && isset($info.statement)}var sql_query = {$info.statement|json_encode};{/if}

$(function() {
    var new_content = '<form action="dbmanager.php?command" method="POST">';
    new_content += '{$jtl_token}';
    console.log('new content: ', new_content);
    $search = $('#db-search');

    $('table.table-sticky-header').stickyTableHeaders({
        fixedOffset: $('.navbar-header')
    });
    
    $search.keyup(function () {
        var val = $(this).val();
        var count = filter_tables(val);
        
        if (count > 0) {
            $search.parent().removeClass('has-error');
        }
        else {
            $search.parent().addClass('has-error');
        }
    });

    add_row_listener();
    var $pnate = $('#pagination');

    $pnate.bootpag({
        total: $pnate.data('total'),
        page: $pnate.data('current'),
        maxVisible: 10,
        leaps: true,
        firstLastUse: true,
        first: '&larr;',
        last: '&rarr;',
        wrapClass: 'pagination pagination-sm',
        activeClass: 'active',
        disabledClass: 'disabled',
        nextClass: 'next',
        prevClass: 'prev',
        lastClass: 'last',
        firstClass: 'first',
    }).on('page', function(event, page){
        var p = get_params({ name: 'page', value: page });
        var url = location.pathname.split('/').slice(-1)[0];
        location.href = url + '?' + jQuery.param(p);
    });

    if ($('.query-code').length > 0) {
        highlight_sql($('.query-code'));
    }

    $(document).on('click', '.query .query-sub a', function () {
        if ($('#sql_query_edit').length) {
            return false;
        }

        var $inner_sql = $(this).parents('.query').find('code.sql');
        var old_text   = $inner_sql.html();

        var new_content = '<form action="dbmanager.php?command" method="POST">';
        new_content += '{$jtl_token}';
        new_content += '<div class="form-group"><textarea name="sql_query_edit" id="sql_query_edit">' + sql_query + '</textarea></div>';
        new_content += '<div class="form-group btn-group-xs last-child">';
        new_content += '    <button type="submit" id="sql_query_edit_save" class="btn btn-primary">OK</button>';
        new_content += '    <button type="button" id="sql_query_edit_discard" class="btn btn-default">Abbrechen</button>';
        new_content += '</div>';

        new_content += '</form>';

        var $editor_area = $('div#inline_editor');
        if ($editor_area.length === 0) {
            $editor_area = $('<div id="inline_editor_outer"></div>');
            $editor_area.insertBefore($inner_sql);
        }

        $editor_area.html(new_content);
        $inner_sql.hide();

        bindCodeMirrorToInlineEditor();
        return false;
    });

    $(document).on('click', "#sql_query_edit_discard", function () {
        var $divEditor = $('div#inline_editor_outer');
        $divEditor.siblings('code.sql').show();
        $divEditor.remove();
    });
});

function get_params(p) {
    var params = $('#filter form').serializeArray();

    for (var i = 0; i < params.length; i++) {
        if (params[i].name === 'page') {
            delete params[i];
        }
    }
    
    params.push(p);
    return params;
}

function add_row_listener() {
    $(document).on('click', '*[data-action="add-row"] > a', function(e) {
        var $row = $(this).parent('.fieldset-row');
        var $body = $row.parent('.fieldset-body');
        
        if ($row.is('.fieldset-row:first')) {
            $add_row_tpl
                .clone()
                .appendTo($body);
        }
        else {
            $row.remove();
        }

        e.preventDefault();
        return false;
    });
}

function filter_tables(value) {
    var rex = new RegExp(value, 'i');
    var $nav = $('.db-sidenav');
    var $items = $nav.find('li');
    
    $items.hide();
    $nav.unhighlight();

    var $found = $items.filter(function () {
        return rex.test($(this).text());
    });
    
    $found.show();
    if ($found.length > 0) {
        $nav.highlight(value);
    }
    
    return $found.length;
}

/*****************************************************************************************************/

function highlight_sql($base) {
    var $elm = $base.find('code.sql');
    $elm.each(function () {
        var $sql = $(this);
        var $div = $sql.find('div');
        if ($div.is(":visible")) {
            var $highlight = $('<div class="sql-highlight cm-s-default"></div>');
            $sql.append($highlight);
            if (typeof CodeMirror != 'undefined') {
                CodeMirror.runMode($sql.text(), 'text/x-mysql', $highlight[0]);
                $div.hide();
            }
        }
    });
}

function get_sql_editor($textarea, options, resize, lintOptions) {
    if ($textarea.length > 0 && typeof CodeMirror !== 'undefined') {

        // merge options for CodeMirror
        var defaults = {
            lineNumbers: true,
            matchBrackets: true,
            extraKeys: { "Ctrl-Space": "autocomplete" },
            hintOptions: { "completeSingle": false, "completeOnSingleClick": true },
            indentUnit: 4,
            mode: "text/x-mysql",
            lineWrapping: true,
            scrollbarStyle: 'simple',
            smartIndent: true,
            autofocus: true
        };

        if (CodeMirror.sqlLint) {
            $.extend(defaults, {
                gutters: ["CodeMirror-lint-markers"],
                lint: {
                    "getAnnotations": CodeMirror.sqlLint,
                    "async": true,
                    "lintOptions": lintOptions
                }
            });
        }

        $.extend(true, defaults, options);

        // create CodeMirror editor
        var codemirrorEditor = CodeMirror.fromTextArea($textarea[0], defaults);
        codemirrorEditor.setCursor($textarea.val().length);

        // allow resizing
        if (! resize) {
            resize = 'vertical';
        }
        var handles = '';
        if (resize === 'vertical') {
            handles = 'n, s';
        }
        if (resize === 'both') {
            handles = 'all';
        }
        if (resize === 'horizontal') {
            handles = 'e, w';
        }
        $(codemirrorEditor.getWrapperElement())
            .css('resize', resize)
            .resizable({
                handles: handles,
                resize: function() {
                    codemirrorEditor.setSize($(this).width(), $(this).height());
                }
            });
        // enable autocomplete
        // codemirrorEditor.on("inputRead", codemirrorAutocompleteOnInputRead);

        return codemirrorEditor;
    }
    return null;
}

CodeMirror.runMode = function(string, modespec, callback, options) {
    var mode = CodeMirror.getMode(CodeMirror.defaults, modespec);
    var ie = /MSIE \d/.test(navigator.userAgent);
    var ie_lt9 = ie && (document.documentMode == null || document.documentMode < 9);

    if (callback.nodeType == 1) {
        var tabSize = (options && options.tabSize) || CodeMirror.defaults.tabSize;
        var node = callback, col = 0;
        node.innerHTML = "";
        callback = function(text, style) {
            if (text == "\n") {
                node.appendChild(document.createTextNode(ie_lt9 ? '\r' : text));
                col = 0;
                return;
            }
            var content = "";
            for (var pos = 0;;) {
                var idx = text.indexOf("\t", pos);
                if (idx == -1) {
                    content += text.slice(pos);
                    col += text.length - pos;
                    break;
                } else {
                    col += idx - pos;
                    content += text.slice(pos, idx);
                    var size = tabSize - col % tabSize;
                    col += size;
                    for (var i = 0; i < size; ++i) content += " ";
                    pos = idx + 1;
                }
            }

            if (style) {
                var sp = node.appendChild(document.createElement("span"));
                sp.className = "cm-" + style.replace(/ +/g, " cm-");
                sp.appendChild(document.createTextNode(content));
            } else {
                node.appendChild(document.createTextNode(content));
            }
        };
    }

    var lines = CodeMirror.splitLines(string), state = (options && options.state) || CodeMirror.startState(mode);
    for (var i = 0, e = lines.length; i < e; ++i) {
        if (i) callback("\n");
        var stream = new CodeMirror.StringStream(lines[i]);
        if (!stream.string && mode.blankLine) mode.blankLine(state);
        while (!stream.eol()) {
            var style = mode.token(stream, state);
            callback(stream.current(), style, i, stream.start, state);
            stream.start = stream.pos;
        }
    }
};

function bindCodeMirrorToInlineEditor() {
    var $inline_editor = $('#sql_query_edit');
    if ($inline_editor.length > 0) {
        if (typeof CodeMirror !== 'undefined') {
            var height = $inline_editor.css('height');
            codemirror_inline_editor = get_sql_editor($inline_editor);
            codemirror_inline_editor.getWrapperElement().style.height = height;
            codemirror_inline_editor.refresh();
            codemirror_inline_editor.focus();
            $(codemirror_inline_editor.getWrapperElement())
                .bind('keydown', catchKeypressesFromSqlInlineEdit);
        } else {
            $inline_editor
                .focus()
                .bind('keydown', catchKeypressesFromSqlInlineEdit);
        }
    }
}

function catchKeypressesFromSqlInlineEdit(event) {
    // ctrl-enter is 10 in chrome and ie, but 13 in ff
    if (event.ctrlKey && (event.keyCode == 13 || event.keyCode == 10)) {
        $("#sql_query_edit_save").trigger('click');
    }
}

/*
$(function() {
    var offset = $('#paginator').offset();

    var paginator = $('#paginator input').bootstrapSlider({
        formatter: function(value) {
            return 'Seite: ' + value;
        }
    });

    paginator.on('slideStop', function(e) {
        $(paginator).bootstrapSlider('disable');
        var p = get_params({ name: 'page', value: e.value });
        var url = location.pathname.split('/').slice(-1)[0];
        location.href = url + '?' + jQuery.param(p);
    });
    
    $('#paginator')
        .css('left', offset.left)
        .addClass('paginator-bottom');
        
    //var slider = $('#paginator .slider');
    //slider.css('margin-left', (slider.width()/2) * -1);
    
    $(document).scroll(function() {
        var off = Math.max(0, offset.left - $(this).scrollLeft());
        $('#paginator')
            .css('left', off);
    });
});
*/
</script>

<div id="content" class="container-fluid">
    <div class="row">

        <div class="col-md-2">
            <div class="form-group">
                <input id="db-search" class="form-control" type="search" placeholder="Tabelle suchen">
            </div>
            <nav class="db-sidebar hidden-print hidden-xs hidden-sm">
                <ul class="nav db-sidenav">
                    {foreach $tables as $table}
                        <li><a href="dbmanager.php?select={$table@key}&token={$smarty.session.jtl_token}">{$table@key}</a></li>
                    {/foreach}
                </ul>
            </nav>
        </div>

        <div class="col-md-10">
            <ol class="simple-menu">
                <li><a href="dbmanager.php">Übersicht</a></li>
                <li><a href="dbmanager.php?command"><span class="glyphicon glyphicon-flash"></span> SQL Kommando</a></li>
                <li><a href="dbcheck.php">Konsistenz</a></li>
            </ol>
        
            {if $sub === 'command'}
                <h2>SQL Kommando</h2>

                <p class="text-muted">
                    <i class="fa fa-keyboard-o" aria-hidden="true"></i>
                    Code-Vervollständigung via <span class="label label-default">STRG+Leertaste</span> ausführen
                </p>
                
                {if isset($error)}
                    <div class="alert alert-danger" role="alert">
                        {get_class($error)}: <strong>{$error->getMessage()}</strong>
                    </div>
                {/if}

                <form action="dbmanager.php?command" method="POST">
                    {$jtl_token}
                    <div class="form-group">
                        <textarea name="query" id="query" class="codemirror sql" data-hint='{$jsTypo|json_encode}'>{if isset($info) && isset($info.statement)}{$info.statement}{/if}</textarea>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-share"></i> Ausführen</button>
                    </div>
                </form>
                
                <!-- ###################################################### -->
                {if isset($result) && !isset($result[0])}
                    <div class="alert alert-xs alert-success">
                        <p>Keine Datens&auml;tze</p>
                    </div>
                {elseif isset($result[0])}
                    {$headers = array_keys($result[0])}
                    <div class="table-responsive">
                        <table class="table table-striped table-condensed table-bordered table-hover table-sql table-sticky-header nowrap">
                            <thead>
                                <tr>
                                    {foreach $headers as $h}
                                        <th>{$h}</th>
                                    {/foreach}
                                </tr>
                            </thead>
                            {foreach $result as $d}
                                <tr class="text-vcenter">
                                    {foreach $headers as $h}
                                        {$value = $d[$h]|escape:'html'|truncate:100:'...'}
                                        <td class="data data-mixed{if $value == null} data-null{/if}"><span>{if $value == null}NULL{else}{$value}{/if}</span></td>
                                    {/foreach}
                                </tr>
                            {/foreach}
                        </table>
                    </div>
                {/if}
                <!-- ###################################################### -->

            {elseif $sub === 'default'}
                {if isset($tables) && $tables|@count > 0}
                    <div class="table-responsive">
                        <table class="table table-striped table-condensed table-bordered table-hover table-sticky-header">
                            <thead>
                            <tr>
                                <th>Tabelle</th>
                                <th class="text-center">Aktion</th>
                                <th class="text-center">Typ</th>
                                <th class="text-center">Kollation</th>
                                <th class="text-right">Datensätze</th>
                                <th class="text-right">Auto-Inkrement</th>
                            </tr>
                            </thead>
                            {foreach $tables as $table}
                                <tr class="text-vcenter{if count($definedTables) > 0 && !($table@key|in_array:$definedTables || $table@key|substr:0:8 === 'xplugin_')} warning{/if}" id="table-{$table@key}">
                                    <td><a href="dbmanager.php?select={$table@key}&token={$smarty.session.jtl_token}">{$table@key}</a></td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-xs" role="group">
                                            <a href="dbmanager.php?table={$table@key}&token={$smarty.session.jtl_token}" class="btn btn-default"><span class="glyphicon glyphicon-equalizer"></span> Struktur</a>
                                            <a href="dbmanager.php?select={$table@key}&token={$smarty.session.jtl_token}" class="btn btn-default"><span class="glyphicon glyphicon-list"></span> Anzeigen</a>
                                        </div>
                                    </td>
                                    <td class="text-center">{$table->Engine}</td>
                                    <td class="text-center">{$table->Collation}</td>
                                    <td class="text-right">{$table->Rows|number_format}</td>
                                    <td class="text-right">{$table->Auto_increment}</td>
                                </tr>
                            {/foreach}
                        </table>
                    </div>
                {/if}
            {elseif $sub === 'table'}
                {table_scope_header table=$selectedTable}
                <div class="row">
                    <div class="col-md-6">
                        <h3>Struktur</h3>
                        <table class="table table-striped table-condensed table-bordered table-hover table-sticky-header">
                            <thead>
                            <tr>
                                <th>Spalte</th>
                                <th>Typ</th>
                                <th>Kollation</th>
                            </tr>
                            </thead>
                            {foreach $columns as $column}
                                <tr class="text-vcenter">
                                    <th><span class="text-vcenter">{$column->Field}</span> {if $column->Extra === 'auto_increment'}<span class="label label-default text-vcenter"><abbr title="Auto-Inkrement">AI</abbr></span>{/if}</th>
                                    <td>{$column->Type} {if $column->Null === 'YES'}<i class="text-danger">NULL</i>{/if} {if $column->Default !== null}<strong class="text-muted">[{$column->Default}]</strong>{/if}</td>
                                    <td>{$column->Collation}</td>
                                </tr>
                            {/foreach}
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h3>Indizes</h3>
                        <table class="table table-striped table-condensed table-bordered table-hover table-sticky-header">
                            <thead>
                            <tr>
                                <th>Typ</th>
                                <th>Spalten</th>
                                <th>Name</th>
                            </tr>
                            </thead>
                            {foreach $indexes as $index}
                                <tr class="text-vcenter">
                                    <th>{$index->Index_type}</th>
                                    <td>{array_keys($index->Columns)|implode:'<strong>,</strong> '}</td>
                                    <td>{$index@key}</td>
                                </tr>
                            {/foreach}
                        </table>
                    </div>
                </div>
            {elseif $sub === 'select'}
                {table_scope_header table=$selectedTable}
                {$headers = array_keys($columns)}
                
                <style>
                    html {
                        background-color: #fff;
                    }
                </style>
                
                <div id="filter">
                    <form method="GET" action="dbmanager.php" data-sql={$info.statement|json_encode}>
                        <input type="hidden" name="token" value="{$smarty.session.jtl_token}">
                        <input type="hidden" name="select" value="{$selectedTable}">

                        <fieldset>
                            <legend>
                                <a href="#filter-where">Suche</a>
                            </legend>
                            
                            <div class="fieldset-body">
                                {if isset($filter.where.col) && $filter.where.col|@count > 0}
                                    {for $i=0 to count($filter.where.col) - 1}
                                        {filter_row headers=$headers col=$filter.where.col[$i] op=$filter.where.op[$i] val=$filter.where.val[$i] remove=$i}
                                    {/for}
                                {else}
                                    {filter_row headers=$headers remove=false}
                                {/if}
                            </div>
                        </fieldset>
                        
                        <fieldset>
                            <legend>Anzahl</legend>
                            <div class="fieldset-body">
                                <input type="number" id="filter-limit" name="filter[limit]" class="form-control input-xs" placeholder="Anzahl" value="{$filter.limit}" size="3">
                            </div>
                        </fieldset>

                        <fieldset>
                            <legend>Aktion</legend>
                            <div class="fieldset-body">
                                <button type="submit" class="btn btn-xs btn-primary">Daten anzeigen</button>
                            </div>
                        </fieldset>
                    </form>
                </div>
                
                <div class="query">
                    <div class="query-code">
                        <code class="sql"><div>{$info.statement}</div></code>
                    </div>
                    <div class="query-sub">
                        <span class="text-muted" title="Millisekunden"><i class="fa fa-clock-o" aria-hidden="true"></i> &nbsp;{"`$info.time*1000`"|number_format:2} ms</span>
                        <span class="text-muted"><i class="fa fa-database" aria-hidden="true"></i> &nbsp;{$count|number_format:0} Datens&auml;tze</span>
                        <a href="dbmanager.php?command&query={$info.statement|urlencode}">
                            <i class="fa fa-pencil" aria-hidden="true"></i> Bearbeiten
                        </a>
                    </div>
                </div>
                
                {if count($data) > 0}
                    <div class="table-responsive">
                        <table class="table table-striped table-condensed table-bordered table-hover table-sql table-sticky-header nowrap">
                            <thead>
                                <tr>
                                    {foreach $headers as $h}
                                        <th>{$h}</th>
                                    {/foreach}
                                </tr>
                            </thead>
                            {foreach $data as $d}
                                <tr class="text-vcenter">
                                    {foreach $headers as $h}
                                        {$value = $d[$h]}
                                        {$class = 'none'}
                                        {$info = $columns[$h]->Type_info}

                                        {if $info->Name|in_array:['varchar', 'tinytext', 'text', 'mediumtext', 'longtext']}
                                            {$class = 'str'}
                                            {$value = $value|escape:'html'|truncate:100:'...'}
                                        {elseif $info->Name|in_array:['float', 'decimal']}
                                            {$class = 'float'}
                                            {$decimals = (int)$info->Size[1]}
                                            {$value = $value|number_format:$decimals}
                                        {elseif $info->Name|in_array:['double']}
                                            {$class = 'float'}
                                            {$value = $value|number_format:2}
                                        {elseif $info->Name|in_array:['tinyint', 'smallint', 'mediumint', 'int', 'bigint']}
                                            {$class = 'int'}
                                        {elseif $info->Name|in_array:['date', 'datetime', 'time', 'timestamp', 'year']}
                                            {$class = 'date'}
                                            {*$default = ($value == '0000-00-00' || $value == '0000-00-00 00-00-00')*}
                                        {elseif $info->Name|in_array:['bit', 'char']}
                                            {$class = 'char'}
                                        {/if}

                                        <td class="data data-{$class}{if $value == null} data-null{/if}"><span>{if $value == null}NULL{else}{$value}{/if}</span></td>
                                    {/foreach}
                                </tr>
                            {/foreach}
                        </table>
                    </div>
                {else}
                    <div class="alert alert-xs alert-success">
                        <p>Keine Datens&auml;tze</p>
                    </div>
                {/if}

                {if $pages > 1}
                    <div id="pagination" class="pagination-static" data-total="{$pages}" data-current="{$page}"></div>
                {/if}
                
                {*if $pages > 1}
                    <div id="paginator" class="paginator">
                        <input type="text" data-slider-min="1" data-slider-max="{$pages}" data-slider-scale="logarithmic" data-slider-step="1" data-slider-value="{$page}" data-slider-handle="square" />
                    </div>
                {/if*}
            {/if}
        </div>
    </div>
</div>

{include file='tpl_inc/footer.tpl'}