{include file='tpl_inc/seite_header.tpl' cTitel=#newsCommentEdit#}
<div id="content" class="container-fluid2">
    <form name="umfrage" method="post" action="news.php" class="navbar-form">
        {$jtl_token}
        <input type="hidden" name="news" value="1" />
        <input type="hidden" name="nkedit" value="1" />
        {if isset($cTab)}
            <input type="hidden" name="tab" value="{$cTab}" />
        {/if}
        {if isset($nFZ) && $nFZ == 1}
            <input name="nFZ" type="hidden" value="1">
        {/if}
        {if isset($cSeite)}
            <input type="hidden" name="{if isset($cTab) && $cTab == 'aktiv'}s2{else}s1{/if}" value="{$cSeite}" />
        {/if}
        <input type="hidden" name="kNews" value="{$oNewsKommentar->kNews}" />
        <input type="hidden" name="kNewsKommentar" value="{$oNewsKommentar->kNewsKommentar}" />
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">{$oNewsKommentar->cName} - {#newsCommentEdit#}</h3>
            </div>
            <div class="table-responsive">
                <table class="list table" id="formtable">
                    <tr>
                        <td><label for="cName">{#newsHeadline#}</label></td>
                        <td><input id="cName" name="cName" class="form-control" type="text" value="{$oNewsKommentar->cName}" /></td>
                    </tr>
                    <tr>
                        <td><label for="cKommentar">{#newsText#}</label></td>
                        <td>
                            <textarea id="cKommentar" class="ckeditor form-control" name="cKommentar" rows="15" cols="60">{$oNewsKommentar->cKommentar}</textarea>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="panel-footer">
                <span class="btn-group">
                    <button name="newskommentarsavesubmit" type="submit" value="{#newsSave#}" class="btn btn-primary"><i class="fa fa-save"></i> {#newsSave#}</button>
                    <a class="btn btn-danger" href="news.php{if isset($cBackPage)}?{$cBackPage}{elseif isset($cTab)}?tab={$cTab}{/if}"><i class="fa fa-exclamation"></i> Abbrechen</a>
                </span>
            </div>
        </div>
    </form>
</div>