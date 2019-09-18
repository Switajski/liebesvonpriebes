{config_load file="$lang.conf" section="branding"}
{include file='tpl_inc/header.tpl'}

{include file='tpl_inc/seite_header.tpl' cTitel=#branding# cBeschreibung=#brandingDesc# cDokuURL=#brandingUrl#}
<div id="content" class="container-fluid">
    <div class="block">
        <form name="branding" method="post" action="branding.php">
            {$jtl_token}
            <input type="hidden" name="branding" value="1" />
            <div class="input-group p25 left">
                <span class="input-group-addon">
                    <label for="{#brandingActive#}">{#brandingPictureKat#}:</label>
                </span>
                <span class="input-group-wrap">
                    <select name="kBranding" class="form-control selectBox" id="{#brandingActive#}" onchange="document.branding.submit();">
                        {foreach name=brandings from=$oBranding_arr item=oBrandingTMP}
                            <option value="{$oBrandingTMP->kBranding}" {if $oBrandingTMP->kBranding == $oBranding->kBrandingTMP}selected{/if}>{$oBrandingTMP->cBildKategorie}</option>
                        {/foreach}
                    </select>
                </span>
            </div>
        </form>
    </div>

    {if $oBranding->kBrandingTMP > 0}
        <div class="no_overflow" id="settings">
            <form name="einstellen" method="post" action="branding.php" enctype="multipart/form-data">
                {$jtl_token}
                <input type="hidden" name="branding" value="1" />
                <input type="hidden" name="kBranding" value="{$oBranding->kBrandingTMP}" />
                <input type="hidden" name="speicher_einstellung" value="1" />
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Branding f&uuml;r {$oBranding->cBildKategorie} bearbeiten</h3>
                    </div>
                    <div class="panel-body">
                        {if $oBranding->cBrandingBild|strlen > 0}
                            <div class="thumbnail">
                                <img src="{$shopURL}/{$PFAD_BRANDINGBILDER}{$oBranding->cBrandingBild}?rnd={$cRnd}" alt="" />
                            </div>
                        {/if}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="nAktiv">{#brandingActive#}</label>
                            </span>
                            <span class="input-group-wrap">
                                <select name="nAktiv" id="nAktiv" class="form-control combo">
                                    <option value="1"{if $oBranding->nAktiv == 1} selected{/if}>Ja</option>
                                    <option value="0"{if $oBranding->nAktiv == 0} selected{/if}>Nein</option>
                                </select>
                            </span>
                            <span class="input-group-addon">{getHelpDesc cDesc=#brandingActiveDesc#}</span>
                        </div>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="cPosition">{#brandingPosition#}</label>
                            </span>
                            <span class="input-group-wrap">
                                <select name="cPosition" id="cPosition" class="form-control combo">
                                    <option value="oben"{if $oBranding->cPosition === 'oben'} selected{/if}>oben</option>
                                    <option value="oben-rechts"{if $oBranding->cPosition === "oben-rechts"} selected{/if}>
                                        oben-rechts
                                    </option>
                                    <option value="rechts"{if $oBranding->cPosition === 'rechts'} selected{/if}>rechts
                                    </option>
                                    <option value="unten-rechts"{if $oBranding->cPosition === 'unten-rechts'} selected{/if}>
                                        unten-rechts
                                    </option>
                                    <option value="unten"{if $oBranding->cPosition === 'unten'} selected{/if}>unten</option>
                                    <option value="unten-links"{if $oBranding->cPosition === "unten-links"} selected{/if}>
                                        unten-links
                                    </option>
                                    <option value="links"{if $oBranding->cPosition === 'links'} selected{/if}>links</option>
                                    <option value="oben-links"{if $oBranding->cPosition === 'oben-links'} selected{/if}>
                                        oben-links
                                    </option>
                                    <option value="zentriert"{if $oBranding->cPosition === 'zentriert'} selected{/if}>
                                        zentriert
                                    </option>
                                </select>
                            </span>
                            <span class="input-group-addon">{getHelpDesc cDesc=#brandingPositionDesc#}</span>
                        </div>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="dTransparenz">{#brandingTransparency#}</label>
                            </span>
                            <span class="input-group-wrap">
                                <input class="form-control" type="text" name="dTransparenz" id="dTransparenz" value="{$oBranding->dTransparenz}" tabindex="1" />
                            </span>
                            <span class="input-group-addon">{getHelpDesc cDesc=#brandingTransparencyDesc#}</span>
                        </div>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="dGroesse">{#brandingSize#}</label>
                            </span>
                            <span class="input-group-wrap">
                                <input class="form-control" type="text" name="dGroesse" id="dGroesse" value="{$oBranding->dGroesse}" tabindex="1" />
                            </span>
                            <span class="input-group-addon">{getHelpDesc cDesc=#brandingSizeDesc#}</span>
                        </div>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="cBrandingBild">{#brandingFileName#}</label>
                            </span>
                            <span class="input-group-wrap">
                                <input class="form-control" type="file" name="cBrandingBild" maxlength="2097152" accept="image/jpeg,image/gif,image/png,image/bmp" id="cBrandingBild" value="" tabindex="1" {if !$oBranding->cBrandingBild|strlen > 0}required{/if}/>
                            </span>
                            <span class="input-group-addon">{getHelpDesc cDesc=#brandingFileNameDesc#}</span>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <button type="submit" value="{#save#}" class="btn btn-primary"><i class="fa fa-save"></i> {#save#}</button>
                    </div>
                </div>
            </form>
        </div>
    {/if}
</div>
{include file='tpl_inc/footer.tpl'}