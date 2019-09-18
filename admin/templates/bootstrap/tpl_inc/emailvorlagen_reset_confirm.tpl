{include file='tpl_inc/seite_header.tpl' cTitel=#emailTemplates#}
<div id="content" class="container-fluid">
    <form method="post" action="emailvorlagen.php">
        {$jtl_token}
        <input type="hidden" name="resetEmailvorlage" value="1" />
        {if isset($kPlugin) && $kPlugin > 0}
            <input type="hidden" name="kPlugin" value="{$kPlugin}" />
        {/if}
        <input type="hidden" name="kEmailvorlage" value="{$oEmailvorlage->kEmailvorlage}" />

        <div class="alert alert-danger">
            <p><strong>Vorsicht</strong>: Ihre Emailvorlage wird zur&uuml;ckgesetzt!</p>

            <p>Wollen Sie die Emailvorlage "<b>{$oEmailvorlage->cName}</b>" wirklich zur&uuml;cksetzen?</p>
        </div>
        <div class="btn-group">
            <button name="resetConfirmJaSubmit" type="submit" value="{#resetEmailvorlageYes#}" class="btn btn-danger"><i class="fa fa-check"></i> {#resetEmailvorlageYes#}</button>
            <button name="resetConfirmNeinSubmit" type="submit" value="{#resetEmailvorlageNo#}" class="btn btn-info"><i class="fa fa-close"></i> {#resetEmailvorlageNo#}</button>
        </div>
    </form>
</div>