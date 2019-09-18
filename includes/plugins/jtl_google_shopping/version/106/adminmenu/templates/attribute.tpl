<h4>Hier braucht im Normalfall NICHTS ver&auml;ndert zu werden. Bitte setzen Sie diese Einstellungen immer wieder auf Standard zur&uuml;ck, falls Probleme auftreten.</h4>
<form method="post" enctype="multipart/form-data" name="export">
    <input type="hidden" name="kPlugin" value="{$oPlugin->kPlugin}" />
    <input type="hidden" name="cPluginTab" value="Export-Attribute" />
    <input type="hidden" name="stepPlugin" value="alteAttr" />
        
    <table class="table">
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
                    <td{$cBackground}>{if $oAttribut->bStandard eq 1}{$oAttribut->cGoogleName}{else}<input class="form-control" type="text" name="cGoogleName[{$oAttribut->kAttribut}]" value="{$oAttribut->cGoogleName}" />{/if}</td>
                    <td{$cBackground}><input class="form-control" type="text" name="cWertName[{$oAttribut->kAttribut}]" value="{$oAttribut->cWertName}" {if isset($kindAttribute_arr[$kAttribut])}style="display: none;"{/if} /></td>
                    <td{$cBackground}>
                        {if isset($kindAttribute_arr[$kAttribut])}
                            {$eWertHerkunft}
                        {else}
                            <select class="form-control" name="eWertHerkunft[{$oAttribut->kAttribut}]">
                                {foreach from=$eWertHerkunft_arr item=eWertHerkunft}
                                    <option value="{$eWertHerkunft}" {if $eWertHerkunft eq $oAttribut->eWertHerkunft}selected{/if}>{$eWertHerkunft}</option>
                                {/foreach}
                            </select>
                        {/if}
                    <td{$cBackground}><input type="checkbox" name="bAktiv[{$oAttribut->kAttribut}]" value="1" {if $oAttribut->bAktiv eq 1}checked="true"{/if} /></td>
                    <td{$cBackground}>
                        {if isset($oAttribut->bStandard) && $oAttribut->bStandard == 1}
                            <button type="submit" name="btn_standard[{$oAttribut->kAttribut}]" value="Zur&uuml;cksetzen" class="btn btn-danger btn-sm" {if isset($kindAttribute_arr[$kAttribut])}style="display: none;"{/if}><i class="fa fa-remove"></i> Zur&uuml;cksetzen</button>
                        {else}
                            <button type="submit" name="btn_delete[{$oAttribut->kAttribut}]" value="L&ouml;schen" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i> L&ouml;schen</button>
                        {/if}
                    </td>
                </tr>
                {if isset($kindAttribute_arr[$kAttribut])}
                    <tr>
                        <td{$cBackground}>&Gt;</td>
                        <td{$cBackground} colspan="5">
                            <table class="table">
                                <tr>
                                    <td>ID</td>
                                    <td>V-ID</td>
                                    <td>Google Name</td>
                                    <td>Wert Name</td>
                                    <td>Werttyp</td>
                                    <td>Aktiv</td>
                                    <td>Aktionen</td>
                                </tr>
                                {foreach name=kindAttribut from=$kindAttribute_arr[$kAttribut] item=oKindAttribut}
                                <tr>
                                    <td>{$oKindAttribut->kAttribut}</td>
                                    <td>{if $oKindAttribut->bStandard eq 1}{$oKindAttribut->kVaterAttribut}{else}<input class="form-control" type="text" name="kVaterAttribut[{$oKindAttribut->kAttribut}]" value="{$oKindAttribut->kVaterAttribut}" style="width: 30px" />{/if}</td>
                                    <td>{if $oKindAttribut->bStandard eq 1}{$oKindAttribut->cGoogleName}{else}<input class="form-control" type="text" name="cGoogleName[{$oKindAttribut->kAttribut}]" value="{$oKindAttribut->cGoogleName}" />{/if}</td>
                                    <td><input class="form-control" type="text" name="cWertName[{$oKindAttribut->kAttribut}]" value="{$oKindAttribut->cWertName}" /></td>
                                    <td>
                                        <select class="form-control" name="eWertHerkunft[{$oKindAttribut->kAttribut}]">
                                            {foreach from=$eWertHerkunft_arr item=eWertHerkunft key=cWertHerkunft}
                                                <option value="{$eWertHerkunft}" {if $eWertHerkunft eq $oKindAttribut->eWertHerkunft}selected{/if}>{$cWertHerkunft}</option>
                                            {/foreach}
                                        </select>
                                    <td><input type="checkbox" name="bAktiv[{$oKindAttribut->kAttribut}]" value="1" {if $oKindAttribut->bAktiv eq 1}checked="true"{/if} /></td>
                                    <td>
                                        {if isset($oKindAttribut->bStandard) && $oKindAttribut->bStandard == 1}
                                            <button type="submit" name="btn_standard[{$oKindAttribut->kAttribut}]" value="Zur&uuml;cksetzen" class="btn btn-danger btn-sm"><i class="fa fa-remove"></i> Zur&uuml;cksetzen</button>
                                        {else}
                                            <button type="submit" name="btn_delete[{$oKindAttribut->kAttribut}]" value="Löschen" class="btn btn-danger btn-sm"><i class="fa fa-remove"></i> Zur&uuml;cksetzen</button>
                                        {/if}
                                    </td>
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
    
    <button type="submit" name="btn_save_old" value="&Auml;nderungen speichern" class="orange btn btn-primary"><i class="fa fa-save"></i> &Auml;nderungen speichern</button>
