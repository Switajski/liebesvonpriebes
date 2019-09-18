{include file='tpl_inc/seite_header.tpl' cTitel=#contactformSubject# cBeschreibung=#contanctformSubjectDesc#}
<div id="content">
    <form name="einstellen" method="post" action="kontaktformular.php">
        {$jtl_token}
        <input type="hidden" name="kKontaktBetreff" value="{if isset($Betreff->kKontaktBetreff)}{$Betreff->kKontaktBetreff}{/if}" />
        <input type="hidden" name="betreff" value="1" />
        <div class="panel panel-default">
            <div class="panel-header">
                <h3 class="panel-title"></h3>
            </div>
            <div class="panel-body">
                <div class="settings">
                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="cName">{#subject#}</label>
                        </span>
                        <input type="text" class="form-control" name="cName" id="cName" value="{if isset($Betreff->cName)}{$Betreff->cName}{/if}" tabindex="1" required />
                    </div>
                    {foreach name=sprachen from=$sprachen item=sprache}
                        {assign var="cISO" value=$sprache->cISO}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="cName_{$cISO}">{#showedName#} ({$sprache->cNameDeutsch})</label>
                            </span>
                            <input type="text" class="form-control" name="cName_{$cISO}" id="cName_{$cISO}" value="{if isset($Betreffname[$cISO])}{$Betreffname[$cISO]}{/if}" tabindex="2" />
                        </div>
                    {/foreach}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="cMail">{#mail#}</label>
                        </span>
                        <input type="text" class="form-control" name="cMail" id="cMail" value="{if isset($Betreff->cMail)}{$Betreff->cMail}{/if}" tabindex="3" required />
                    </div>
                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="cKundengruppen">{#restrictedToCustomerGroups#}</label>
                        </span>
                        <select class="form-control" name="cKundengruppen[]" multiple="multiple" id="cKundengruppen">
                            <option value="0" {if $gesetzteKundengruppen[0]}selected{/if}>{#allCustomerGroups#}</option>
                            {foreach name=kdgrp from=$kundengruppen item=kundengruppe}
                                {assign var="kKundengruppe" value=$kundengruppe->kKundengruppe}
                                <option value="{$kundengruppe->kKundengruppe}" {if isset($gesetzteKundengruppen[$kKundengruppe])}selected{/if}>{$kundengruppe->cName}</option>
                            {/foreach}
                        </select>
                        <span class="input-group-addon">{getHelpDesc cDesc=#multipleChoice#}</span>
                    </div>
                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="nSort">{#sortNo#}</label>
                        </span>
                        <input type="text" class="form-control" name="nSort" id="nSort" value="{if isset($Betreff->nSort)}{$Betreff->nSort}{/if}" tabindex="4" />
                    </div>
                </div>
            </div>
            <div class="panel-footer">
                <button type="submit" value="{#save#}" class="btn btn-primary"><i class="fa fa-save"></i> {#save#}</button>
            </div>
        </div>
    </form>
</div>