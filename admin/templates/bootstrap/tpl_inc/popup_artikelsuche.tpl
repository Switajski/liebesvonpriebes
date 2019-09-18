<h1>Nach Artikel suchen</h1>
<fieldset>
    <input class="form-control" type="text" id="article_list_input" value="{if isset($cSearch)}{$cSearch}{/if}" autocomplete="off" />
    <div class="select_wrapper">
        <div class="search">
            <h2>Gefundene Artikel</h2>
            <select multiple="multiple" name="article_list_found">
            </select>
        </div>
        <div class="added">
            <h2>Gew&auml;hlte Artikel</h2>
            <select multiple="multiple" name="article_list_selected">
            </select>
        </div>
        <div class="clear"></div>
    </div>
    <div class="tcenter btn-group">
        <a href="#" class="btn btn-default" id="article_list_add"><i class="fa fa-check-square-o"></i> hinzuf&uuml;gen</a>
        <a href="#" class="btn btn-default" id="article_list_remove"><i class="fa fa-square-o"></i> entfernen</a>
        <a href="#" class="btn btn-primary" id="article_list_save"><i class="fa fa-save"></i> Speichern</a>
        <a href="#" class="btn btn-danger" id="article_list_cancel"><i class="fa fa-exclamation"></i> Abbrechen</a>
    </div>
</fieldset>