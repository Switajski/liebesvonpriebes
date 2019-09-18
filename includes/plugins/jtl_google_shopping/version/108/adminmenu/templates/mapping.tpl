{if $cFehler}
    <div class="alert alert-danger">{$cFehler}</div>
{/if}
{if $cHinweis}
    <div class="alert alert-info">{$cHinweis}</div>
{/if}
<form method="post" enctype="multipart/form-data" name="mapping">
    <input type="hidden" name="kPlugin" value="{$oPlugin->kPlugin}" />
    <input type="hidden" name="cPluginTab" value="Merkmale mappen" />
    <input type="hidden" name="stepPlugin" value="{$stepPlugin}" />
    <table class="table">
        <tr>
            <th class="TD1">ID</th>
            <th class="TD2">Typ</th>
            <th class="TD3">Zu</th>
            <th class="TD4">Von</th>
            <th class="TD5">L&ouml;schen</th>
        </tr>
        {if $mapping_arr}
            {foreach name=attribut from=$mapping_arr item=mapping}
                <tr>
                    <td class="TD1" style="width: 110px;">{$mapping.kMapping}</td>
                    <td class="TD2">{$mapping.cType}</td>
                    <td class="TD3">{$mapping.cZu}</td>
                    <td class="TD4">{$mapping.cVon}</td>
                    <td class="TD5"><button type="submit" name="btn_delete[{$mapping.kMapping}]" value="L&ouml;schen" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i> L&ouml;schen</button></td>
                </tr>
            {/foreach}
        {else}
            <tr>
                <td colspan="5"><div class="alert alert-info"><i class="fa fa-info-circle"></i> Zurzeit sind keine optionalen Attribute angelegt.</div></td>
            </tr>
        {/if}
    </table>
    <h3>Neues Mapping anlegen:</h3>
    <table class="table">
        <tr>
            <td class="TD1" style="width: 110px;"><label for="cType">Typ</label></td>
            <td class="TD2">
                <select class="form-control" id="cType" name="cType">
                    <option value="0">-bitte Ausw&auml;hlen-</option>
                    <option value="Merkmal">Merkmal</option>
                    <option value="Merkmalwert">Merkmalwert</option>
                </select>
            </td>
        </tr>
        <tr class="zeile">
            <td class="TD1"><label for="cVon">Von</label></td>
            <td class="TD2">
                <input class="form-control" type="text" name="cVon" id="cVon" />
                <span class="cZu Merkmalwert">
                    <small>(z.B. Sehr Gro&szlig;)</small>
                </span>
                <span class="cZu Merkmal">
                    <small>(z.B. Konfektionsgr&ouml;&szlig;e)</small>
                </span>
            </td>
        </tr>
        <tr class="zeile">
            <td class="TD1"><label for="cZuMerkmal">Zu</label></td>
            <td class="TD2">
                <span class="cZu Merkmalwert">
                    <input class="form-control" id="cZu" type="text" name="cZuMerkmalwert" /> <small>(z.B. XL)</small>
                </span>
                <span class="cZu Merkmal">
                    <select class="form-control" name="cZuMerkmal" id="cZuMerkmal">
                        <option value="farbe">Farbe</option>
                        <option value="groesse">Gr&ouml;sse</option>
                        <option value="geschlecht">Geschlecht</option>
                        <option value="altersgruppe">Altersgruppe</option>
                        <option value="muster">Muster</option>
                        <option value="material">Material</option>
                    </select><br />
                    <small>(z.B. Gr&ouml;sse)</small>
                </span>
            </td>
        </tr>
    </table>
    <button type="submit" class="btn btn-primary zeile" name="btn_save_new" value="Neues Attribut speichern"><i class="fa fa-save"></i> Neues Attribut speichern</button>
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