{config_load file="$lang.conf" section='zahlungsarten'}
{if isset($saved) && $saved}
<div class="alert alert-success">
    <i class="fa fa-info-circle"></i> Einstellungen wurden erfolgreich gespeichert
</div>
{else}
<div class="alert alert-info">
    <i class="fa fa-info-circle"></i> Wählen Sie bis zu 5 zusätzliche Zahlungsarten aus, die in der Payment Wall unter den Standard-Bezahlmethoden von PayPal PLUS angeboten werden. 
</div>
{/if}
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">Verfügbare Zahlungsarten</h3>
    </div>
    <form method="post" action="{$postUrl}">
        <input type="hidden" name="save" value="1">
        <div class="table-responsive">
            <table class="list table" id="payments">
                <tbody>
                {foreach name=p from=$payments item=payment}
                    <tr>
                        <td class="text-center v-center" width="40"><input type="checkbox" name="payment[]" value="{$payment->kZahlungsart}" {if $payment->checked}checked="checked"{/if} /></td>
                        <td class="v-center ui-drag-visible">
                            <strong>{$payment->cName}</strong> {if $payment->cAnbieter|@count_characters > 0}<span class="text-muted">&nbsp;&ndash;&nbsp; {$payment->cAnbieter}</span>{/if}
                        </td>
                        <td class="text-center v-center" width="40">
                            <a href="zahlungsarten.php?kZahlungsart={$payment->kZahlungsart}" class="btn btn-default" title="Bearbeiten"><i class="fa fa-edit"></i></a>
                        </td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </div>
        <div class="panel-footer">
            <div class="save btn-group">
                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Speichern</button>
            </div>
        </div>
    </form>
</div>
<script type="text/javascript">
{literal}
$(function() {
    $("#payments tbody").sortable({
        placeholder: "ui-state-highlight"
    });
    
    $("#payments tbody").disableSelection();

    $('#payments input[name="payment[]"]').tooltip({
        trigger: 'manual',
        placement: 'right',
        title: 'Maximal 5 weitere Zahlungsarten'
    });

    $('#payments input[name="payment[]"]').change(function(e) {
        var count = $('input[name="payment[]"]:checked').length;
        if (count > 5) {
            $(e.target).attr('checked', false);
            $(e.target).tooltip('show');
        }
    });
    
    $('#payments input[name="payment[]"]').focusout(function(e) {
        $(e.target).tooltip('hide');
    });
});
{/literal}
</script>