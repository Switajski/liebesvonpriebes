{include file='tpl_inc/seite_header.tpl' cTitel=#statusemail# cBeschreibung=#statusemailDesc# cDokuURL=#statusemailURL#}
<div id="content" class="container-fluid">
    <form name="einstellen" method="post" action="statusemail.php">
        {$jtl_token}
        <input type="hidden" name="einstellungen" value="1" />
        <div id="settings">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">{#settings#}</h3>
                </div>
                <div class="panel-body">
                    <div class="item input-group">
                        <span class="input-group-addon">
                            <label for="nAktiv">{#statusemailUse#}</label>
                        </span>
                        <span class="input-group-wrap">
                            <select class="form-control" name="nAktiv" id="nAktiv">
                                <option value="1" {if isset($oStatusemailEinstellungen->nAktiv) && $oStatusemailEinstellungen->nAktiv == 1}selected{/if}>Ja</option>
                                <option value="0" {if isset($oStatusemailEinstellungen->nAktiv) && $oStatusemailEinstellungen->nAktiv == 0}selected{/if}>Nein</option>
                            </select>
                        </span>
                        <span class="input-group-addon">
                            {getHelpDesc cDesc=#statusemailUseDesc#}
                        </span>
                    </div>

                    <div class="item input-group">
                        <span class="input-group-addon">
                            <label for="cEmail">{#statusemailEmail#}</label>
                        </span>
                        <span class="input-group-wrap">
                            <input class="form-control" type="text" name="cEmail" id="cEmail"
                                   value="{if isset($oStatusemailEinstellungen->cEmail)}{$oStatusemailEinstellungen->cEmail}{/if}"
                                   tabindex="1">
                        </span>
                        <span class="input-group-addon">
                            {getHelpDesc cDesc=#statusemailEmailDesc#}
                        </span>
                    </div>

                    <div class="item input-group">
                        <span class="input-group-addon">
                            <label for="cIntervall">{#statusemailIntervall#}</label>
                        </span>
                        <select name="cIntervall_arr[]" id="cIntervall" multiple="multiple" class="form-control multiple"
                                size="3">
                            {foreach $oStatusemailEinstellungen->cIntervallMoeglich_arr as $key => $nIntervallMoeglich}
                                <option value="{$nIntervallMoeglich}"
                                        {if $nIntervallMoeglich|in_array:$oStatusemailEinstellungen->nIntervall_arr}selected{/if}>
                                    {$key}
                                </option>
                            {/foreach}
                        </select>
                        <span class="input-group-addon">
                            {getHelpDesc cDesc=#statusemailIntervallDesc#}
                        </span>
                    </div>

                    <div class="item input-group">
                        <span class="input-group-addon">
                            <label for="cInhalt">{#statusemailContent#}</label>
                        </span>
                        <select name="cInhalt_arr[]" id="cInhalt" multiple="multiple" class="form-control multiple"
                                size="15">
                            {foreach $oStatusemailEinstellungen->cInhaltMoeglich_arr as $key => $nInhaltMoeglich}
                                <option value="{$nInhaltMoeglich}"
                                        {if $nInhaltMoeglich|in_array:$oStatusemailEinstellungen->nInhalt_arr}selected{/if}>
                                    {$key}
                                </option>
                            {/foreach}
                        </select>
                        <span class="input-group-addon">
                            {getHelpDesc cDesc=#statusemailContentDesc#}
                        </span>
                    </div>
                </div>
                <div class="panel-footer">
                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {#statusemailSave#}</button>
                        <button type="submit" class="btn btn-default" name="action" value="sendnow">
                            <i class="fa fa-envelope-o"></i> E-Mail-Berichte jetzt senden</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>