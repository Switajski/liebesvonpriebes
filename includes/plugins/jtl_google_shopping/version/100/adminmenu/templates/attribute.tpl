{if $cFehler}
<div class="box_error">{$cFehler}</div>
<br />
{/if}
{if $cHinweis}
<div class="box_success">{$cHinweis}</div>
<br />
{/if}
<form method="post" enctype="multipart/form-data" name="export">
    <input type="hidden" name="kPlugin" value="{$oPlugin->kPlugin}" />
    <input type="hidden" name="cPluginTab" value="Export-Attribute" />
    <input type="hidden" name="stepPlugin" value="alteAttr" />
        
    <table style="width: 1000px;">
        <tr>
            <td>ID</td>
            <td>Google Name</td>
            <td>Wert Name</td>
            <td>Werttyp</td>
            <td>Aktiv</td>
            <td>Aktionen</td>
        </tr>
        {if $attribute_arr}
            {foreach name=attribut from=$attribute_arr item=oAttribut}
                {assign var=kAttribut value=$oAttribut->kAttribut} 
                
                {if isset($kindAttribute_arr[$kAttribut])}
                {assign var=cBackground value=' style="background-color: #f1f1f1; border:0px"'} 
                {else}
                {assign var=cBackground value=''} 
                {/if}
                
                <tr>
                    <td{$cBackground}>{$oAttribut->kAttribut}</td>
                    <td{$cBackground}>{if $oAttribut->bStandard eq 1}{$oAttribut->cGoogleName}{else}<input type="text" name="cGoogleName[{$oAttribut->kAttribut}]" value="{$oAttribut->cGoogleName}" />{/if}</td>
                    <td{$cBackground}><input type="text" name="cWertName[{$oAttribut->kAttribut}]" value="{$oAttribut->cWertName}" {if isset($kindAttribute_arr[$kAttribut])}style="display: none;"{/if} /></td>
                    <td{$cBackground}>
                        {if isset($kindAttribute_arr[$kAttribut])}
                            {$eWertHerkunft}
                        {else}
                            <select name="eWertHerkunft[{$oAttribut->kAttribut}]">
                                {foreach from=$eWertHerkunft_arr item=eWertHerkunft}
                                    <option value="{$eWertHerkunft}" {if $eWertHerkunft eq $oAttribut->eWertHerkunft}selected{/if}>{$eWertHerkunft}</option>
                                {/foreach}
                            </select>
                        {/if}
                    <td{$cBackground}><input type="checkbox" name="bAktiv[{$oAttribut->kAttribut}]" value="1" {if $oAttribut->bAktiv eq 1}checked="true"{/if} /></td>
                    <td{$cBackground}>{if $oAttribut->bStandard eq 1}<input type="submit" name="btn_standard[{$oAttribut->kAttribut}]" value="Zur&uuml;cksetzen" {if isset($kindAttribute_arr[$kAttribut])}style="display: none;"{/if} />{else}<input type="submit" name="btn_delete[{$oAttribut->kAttribut}]" value="Löschen" />{/if}</td>
                </tr>
                {if isset($kindAttribute_arr[$kAttribut])}
                    <tr>
                        <td{$cBackground}>&Gt;</td>
                        <td{$cBackground} colspan="5">
                            <table>
                                <tr>
                                    <td{$cBackground}>ID</td>
                                    <td{$cBackground}>V-ID</td>
                                    <td{$cBackground}>Google Name</td>
                                    <td{$cBackground}>Wert Name</td>
                                    <td{$cBackground}>Werttyp</td>
                                    <td{$cBackground}>Aktiv</td>
                                    <td{$cBackground}>Aktionen</td>
                                </tr>
                    {foreach name=kindAttribut from=$kindAttribute_arr[$kAttribut] item=oKindAttribut}
                    <tr>
                        <td{$cBackground}>{$oKindAttribut->kAttribut}</td>
                        <td{$cBackground}>{if $oKindAttribut->bStandard eq 1}{$oKindAttribut->kVaterAttribut}{else}<input type="text" name="kVaterAttribut[{$oKindAttribut->kAttribut}]" value="{$oKindAttribut->kVaterAttribut}" style="width: 30px" />{/if}</td>
                        <td{$cBackground}>{if $oKindAttribut->bStandard eq 1}{$oKindAttribut->cGoogleName}{else}<input type="text" name="cGoogleName[{$oKindAttribut->kAttribut}]" value="{$oKindAttribut->cGoogleName}" />{/if}</td>
                        <td{$cBackground}><input type="text" name="cWertName[{$oKindAttribut->kAttribut}]" value="{$oKindAttribut->cWertName}" /></td>
                        <td{$cBackground}>
                            <select name="eWertHerkunft[{$oKindAttribut->kAttribut}]">
                                {foreach from=$eWertHerkunft_arr item=eWertHerkunft key=cWertHerkunft}
                                    <option value="{$eWertHerkunft}" {if $eWertHerkunft eq $oKindAttribut->eWertHerkunft}selected{/if}>{$cWertHerkunft}</option>
                                {/foreach}
                            </select>
                        <td{$cBackground}><input type="checkbox" name="bAktiv[{$oKindAttribut->kAttribut}]" value="1" {if $oKindAttribut->bAktiv eq 1}checked="true"{/if} /></td>
                        <td{$cBackground}>{if $oKindAttribut->bStandard eq 1}<input type="submit" name="btn_standard[{$oKindAttribut->kAttribut}]" value="Zur&uuml;cksetzen" />{else}<input type="submit" name="btn_delete[{$oKindAttribut->kAttribut}]" value="Löschen" />{/if}</td>
                    </tr>
                    {/foreach}
                            </table>
                        </td>
                    <tr>
                {/if}
            {/foreach}
        {else}
            <tr>
                <td colspan="5">Zurzeit wurden keine Attribute angelegt.</td>
            </tr>
        {/if}
    </table>
    
    <input type="submit" name="btn_save_old" value="Änderungen speichern" class="orange" />
    <br /><br /><br />
