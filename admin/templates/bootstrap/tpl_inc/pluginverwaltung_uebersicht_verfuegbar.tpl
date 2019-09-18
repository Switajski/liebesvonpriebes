<script>
    // transfer our licenses(-json-object) from php into js
    var vLicenses = {if isset($szLicenses)}{$szLicenses}{else}[]{/if};
//{literal}

    $(document).ready(function() {
        token = $('input[name="jtl_token"]').val();

        // for all found licenses..
        for (var key in vLicenses) {
            // ..bind a click-handler to the plugins checkbox
            $('input[id="plugin-check-'+key+'"]').click(function(event) {
                // grab the element, which was rising that click-event (click to the checkbox)
                var oTemp = $(event.currentTarget);
                szPluginName = oTemp.val();

                if (this.checked) { // it's checked yet, right after the click was fired
                    $('input[id="plugin-check-'+szPluginName+'"]').attr('disabled', 'disabled'); // block the checkbox!
                    $('div[id="licenseModal"]').modal({backdrop : 'static'}); // set our modal static (a click in black did not hide it!)
                    $('div[id="licenseModal"]').find('.modal-body').load('getMarkdownAsHTML.php', {'jtl_token':token, 'path':vLicenses[szPluginName]});
                    $('div[id="licenseModal"]').modal('show');
                }
            });
        }

        // handle the (befor-)hiding of the modal and what's happening during it occurs
        $('div[id="licenseModal"]').on('hide.bs.modal', function(event) {
            // IMPORTANT: release the checkbox on modal-close again too!
            $('input[id=plugin-check-'+szPluginName+']').removeAttr('disabled');

            // check, which element is 'active' before/during the modal goes hiding (to determine, which button closes it)
            // (it is faster than check a var or bind an event to an element)
            if ('ok' === document.activeElement.name) {
                $('input[id=plugin-check-'+szPluginName+']').prop('checked', true);
            } else {
                $('input[id=plugin-check-'+szPluginName+']').prop('checked', false);
            }
        });
    });
</script>
{/literal}

<div id="verfuegbar" class="tab-pane fade {if isset($cTab) && $cTab === 'verfuegbar'} active in{/if}">
    {if isset($PluginVerfuebar_arr) && $PluginVerfuebar_arr|@count > 0}
        <form name="pluginverwaltung" method="post" action="pluginverwaltung.php">
            {$jtl_token}
            <input type="hidden" name="pluginverwaltung_uebersicht" value="1" />
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">{#pluginListNotInstalled#}</h3>
                </div>
                <div class="table-responsive">

                    <!-- license-modal definition -->
                    <div id="licenseModal" class="modal fade" role="dialog">
                        <div class="modal-dialog modal-lg">

                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    <h4 class="modal-title">License Plugin</h4>
                                </div>
                                <div class="modal-body">
                                    {* license.md content goes here via js *}
                                </div>
                                <div class="modal-footer">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-success" name="ok" data-dismiss="modal"><i class="fa fa-check"></i>&nbsp;Ok</button>
                                        <button type="button" class="btn btn-danger" name="cancel" data-dismiss="modal"><i class="fa fa-close"></i>&nbsp;Abbrechen</button>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <table class="list table">
                        <thead>
                        <tr>
                            <th></th>
                            <th class="tleft">{#pluginName#}</th>
                            <th>{#pluginVersion#}</th>
                            <th>{#pluginFolder#}</th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach name="verfuergbareplugins" from=$PluginVerfuebar_arr item=PluginVerfuebar}
                            <tr>
                                <td class="check"><input type="checkbox" name="cVerzeichnis[]" id="plugin-check-{$PluginVerfuebar->cVerzeichnis}" value="{$PluginVerfuebar->cVerzeichnis}" /></td>
                                <td>
                                    <label for="plugin-check-{$PluginVerfuebar->cVerzeichnis}">{$PluginVerfuebar->cName}</label>
                                    <p>{$PluginVerfuebar->cDescription}</p>
                                    {if isset($PluginVerfuebar->shop4compatible) && $PluginVerfuebar->shop4compatible === false}
                                        <div class="alert alert-info"><strong>Achtung:</strong> Plugin ist nicht vollst&auml;ndig Shop4-kompatibel! Es k&ouml;nnen daher Probleme beim Betrieb entstehen.</div>
                                    {/if}
                                </td>
                                <td class="tcenter">{$PluginVerfuebar->cVersion}</td>
                                <td class="tcenter">{$PluginVerfuebar->cVerzeichnis}</td>
                            </tr>
                        {/foreach}
                        </tbody>
                        <tfoot>
                        <tr>
                            {*<td class="check"><input name="ALLMSGS" id="ALLMSGS4" type="checkbox" onclick="AllMessages(this.form);" /></td>*}
                            <td class="check"><input name="ALLMSGS" id="ALLMSGS4" type="checkbox" onclick="AllMessagesExcept(this.form, vLicenses);" /></td>
                            <td colspan="5"><label for="ALLMSGS4">{#pluginSelectAll#}</label></td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="panel-footer">
                    <button name="installieren" type="submit" class="btn btn-primary"><i class="fa fa-share"></i> {#pluginBtnInstall#}</button>
                </div>
            </div>
        </form>
    {else}
        <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
    {/if}
</div>
