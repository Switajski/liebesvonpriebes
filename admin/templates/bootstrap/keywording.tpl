{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="keywording"}
{include file='tpl_inc/seite_header.tpl' cTitel=#excludeKeywords# cBeschreibung=#keywordingDesc# cDokuURL=#keywordingURL#}
<div id="content" class="container-fluid">
    <div id="settings">
        <form name="login" method="post" action="keywording.php">
            {$jtl_token}
            <input type="hidden" name="keywording" value="1" />
            {foreach name=sprachen from=$sprachen item=sprache}
                <div class="panel panel-default">
                    <div class="panel-heading">
                        {assign var="cISO" value=$sprache->cISO}
                        <h3 class="panel-title">{$sprache->cNameDeutsch}</h3>
                    </div>
                    <div class="panel-body">
                        <div class="item">
                            <div class="name">
                                <label for="keywords_{$cISO}">{#excludeKeywords#} ({#spaceSeparated#})</label>
                            </div>
                            <div class="for">
                                <textarea id="keywords_{$cISO}" name="keywords_{$cISO}" rows="10" class="form-control p100">{$keywords[$cISO]}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            {/foreach}
            <p class="submit">
                <button type="submit" value="{#save#}" class="btn btn-primary"><i class="fa fa-save"></i> {#save#}</button>
            </p>
        </form>
    </div>
</div>
{include file='tpl_inc/footer.tpl'}