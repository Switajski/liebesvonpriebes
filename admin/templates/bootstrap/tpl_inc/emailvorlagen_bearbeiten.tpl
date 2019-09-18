{assign var="template" value=#template#}
{assign var="modify" value=#modify#}
{include file='tpl_inc/seite_header.tpl' cTitel=$template|cat: " "|cat:$Emailvorlage->cName|cat: " "|cat:$modify cBeschreibung=#emailTemplateModifyHint#}
<div id="content" class="container-fluid">
    <form name="vorlagen_aendern" method="post" action="emailvorlagen.php" enctype="multipart/form-data">
        {$jtl_token}
        <input type="hidden" name="Aendern" value="1" />
        {if isset($kPlugin) && $kPlugin > 0}
            <input type="hidden" name="kPlugin" value="{$kPlugin}" />
        {/if}
        <input type="hidden" name="kEmailvorlage" value="{$Emailvorlage->kEmailvorlage}" />
        <div id="settings" class="settings">
            {if $Emailvorlage->cModulId !== 'core_jtl_anbieterkennzeichnung'}
                <div class="settings panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Einstellungen</h3>
                    </div>
                    <div class="panel-body">
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="cEmailActive">{#emailActive#}</label>
                            </span>
                            <span class="input-group-wrap">
                                <select name="cEmailActive" id="cEmailActive" class="form-control">
                                    <option value="Y"{if isset($Emailvorlage->cAktiv) && $Emailvorlage->cAktiv === 'Y'} selected{/if}>
                                        Ja
                                    </option>
                                    <option value="N"{if isset($Emailvorlage->cAktiv) && $Emailvorlage->cAktiv === 'N'} selected{/if}>
                                        Nein
                                    </option>
                                </select>
                            </span>
                        </div>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="cEmailOut">{#emailOut#}</label>
                            </span>
                            <input class="form-control" id="cEmailOut" name="cEmailOut" type="text" value="{if isset($oEmailEinstellungAssoc_arr.cEmailOut)}{$oEmailEinstellungAssoc_arr.cEmailOut|escape}{/if}" />
                        </div>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="cEmailSenderName">{#emailSenderName#}</label>
                            </span>
                            <input class="form-control" id="cEmailSenderName" name="cEmailSenderName" type="text" value="{if isset($oEmailEinstellungAssoc_arr.cEmailSenderName)}{$oEmailEinstellungAssoc_arr.cEmailSenderName|escape}{/if}" />
                        </div>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="cEmailCopyTo">{#emailCopyTo#}</label>
                            </span>
                            <input class="form-control" id="cEmailCopyTo" name="cEmailCopyTo" type="text" value="{if isset($oEmailEinstellungAssoc_arr.cEmailCopyTo)}{$oEmailEinstellungAssoc_arr.cEmailCopyTo|escape}{/if}" />
                        </div>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="cMailTyp">{#mailType#}</label>
                            </span>
                            <span class="input-group-wrap">
                                <select name="cMailTyp" id="cMailTyp" class="form-control">
                                    <option value="text/html" {if $Emailvorlage->cMailTyp === 'text/html'}selected{/if}>
                                        text/html
                                    </option>
                                    <option value="text" {if $Emailvorlage->cMailTyp === 'text'}selected{/if}>text
                                    </option>
                                </select>
                            </span>
                        </div>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="nAKZ">{#emailAddAKZ#}</label>
                            </span>
                            <span class="input-group-wrap">
                                <select id="nAKZ" name="nAKZ" class="form-control">
                                    <option value="0"{if $Emailvorlage->nAKZ == '0'} selected{/if}>{#no#}</option>
                                    <option value="1"{if $Emailvorlage->nAKZ == '1'} selected{/if}>{#yes#}</option>
                                </select>
                            </span>
                        </div>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="nAFK">{#emailAddAGB#}</label>
                            </span>
                            <span class="input-group-wrap">
                                <select id="nAFK" name="nAGB" class="form-control">
                                    <option value="0"{if $Emailvorlage->nAGB == '0'} selected{/if}>{#no#}</option>
                                    <option value="1"{if $Emailvorlage->nAGB == '1'} selected{/if}>{#yes#}</option>
                                </select>
                            </span>
                        </div>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="nWRB">{#emailAddWRB#}</label>
                            </span>
                            <span class="input-group-wrap">
                                <select id="nWRB" name="nWRB" class="form-control">
                                    <option value="0"{if $Emailvorlage->nWRB == "0"} selected{/if}>{#no#}</option>
                                    <option value="1"{if $Emailvorlage->nWRB == "1"} selected{/if}>{#yes#}</option>
                                </select>
                            </span>
                        </div>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="nWRBForm">{#emailAddWRBForm#}</label>
                            </span>
                            <span class="input-group-wrap">
                                <select id="nWRBForm" name="nWRBForm" class="form-control">
                                    <option value="0"{if $Emailvorlage->nWRBForm == "0"} selected{/if}>{#no#}</option>
                                    <option value="1"{if $Emailvorlage->nWRBForm == "1"} selected{/if}>{#yes#}</option>
                                </select>
                            </span>
                        </div>
                    </div>
                </div>
            {else}
                <input type="hidden" name="cEmailActive" value="Y" />
                <input type="hidden" name="cMailTyp" value="text/html" />
            {/if}
            <div class="box_info panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Platzhalter (Beispiele)</h3>
                </div>
                <div class="panel-body">
                    <code>
                    <span class="elem">
                        <span class="name">{ldelim}$Kunde->cAnrede{rdelim}</span><br />
                        <span class="for">m</span><br />
                    </span>
                    <span class="elem">
                        <span class="name">{ldelim}$Kunde->cAnredeLocalized{rdelim}</span><br />
                        <span class="for">Herr</span><br />
                    </span>
                    <span class="elem">
                        <span class="name">{ldelim}$Kunde->cVorname{rdelim}</span><br />
                        <span class="for">Max</span><br />
                    </span>
                    <span class="elem">
                        <span class="name">{ldelim}$Kunde->cNachname{rdelim}</span><br />
                        <span class="for">Mustermann</span><br />
                    </span>
                    <span class="elem">
                        <span class="name">{ldelim}$Firma->cName{rdelim}</span><br />
                        <span class="for">Muster GmbH</span><br />
                    </span>
                    </code>
                </div>
            </div>
            {foreach name=sprachen from=$Sprachen item=sprache}
                <div class="box_info panel panel-default">
                    {assign var="kSprache" value=$sprache->kSprache}
                    <div class="panel-heading">
                        <h3 class="panel-title">Inhalt {$sprache->cNameDeutsch}</h3>
                    </div>
                    <div class="panel-body">
                        {if $Emailvorlage->cModulId !== 'core_jtl_anbieterkennzeichnung'}
                            <div class="item well">
                                <div class="name"><label for="cBetreff_{$kSprache}">{#subject#}</label></div>
                                <div class="for">
                                    <input class="form-control" style="width:400px" type="text" name="cBetreff_{$kSprache}" id="cBetreff_{$kSprache}"
                                           value="{if isset($Emailvorlagesprache[$kSprache]->cBetreff)}{$Emailvorlagesprache[$kSprache]->cBetreff|escape}{/if}" tabindex="1" />
                                </div>
                            </div>
                        {/if}
                        <div class="item well">
                            <div class="name"><label for="cContentHtml_{$kSprache}">{#mailHtml#}</label></div>
                            <div class="for">
                                <textarea class="codemirror smarty" id="cContentHtml_{$kSprache}" name="cContentHtml_{$kSprache}"
                                          style="width:99%" rows="20">{if isset($Emailvorlagesprache[$kSprache]->cContentHtml)}{$Emailvorlagesprache[$kSprache]->cContentHtml|escape:"html"}{/if}</textarea>
                            </div>
                        </div>
                        <div class="item well">
                            <div class="name"><label for="cContentText_{$kSprache}">{#mailText#}</label></div>
                            <div class="for">
                                <textarea class="codemirror smarty" id="cContentText_{$kSprache}" name="cContentText_{$kSprache}"
                                          style="width:99%" rows="20">{if isset($Emailvorlagesprache[$kSprache]->cContentText)}{$Emailvorlagesprache[$kSprache]->cContentText|escape:"html"}{/if}</textarea>
                            </div>
                        </div>
                        {if isset($Emailvorlagesprache[$kSprache]->cPDFS_arr) && $Emailvorlagesprache[$kSprache]->cPDFS_arr|@count > 0}
                            <div class="item">
                                <div class="name">
                                    {#currentFiles#}
                                    (<a href="emailvorlagen.php?kEmailvorlage={$Emailvorlage->kEmailvorlage}&kS={$kSprache}&a=pdfloeschen&token={$smarty.session.jtl_token}{if isset($kPlugin) && $kPlugin > 0}&kPlugin={$kPlugin}{/if}">{#deleteAll#}</a>)
                                </div>
                                <div class="for">
                                    {foreach name=pdfs from=$Emailvorlagesprache[$kSprache]->cPDFS_arr item=cPDF}
                                        {assign var="i" value=$smarty.foreach.pdfs.iteration-1}
                                        <div>
                                            <span class="pdf">{$Emailvorlagesprache[$kSprache]->cDateiname_arr[$i]}.pdf</span>
                                        </div>
                                    {/foreach}
                                </div>
                            </div>
                        {/if}
                        {if $Emailvorlage->cModulId !== 'core_jtl_anbieterkennzeichnung'}
                            {section name=anhaenge loop=4 start=1 step=1}
                                <div class="item well">
                                    <div class="name">
                                        <label for="pdf_{$smarty.section.anhaenge.index}_{$kSprache}">{#pdf#} {$smarty.section.anhaenge.index}</label>
                                    </div>
                                    <div class="for">
                                        {math equation="x-y" x=$smarty.section.anhaenge.index y=1 assign=loopdekr}
                                        <label for="dateiname_{$smarty.section.anhaenge.index}_{$kSprache}">{#filename#}</label>
                                        <input id="dateiname_{$smarty.section.anhaenge.index}_{$kSprache}"
                                           name="dateiname_{$smarty.section.anhaenge.index}_{$kSprache}"
                                           type="text"
                                           value="{if isset($Emailvorlagesprache[$kSprache]->cDateiname_arr[$loopdekr])}{$Emailvorlagesprache[$kSprache]->cDateiname_arr[$loopdekr]}{/if}"
                                           class="form-control{if count($cFehlerAnhang_arr) > 0}{if isset($cFehlerAnhang_arr[$kSprache][$smarty.section.anhaenge.index]) && $cFehlerAnhang_arr[$kSprache][$smarty.section.anhaenge.index] == 1} fieldfillout{/if}{/if}" />
                                        <input id="pdf_{$smarty.section.anhaenge.index}_{$kSprache}" name="pdf_{$smarty.section.anhaenge.index}_{$kSprache}" type="file" class="form-control" maxlength="2097152" style="margin-top:5px;" />
                                    </div>
                                </div>
                            {/section}
                        {/if}
                        </div>
                    </div>
            {/foreach}
            <div class="btn-group">
                <button type="submit" name="continue" value="0" class="btn btn-primary"><i class="fa fa-save"></i> {#save#}</button>
                <button type="submit" name="continue" value="1" class="btn btn-default">{#saveAndContinue#}</button>
            </div>
        </div>
    </form>
    {if isset($Emailvorlage->kEmailvorlage)}
        {getRevisions type='mail' key=$Emailvorlage->kEmailvorlage show=['cContentText','cContentHtml'] secondary=true data=$Emailvorlagesprache}
    {/if}
</div>