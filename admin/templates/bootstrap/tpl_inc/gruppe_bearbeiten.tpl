{assign var="cTitel" value=#gruppeNeu#}
{if isset($oAdminGroup) && $oAdminGroup->kAdminlogingruppe > 0}
    {assign var="cTitel" value=#gruppeBearbeiten#}
{/if}

{include file='tpl_inc/seite_header.tpl' cTitel=$cTitel cBeschreibung=#benutzerDesc#}
<div id="content" class="container-fluid">
    <form class="settings navbar-form" action="benutzerverwaltung.php" method="post">
        {$jtl_token}
        <input type="hidden" name="tab" value="group_view" />
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Allgemein</h3>
            </div>
            <div class="panel-body">
                <div class="input-group{if isset($cError_arr.cGruppe)} error{/if}">
                    <span class="input-group-addon"><label for="cGruppe">Name</label></span>
                    <input class="form-control" type="text" name="cGruppe" id="cGruppe" value="{if isset($oAdminGroup->cGruppe)}{$oAdminGroup->cGruppe}{/if}" />
                    {if isset($cError_arr.cGruppe)}<span class="input-group-addon error" title="Bitte ausf&uuml;llen"><i class="fa fa-exclamation-triangle"></i></span>{/if}
                </div>
                <div class="input-group{if isset($cError_arr.cBeschreibung)} error{/if}">
                    <span class="input-group-addon"><label for="cBeschreibung">Beschreibung</label></span>
                    <input class="form-control" type="text" id="cBeschreibung" name="cBeschreibung" value="{if isset($oAdminGroup->cBeschreibung)}{$oAdminGroup->cBeschreibung}{/if}" />
                    {if isset($cError_arr.cBeschreibung)}<span class="input-group-addon error" title="Bitte ausf&uuml;llen"><i class="fa fa-exclamation-triangle"></i></span>{/if}
                </div>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Berechtigungen</h3>
            </div>
            <div class="panel-body">
                {foreach from=$oAdminDefPermission_arr item=oGroup name="perm"}
                    <div id="settings-{$smarty.foreach.perm.iteration}" class=" col-md-4">
                        <div class="panel panel-default">
                            <div class="panel-heading"><h3 class="panel-title">{$oGroup->cName}</h3></div>
                            <div class="perm_list panel-body">
                                {foreach from=$oGroup->oPermission_arr item=oPerm}
                                    <div class="input">
                                    <input type="checkbox" name="perm[]" value="{$oPerm->cRecht}" id="{$oPerm->cRecht}" {if isset($cAdminGroupPermission_arr) && is_array($cAdminGroupPermission_arr)}{if $oPerm->cRecht|in_array:$cAdminGroupPermission_arr}checked="checked"{/if}{/if} />
                                    <label for="{$oPerm->cRecht}" class="perm">
                                        {if $oPerm->cBeschreibung|strlen > 0}{$oPerm->cBeschreibung}{if isset($bDebug) && $bDebug} - {$oPerm->cRecht}{/if}{else}{$oPerm->cRecht}{/if}
                                    </label>
                                    </div>
                                {/foreach}
                            </div>
                            <div class="panel-footer">
                                <input type="checkbox" onclick="checkToggle('#settings-{$smarty.foreach.perm.iteration}');" id="cbtoggle-{$smarty.foreach.perm.iteration}" /> <label for="cbtoggle-{$smarty.foreach.perm.iteration}">Alle ausw&auml;hlen</label>
                            </div>
                        </div>
                    </div>
                {/foreach}
            </div>
            <div class="panel-footer">
                <input type="checkbox" onclick="AllMessages(this.form);" id="ALLMSGS" name="ALLMSGS" /> <label for="ALLMSGS">Alle ausw&auml;hlen</label>
            </div>
        </div>

        <div class="panel-footer">
            <div class="btn-group">
                <input type="hidden" name="action" value="group_edit" />
                {if isset($oAdminGroup) && $oAdminGroup->kAdminlogingruppe > 0}
                    <input type="hidden" name="kAdminlogingruppe" value="{$oAdminGroup->kAdminlogingruppe}" />
                {/if}
                <input type="hidden" name="save" value="1" />
                <button type="submit" value="{$cTitel}" class="btn btn-primary"><i class="fa fa-save"></i> Speichern</button>
                <a class="btn btn-danger" href="benutzerverwaltung.php?tab=group_view"><i class="fa fa-exclamation"></i> Abbrechen</a>
            </div>
        </div>
    </form>
</div>

