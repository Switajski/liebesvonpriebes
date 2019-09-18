<div id="settings">
    <form action="slider.php?action={$cAction}" method="post" accept-charset="iso-8859-1" id="slider" enctype="multipart/form-data">
        <input type="hidden" name="action" value="{$cAction}" /> 
        <input type="hidden" name="kSlider" value="{$oSlider->kSlider}" /> 
        <div class="category first">Allgemein</div>
        <div class="item">
            <div class="name">
                <label for="cName">Interner Name</label>
            </div>
            <div class="for">
                <input type="text" name="cName" id="cName" value="{$oSlider->cName}" />
            </div>
        </div>
        <div class="item">
            <div class="name">
                <label for="bAktiv">Status</label>
            </div>
            <div class="for">
                <select name="bAktiv">
                    <option value="0" {if $oSlider->bAktiv == 0} selected="selected" {/if}>Deaktiviert</option>
                    <option value="1" {if $oSlider->bAktiv == 1} selected="selected" {/if}>Aktiviert</option>
                </select>
            </div>
        </div>
        <div class="item">
            <div class="name">
                <label for="bRandomStart">Zuf&auml;lliger Start</label>
            </div>
            <div class="for">
                <select name="bRandomStart">
                    <option value="0" {if $oSlider->bRandomStart == "0"} selected="selected" {/if}>Nein</option>
                    <option value="1" {if $oSlider->bRandomStart == "1"} selected="selected" {/if}>Ja</option>
                </select>
            </div>
        </div>
        <div class="item">
            <div class="name">
                <label for="bPauseOnHover">Pause bei Hover</label>
            </div>
            <div class="for">
                <select name="bPauseOnHover">
                    <option value="0" {if $oSlider->bPauseOnHover == "0"} selected="selected" {/if}>Nein</option>
                    <option value="1" {if $oSlider->bPauseOnHover == "1"} selected="selected" {/if}>Ja</option>
                </select>
            </div>
        </div>  
        <div class="category">Darstellung</div>
        <div class="item">
            <div class="name">
                <label for="bControlNav">Navigation</label>
            </div>
            <div class="for">
                <select name="bControlNav">
                    <option value="0" {if $oSlider->bControlNav == "0"} selected="selected" {/if}>Ausblenden</option>
                    <option value="1" {if $oSlider->bControlNav == "1"} selected="selected" {/if}>Anzeigen</option>
                </select>
            </div>
        </div>
        <div class="item">
            <div class="name">
                <label for="bThumbnail">Thumbnail Navigation</label>
            </div>
            <div class="for">
                <select name="bThumbnail">
                    <option value="0" {if $oSlider->bThumbnail == "0"} selected="selected" {/if}>Deaktiviert</option>
                    <option value="1" {if $oSlider->bThumbnail == "1"} selected="selected" {/if}>Aktiviert</option>
                </select>
            </div>
        </div>
        <div class="item">
            <div class="name">
                <label for="bDirectionNav">Navigation (Richtung)</label>
            </div>
            <div class="for">
                <select name="bDirectionNav">
                    <option value="0" {if $oSlider->bDirectionNav == "0"} selected="selected" {/if}>Ausblenden</option>
                    <option value="1" {if $oSlider->bDirectionNav == "1"} selected="selected" {/if}>Anzeigen</option>
                </select>
            </div>
        </div>
        <div class="item">
            <div class="name">
                <label for="cSelectedEffects">Effekte</label>
            </div>
            <div class="for">
                <input type="checkbox" value="random" class="random_effects" {if isset($checked)}{$checked} {/if}name="cEffects" /> Zuf&auml;llige Effekte<br>
                <div class="select_container">
                    <div class="select_box">
                        <span class="select_title">ausgew&auml;hlte Effekte</span>
                        <select name="cSelectedEffects" size="10" multiple {$disabled}>
                            {$cEffects}
                        </select>
                        <input type="hidden" name="cEffects" value="{if isset($oSlider->cEffects)}{$oSlider->cEffects}{/if}" {$disabled}/>
                        <input type="button" class="select_remove button remove" value="Entfernen" {$disabled}/>
                    </div>
                    <div class="select_box">
                        <span class="select_title">Verf&uuml;gbare Effekte</span>
                        <select name="cAvaibleEffects" size="10" multiple {$disabled}>
                            <option value="sliceDown">sliceDown</option>
                            <option value="sliceDownLeft">sliceDownLeft</option>
                            <option value="sliceUp">sliceUp</option>
                            <option value="sliceUpLeft">sliceUpLeft</option>
                            <option value="sliceUpDown">sliceUpDown</option>
                            <option value="sliceUpDownLeft">sliceUpDownLeft</option>
                            <option value="fold">fold</option>
                            <option value="fade">fade</option>
                            <option value="slideInRight">slideInRight</option>
                            <option value="slideInLeft">slideInLeft</option>
                            <option value="boxRandom">boxRandom</option>
                            <option value="boxRain">boxRain</option>
                            <option value="boxRainReverse">boxRainReverse</option>
                            <option value="boxRainGrow">boxRainGrow</option>
                            <option value="boxRainGrowReverse">boxRainGrowReverse</option>
                        </select>
                        <input type="button" class="select_add button add" value="Hinzuf&uuml;gen" {$disabled} />
                    </div>
                </div>
            </div>
        </div>
        <div class="item">
            <div class="name">
                <label for="cTheme">Theme</label>
            </div>
            <div class="for">
                <select name="cTheme">
                    <option value="default" {if $oSlider->cTheme == "default"} selected="selected" {/if}>default</option>
                    <option value="bar" {if $oSlider->cTheme == "bar"} selected="selected" {/if}>bar</option>
                    <option value="light" {if $oSlider->cTheme == "light"} selected="selected" {/if}>light</option>
                    <option value="dark" {if $oSlider->cTheme == "dark"} selected="selected" {/if}>dark</option>
                </select>
            </div>
        </div>

        <div class="item">
            <div class="name">
                <label for="nAnimationSpeed">Animation Geschwindigkeit (in ms)</label>
            </div>
            <div class="for">
                <input type="text" name="nAnimationSpeed" id="nAnimationSpeed" value="{$oSlider->nAnimationSpeed}" />
                <p id="nAnimationSpeedWarning" class="nAnimationSpeedWarningColor">Der Wert von "Animations Geschwindigkeit" darf den Wert von "Pause Zeit" nicht &uuml;berschreiten!</p>
            </div>
        </div>

        <div class="item">
            <div class="name">
                <label for="nPauseTime">Pause Zeit (in ms)</label>
            </div>
            <div class="for">
                <input type="text" name="nPauseTime" id="nPauseTime" value="{$oSlider->nPauseTime}" />
            </div>
        </div>
        <div class="category">Anzeigeoptionen</div>
        <div class="item">
            <div class="name">
                <label for="kSprache">Sprache</label>
            </div>
            <div class="for">
                <select name="kSprache">
                    <option value="0">Alle</option> 
                    {foreach from=$oSprachen_arr item=oSprache}
                        <option value="{$oSprache->kSprache}" {if isset($oExtension->kSprache) && $oExtension->kSprache == $oSprache->kSprache}selected="selected"{/if}>{$oSprache->cNameDeutsch}</option>
                    {/foreach}
                </select>
            </div>
        </div>
        <div class="item">
            <div class="name">
                <label for="kKundengruppe">Kundengruppe</label>
            </div>
            <div class="for">
                <select name="kKundengruppe">
                    <option value="0">Alle</option>
                    {foreach from=$oKundengruppe_arr item=oKundengruppe}
                        <option value="{$oKundengruppe->getKundengruppe()}" {if isset($oExtension->kKundengruppe) && $oExtension->kKundengruppe == $oKundengruppe->getKundengruppe()}selected="selected"{/if}>{$oKundengruppe->getName()}</option>
                    {/foreach}
                </select>
            </div>
        </div>
        <div class="item">
            <div class="name">
                <label for="nSeitenTyp">Seitentyp</label>
            </div>
            <div class="for">
                <select name="nSeitenTyp"> {include file="tpl_inc/seiten_liste.tpl" nPage=$oExtension->nSeite}</select>
            </div>
            <div id="type2" class="custom">
                <div class="item">
                    <div class="name">
                        <label for="cKey">&raquo; Filter</label>
                    </div>
                    <div class="for">
                        <div>
                            <select name="cKey">
                                <option value="" {if isset($oExtension->cKey) && $oExtension->cKey ==""} selected="selected"{/if}>Kein Filter</option>
                                <option value="kTag" {if isset($oExtension->cKey) && $oExtension->cKey =="kTag"} selected="selected"{/if}>Tag</option>
                                <option value="kMerkmalWert" {if isset($oExtension->cKey) && $oExtension->cKey =="kMerkmalWert"} selected="selected"{/if}>Merkmal</option>
                                <option value="kKategorie" {if isset($oExtension->cKey) && $oExtension->cKey =="kKategorie"} selected="selected"{/if}>Kategorie</option>
                                <option value="kHersteller" {if isset($oExtension->cKey) && $oExtension->cKey =="kHersteller"} selected="selected"{/if}>Hersteller</option>
                                <option value="cSuche" {if isset($oExtension->cKey) && $oExtension->cKey =="cSuche"} selected="selected"{/if}>Suchbegriff</option>
                            </select>
                        </div>
                        <div class="nl">
                            <div id="keykTag" class="key">
                                <input type="hidden" name="tag_key" value="{if isset($oExtension->cKey) && $oExtension->cKey == "kTag"}{$oExtension->cValue}{/if}" />
                                <input type="text" name="tag_name" disabled="disabled" value="{if isset($oExtension->cKey) && $oExtension->cKey == "kTag"}{$oExtension->cValue}{else}Kein Tag ausgew&auml;hlt{/if}" />
                                <a href="#" class="button edit" id="tag">Tag suchen</a>
                            </div>
                            <div id="keykMerkmalWert" class="key">
                                <input type="hidden" name="attribute_key" value="{if isset($oExtension->cKey) && $oExtension->cKey == "kMerkmalWert"}{$oExtension->cValue}{/if}"/>
                                <input type="text" name="attribute_name" disabled="disabled" value="{if isset($oExtension->cKey) && $oExtension->cKey == "kMerkmalWert"}{$oExtension->cValue}{else}Kein Merkmal ausgew&auml;hlt{/if}" />
                                <a href="#" class="button edit" id="attribute">Merkmal suchen</a>
                            </div>
                            <div id="keykKategorie" class="key">
                                <input type="hidden" name="categories_key" value="{if isset($oExtension->cKey) && $oExtension->cKey == "kKategorie"}{$oExtension->cValue}{/if}"/>
                                <input type="text" name="categories_name" disabled="disabled" value="{if isset($oExtension->cKey) && $oExtension->cKey == "kKategorie"}{$oExtension->cValue}{else}Keine Kategorie ausgew&auml;hlt{/if}" />
                                <a href="#" class="button edit" id="categories">Kategorie suchen</a>
                            </div>
                            <div id="keykHersteller" class="key">
                                <input type="hidden" name="manufacturer_key" value="{if isset($oExtension->cKey) && $oExtension->cKey == "kHersteller"}{$oExtension->cValue}{/if}" />
                                <input type="text" name="manufacturer_name" disabled="disabled" value="{if isset($oExtension->cKey) && $oExtension->cKey == "kHersteller"}{$oExtension->cValue}{else}Kein Hersteller ausgew&auml;hlt{/if}" />
                                <a href="#" class="button edit" id="manufacturer">Hersteller suchen</a>
                            </div>
                            <div id="keycSuche" class="key">
                                <input type="text" name="keycSuche" value="{if isset($oExtension->cKey) && $oExtension->cKey == "cSuche"}{$oExtension->cValue}{/if}" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="save_wrapper">
            <input type="button" class="button orange" onclick="window.location.href = 'slider.php';" value="Zur&uuml;ck" />
            <input type="submit" class="button orange" value="Speichern" />
        </div>
    </form>
</div>
{include file="tpl_inc/single_search_browser.tpl"}