</form>
<form method="post" enctype="multipart/form-data" name="export">
    <input type="hidden" name="kPlugin" value="{$oPlugin->kPlugin}" />
    <input type="hidden" name="cPluginTab" value="Export-Attribute" />
    <input type="hidden" name="stepPlugin" value="neuesAttr" />
    <b>Neue Attribute anlegen:</b><br />
    <table style="width: 1000px;">
        <tr>
            <td style="width: 80px"><label for="cGoogleName">Google Name</label></td>
            <td style="width: 160px"><input type="text" name="cGoogleName" value="{$smarty.post.cGoogleName}" /></td>
            <td>Wie soll das Attribut in der Export-Datei f&uuml;r Google hei&szlig;en?</td>
        </tr>
        <tr>
            <td><label for="bAktiv">Werttyp</label></td>
            <td>
                <select name="eWertHerkunft">
                    {foreach from=$eWertHerkunft_arr item=eWertHerkunft key=cWertHerkunft}
                        <option value="{$eWertHerkunft}" {if $smarty.post.eWertHerkunft eq $eWertHerkunft}selected {/if}>{$cWertHerkunft}</option>
                    {/foreach}
                </select>
            </td>
            <td>Aus welchem "Feld"-Typ soll der Wert exportiert werden</td>
        </tr>
        <tr>
            <td><label for="cWertName">Wert Name</label></td>
            <td><input type="text" name="cWertName" value="{$smarty.post.cWertName}" /></td>
            <td>
                Je nach Werttyp:<br/>
                <b>Artikel Eigenschaft:</b> In welcher Eigenschaft des Artikel-Objektes steht der Wert?<br />
                <b>Funktions Attribut:</b> In welchem FunktionsAttribut des Artikels steht der Wert?<br />
                <b>Attribut:</b> In welchem Attribut des Artikels steht der Wert?<br />
                <b>Merkmal:</b> In welchem Merkmal des Artikels steht der Wert?<br />
                <b>statischer Wert:</b> Geben Sie einen Wert ein der immer bei als Wert verwendet werden soll.<br />
                <b>Vater Attribut:</b> Geben Sie hier nichts ein wenn dies ein Vater-Attribut ist.<br />
            </td>
        </tr>
        <tr>
            <td><label for="bAktiv">Aktiv</label></td>
            <td><input type="checkbox" name="bAktiv" value="1" {if $smarty.post.bAktiv eq 1}checked="true" {/if}/></td>
            <td>Soll dieses Attribut exportiert werden?</td>
        </tr>
        <tr>
            <td><label for="kVaterAttribut">V-ID (Vater-ID)</label></td>
            <td><input type="text" name="kVaterAttribut" style="width: 30px" value="{$smarty.post.kVaterAttribut}" /></td>
            <td>
                Hier die ID des Attributes eingeben von dem dieses ein Kind-Attribut ist. 
                (<b>ACHTUNG:</b> Es funktionieren nur IDs bei denen der Werttyp (des Vaters) "VaterAttribut" ist)<br />
                Wenn diess Attribut kein Kind-Attribut ist brauchen Sie hier nichts eingeben.
            </td>
        </tr>
    </table>
    <input type="submit" name="btn_save_new" value="Neues Attribut Speichern" class="orange" />
</form>


