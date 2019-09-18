{assign var=title value=#pluginverwaltungLicenceKeyInput#}
{include file='tpl_inc/seite_header.tpl' cTitel=$title|cat:": "|cat:$oPlugin->cName cBeschreibung=#pluginverwaltungDesc#}
<div id="content" class="container-fluid">
    <form name="pluginverwaltung" method="post" action="pluginverwaltung.php">
        {$jtl_token}
        <input type="hidden" name="pluginverwaltung_uebersicht" value="1" />
        <input type="hidden" name="lizenzkeyadd" value="1" />
        <input type="hidden" name="kPlugin" value="{$kPlugin}" />

        <div class="input-group">
            <span class="input-group-addon">
                <label for="cKey">{#pluginverwaltungLicenceKey#}</label>
            </span>
            <input id="cKey" placeholder="{#pluginverwaltungLicenceKey#}" class="form-control" name="cKey" type="text" value="{if isset($oPlugin->cLizenz)}{$oPlugin->cLizenz}{/if}" />
            <span class="input-group-btn">
                <button name="speichern" type="submit" value="{#pluginBtnSave#}" class="btn btn-primary"><i class="fa fa-save"></i> {#pluginBtnSave#}</button>
            </span>
        </div>
    </form>
</div>
