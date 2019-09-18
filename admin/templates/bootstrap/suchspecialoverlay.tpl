{config_load file="$lang.conf" section="suchspecialoverlay"}
{include file='tpl_inc/header.tpl'}

{include file='tpl_inc/seite_header.tpl' cTitel=#suchspecialoverlay# cBeschreibung=#suchspecialoverlayDesc# cDokuURL=#suchspecialoverlayUrl#}
<div id="content" class="container-fluid">
    <div class="block">
        {if isset($Sprachen) && $Sprachen|@count > 1}
            <form name="sprache" method="post" action="suchspecialoverlay.php" class="inline_block">
                {$jtl_token}
                <input type="hidden" name="sprachwechsel" value="1" />
                <div class="input-group p25 left" style="margin-right: 20px;">
                    <span class="input-group-addon">
                        <label for="{#changeLanguage#}">{#changeLanguage#}</label>
                    </span>
                    <span class="input-group-wrap last">
                        <select id="{#changeLanguage#}" name="kSprache" class="form-control selectBox" onchange="document.sprache.submit();">
                            {foreach name=sprachen from=$Sprachen item=sprache}
                                <option value="{$sprache->kSprache}" {if $sprache->kSprache==$smarty.session.kSprache}selected{/if}>{$sprache->cNameDeutsch}</option>
                            {/foreach}
                        </select>
                    </span>
                </div>
            </form>
        {/if}
        <form name="suchspecialoverlay" method="post" action="suchspecialoverlay.php" class="inline_block">
            {$jtl_token}
            <div class="p25 input-group">
                <span class="input-group-addon">
                    <label for="{#suchspecial#}">{#suchspecial#}</label>
                </span>
                <input type="hidden" name="suchspecialoverlay" value="1" />
                <span class="input-group-wrap last">
                    <select name="kSuchspecialOverlay" class="form-control selectBox" id="{#suchspecial#}" onchange="document.suchspecialoverlay.submit();">
                        {foreach name=suchspecialoverlay from=$oSuchspecialOverlay_arr item=oSuchspecialOverlayTMP}
                            <option value="{$oSuchspecialOverlayTMP->kSuchspecialOverlay}" {if $oSuchspecialOverlayTMP->kSuchspecialOverlay == $oSuchspecialOverlay->kSuchspecialOverlay}selected{/if}>{$oSuchspecialOverlayTMP->cSuchspecial}</option>
                        {/foreach}
                    </select>
                </span>
            </div>
        </form>
    </div>

    {if $oSuchspecialOverlay->kSuchspecialOverlay > 0}
        <form name="einstellen" method="post" action="suchspecialoverlay.php" enctype="multipart/form-data" onsubmit="checkfile(event)">
            {$jtl_token}
            <input type="hidden" name="suchspecialoverlay" value="1" />
            <input type="hidden" name="kSuchspecialOverlay" value="{$oSuchspecialOverlay->kSuchspecialOverlay}" />
            <input type="hidden" name="speicher_einstellung" value="1" />

            <div class="clearall">
                <div class="no_overflow panel panel-default" id="settings">
                    <div class="panel-body">
                        {if $oSuchspecialOverlay->cBildPfad|strlen > 0}
                            <img src="{$shopURL}/{$PFAD_SUCHSPECIALOVERLAY}{$oSuchspecialOverlay->cBildPfad}?rnd={$cRnd}" style="margin-bottom: 15px;" />
                        {/if}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="nAktiv">{#suchspecialoverlayActive#}</label>
                            </span>
                            <span class="input-group-wrap">
                                <select name="nAktiv" id="nAktiv" class="form-control combo">
                                    <option value="1"{if $oSuchspecialOverlay->nAktiv == 1} selected{/if}>Ja
                                    </option>
                                    <option value="0"{if $oSuchspecialOverlay->nAktiv == 0} selected{/if}>Nein
                                    </option>
                                </select>
                            </span>
                            <span class="input-group-addon">
                                {getHelpDesc cDesc=#suchspecialoverlayActiveDesc#}
                            </span>
                        </div>

                        <div class="input-group file-input">
                            <span class="input-group-addon">
                                <label for="cSuchspecialOverlayBild">{#suchspecialoverlayFileName#}</label>
                            </span>
                            <span class="input-group-wrap">
                                <input class="form-control" type="file" name="cSuchspecialOverlayBild" accept="image/jpeg,image/gif,image/png,image/bmp" id="cSuchspecialOverlayBild" value="" tabindex="1" />
                            </span>
                            <span class="input-group-addon">
                                {getHelpDesc cDesc=#suchspecialoverlayFileNameDesc#}
                            </span>
                        </div>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="nPrio">{#suchspecialoverlayPrio#}</label>
                            </span>
                            <span class="input-group-wrap">
                                <select id="nPrio" name="nPrio" class="form-control combo">
                                    <option value="-1"></option>
                                    {section name=prios loop=$nSuchspecialOverlayAnzahl start=1 step=1}
                                        <option value="{$smarty.section.prios.index}"{if $smarty.section.prios.index == $oSuchspecialOverlay->nPrio} selected{/if}>{$smarty.section.prios.index}</option>
                                    {/section}
                                </select>
                            </span>
                            <span class="input-group-addon">
                                {getHelpDesc cDesc=#suchspecialoverlayPrioDesc#}
                            </span>
                        </div>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="nTransparenz">{#suchspecialoverlayClarity#}</label>
                            </span>
                            <span class="input-group-wrap">
                                <select name="nTransparenz" class="form-control combo" id="nTransparenz">
                                    {section name=transparenz loop=101 start=0 step=1}
                                        <option value="{$smarty.section.transparenz.index}"{if $smarty.section.transparenz.index == $oSuchspecialOverlay->nTransparenz} selected{/if}>{$smarty.section.transparenz.index}</option>
                                    {/section}
                                </select>
                            </span>
                            <span class="input-group-addon">
                                {getHelpDesc cDesc=#suchspecialoverlayClarityDesc#}
                            </span>
                        </div>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="nGroesse">{#suchspecialoverlaySize#}</label>
                            </span>
                            <span class="input-group-wrap">
                                <input id="nGroesse" class="form-control" name="nGroesse" type="number" value="{$oSuchspecialOverlay->nGroesse}" />
                            </span>
                            <span class="input-group-addon">
                                {getHelpDesc cDesc=#suchspecialoverlaySizeDesc#}
                            </span>
                        </div>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="nPosition">{#suchspecialoverlayPosition#}</label>
                            </span>
                            <span class="input-group-wrap">
                                <select name="nPosition" id="nPosition" class="combo form-control"{if !empty($isDeprecated)} disabled="disabled"{/if}>
                                    <option value="1"{if $oSuchspecialOverlay->nPosition === '1'} selected{/if}>
                                        oben-links
                                    </option>
                                    <option value="2"{if $oSuchspecialOverlay->nPosition === '2'} selected{/if}>
                                        oben
                                    </option>
                                    <option value="3"{if $oSuchspecialOverlay->nPosition === '3'} selected{/if}>
                                        oben-rechts
                                    </option>
                                    <option value="4"{if $oSuchspecialOverlay->nPosition === '4'} selected{/if}>
                                        rechts
                                    </option>
                                    <option value="5"{if $oSuchspecialOverlay->nPosition === '5'} selected{/if}>
                                        unten-rechts
                                    </option>
                                    <option value="6"{if $oSuchspecialOverlay->nPosition === '6'} selected{/if}>
                                        unten
                                    </option>
                                    <option value="7"{if $oSuchspecialOverlay->nPosition === '7'} selected{/if}>
                                        unten-links
                                    </option>
                                    <option value="8"{if $oSuchspecialOverlay->nPosition === '8'} selected{/if}>
                                        links
                                    </option>
                                    <option value="9"{if $oSuchspecialOverlay->nPosition === '9'} selected{/if}>
                                        zentriert
                                    </option>
                                </select>
                            </span>
                            <span class="input-group-addon">
                                {getHelpDesc cDesc=#suchspecialoverlayPositionDesc#}
                            </span>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <button type="submit" value="{#save#}" class="btn btn-primary"><i class="fa fa-save"></i> {#save#}</button>
                    </div>
                </div>
            </div>
        </form>
    {/if}
</div>
<script type="text/javascript">
    {literal}
    var file2large = false;

    function checkfile(e){
        e.preventDefault();
        if (!file2large){
            document.einstellen.submit();
        }
    }

    $(document).ready(function () {
        $('form #cSuchspecialOverlayBild').change(function(e){
            $('form div.alert').slideUp();
            var filesize= this.files[0].size;
            {/literal}
            var maxsize = {$nMaxFileSize};
            {literal}
            if (filesize >= maxsize) {
                $('.input-group.file-input').after('<div class="alert alert-danger"><i class="fa fa-warning"></i> Die Datei ist gr&ouml;&szlig;er als das Uploadlimit des Servers.</div>').slideDown();
                file2large = true;
            } else {
                $('form div.alert').slideUp();
                file2large = false;
            }
        });
    });
    {/literal}
</script>
{include file='tpl_inc/footer.tpl'}