</form>
<form method="post" enctype="multipart/form-data" name="export">
    <input type="hidden" name="kPlugin" value="{$oPlugin->kPlugin}" />
    <input type="hidden" name="cPluginTab" value="Export-Attribute" />
    <input type="hidden" name="stepPlugin" value="neuesAttr" />
    <h3>Neue Attribute anlegen:</h3>
    <table class="table">
        <tr>
            <td style="width: 80px"><label for="cGoogleName">Google Name</label></td>
            <td style="width: 160px"><input class="form-control" id="cGoogleName" type="text" name="cGoogleName" value="{if isset($smarty.post.cGoogleName)}{$smarty.post.cGoogleName}{/if}" /></td>
            <td>Wie soll das Attribut in der Export-Datei f&uuml;r Google hei&szlig;en?</td>
        </tr>
        <tr>
            <td><label for="eWertHerkunft">Werttyp</label></td>
            <td>
                <select class="form-control" name="eWertHerkunft" id="eWertHerkunft">
                    {foreach from=$eWertHerkunft_arr item=eWertHerkunft key=cWertHerkunft}
                        <option value="{$eWertHerkunft}" {if isset($smarty.post.eWertHerkunft) && $smarty.post.eWertHerkunft == $eWertHerkunft}selected {/if}>{$cWertHerkunft}</option>
                    {/foreach}
                </select>
            </td>
            <td>Aus welchem "Feld"-Typ soll der Wert exportiert werden</td>
        </tr>
        <tr>
            <td><label for="cWertName">Wert Name</label></td>
            <td><input class="form-control" id="cWertName" type="text" name="cWertName" value="{if isset($smarty.post.cWertName)}{$smarty.post.cWertName}{/if}" /></td>
            <td>
                Je nach Werttyp:<br/>
                <b>Artikel-Eigenschaft:</b> In welcher Eigenschaft des Artikel-Objektes steht der Wert?<br />
                <b>Funktionsattribut:</b> In welchem FunktionsAttribut des Artikels steht der Wert?<br />
                <b>Attribut:</b> In welchem Attribut des Artikels steht der Wert?<br />
                <b>Merkmal:</b> In welchem Merkmal des Artikels steht der Wert?<br />
                <b>statischer Wert:</b> Geben Sie einen Wert ein, der immer als Wert verwendet werden soll.<br />
                <b>Vater-Attribut:</b> Geben Sie hier nichts ein, wenn dies ein Vater-Attribut ist.<br />
            </td>
        </tr>
        <tr>
            <td><label for="bAktiv">Aktiv</label></td>
            <td><input id="bAktiv" type="checkbox" name="bAktiv" value="1" {if isset($smarty.post.bAktiv) && $smarty.post.bAktiv == 1}checked="true" {/if}/></td>
            <td>Soll dieses Attribut exportiert werden?</td>
        </tr>
        <tr>
            <td><label for="kVaterAttribut">V-ID (Vater-ID)</label></td>
            <td><input class="form-control" id="kVaterAttribut" type="text" name="kVaterAttribut" style="width: 30px" value="{if isset($smarty.post.kVaterAttribut)}{$smarty.post.kVaterAttribut}{/if}" /></td>
            <td>
                Hier die ID des Attributes eingeben, von dem dieses ein Kind-Attribut ist.
                (<b>ACHTUNG:</b> Es funktionieren nur IDs, bei denen der Werttyp (des Vaters) "VaterAttribut" ist)<br />
                Wenn dieses Attribut kein Kind-Attribut ist, brauchen Sie hier nichts eingeben.
            </td>
        </tr>
    </table>
    <button type="submit" name="btn_save_new" value="Neues Attribut speichern" class="orange btn btn-primary"><i class="fa fa-save"></i> Neues Attribut speichern</button>
</form>


