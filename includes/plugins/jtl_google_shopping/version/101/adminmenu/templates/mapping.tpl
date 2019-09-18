{if $cFehler}
<div class="box_error">{$cFehler}</div>
<br />
{/if}
{if $cHinweis}
<div class="box_success">{$cHinweis}</div>
<br />
{/if}
<form method="post" enctype="multipart/form-data" name="mapping">
    <input type="hidden" name="kPlugin" value="{$oPlugin->kPlugin}" />
    <input type="hidden" name="cPluginTab" value="Merkmale mappen" />
    <input type="hidden" name="stepPlugin" value="{$stepPlugin}" />
    <table style="width: 800px;">
        <tr>
            <td class="TD1">ID</td>
            <td class="TD2">Typ</td>
            <td class="TD3">Zu</td>
            <td class="TD4">Von</td>
            <td class="TD5">Löschen</td>
        </tr>
        {if $mapping_arr}
            {foreach name=attribut from=$mapping_arr item=mapping}
                <tr>
                    <td class="TD1" style="width: 110px;">{$mapping.kMapping}</td>
                    <td class="TD2">{$mapping.cType}</td>
                    <td class="TD3">{$mapping.cZu}</td>
                    <td class="TD4">{$mapping.cVon}</td>
                    <td class="TD5"><input type="submit" name="btn_delete[{$mapping.kMapping}]" value="Löschen" /></td>
                </tr>
            {/foreach}
        {else}
            <tr>
                <td colspan="4">Zurzeit wurden keine optionalen Attribute angelegt.</td>
            </tr>
        {/if}
    </table>
    <br /><br /><br />
    Neues Mapping anlegen:<br />
    <table style="width: 300px;">
        <tr>
            <td class="TD1" style="width: 110px;"><label for="cType[0]">Typ</label></td>
            <td class="TD2">
                <select id="cType" name="cType">
                    <option value="0">-bitte Auswählen-</option>
                    <option value="Merkmal">Merkmal</option>
                    <option value="Merkmalwert">Merkmalwert</option>
                </select>
            </td>
        </tr>
        <tr class="zeile">
            <td class="TD1"><label for="cVon">Von</label></td>
            <td class="TD2">
                <input type="text" name="cVon" /> 
                <span class="cZu Merkmalwert">
                    <small>(z.B. Sehr Groß)</small>
                </span>
                <span class="cZu Merkmal">
                    <small>(z.B. Konfektionsgrösse)</small>
                </span>
            </td>
        </tr>
        <tr class="zeile">
            <td class="TD1"><label for="cZu">Zu</label></td>
            <td class="TD2">
                <span class="cZu Merkmalwert">
                    <input type="text" name="cZuMerkmalwert" /> <small>(z.B. XL)</small>
                </span>
                <span class="cZu Merkmal">
                    <select name="cZuMerkmal">
                        <option value="farbe">Farbe</option>
                        <option value="groesse">Größe</option>
                        <option value="geschlecht">Geschlecht</option>
                        <option value="altersgruppe">Altersgruppe</option>
                        <option value="muster">Muster</option>
                        <option value="material">Material</option>
                    </select><br />
                    <small>(z.B. Grösse)</small>
                </span>
            </td>
        </tr>
    </table>
    <input type="submit" class="zeile" name="btn_save_new" value="Neues Attribut Speichern" />
</form>
{literal}
<script type="text/javascript">
    $(function() {
        toogleZu();
        $('#cType').change(function() {
            toogleZu();
        });
    });
    
    function toogleZu() {
        var cValue = $('#cType').val();
        if(cValue == 0) {
            $('.zeile').hide();
        } else {
            $('.zeile').show();
            $('.cZu').hide();
            $('.'+cValue).show();
        }
    }
</script>
{/literal}