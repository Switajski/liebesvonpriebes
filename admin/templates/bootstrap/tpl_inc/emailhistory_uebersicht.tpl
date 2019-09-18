{include file='tpl_inc/seite_header.tpl' cTitel=#emailhistory# cBeschreibung=#emailhistoryDesc# cDokuURL=#emailhistoryURL#}
<div id="content" class="container-fluid">
    {if $oEmailhistory_arr|@count > 0 && $oEmailhistory_arr}
        {include file='tpl_inc/pagination.tpl' oPagination=$oPagination}
        <form name="emailhistory" method="post" action="emailhistory.php">
            {$jtl_token}
            <script>
                {literal}
                $(document).ready(function() {
                    // onclick-handler for the modal-button 'Ok'
                    $('#submitForm').on('click', function() {
                        // we need to add our interest here again (a anonymouse button is not sent)
                        $('form[name$=emailhistory]').append('<input type="hidden" name="remove_all" value="true">');
                        // do the 'submit'
                        $('form[name$=emailhistory]').submit();
                    })
                });
                {/literal}
            </script>
            <div id="confirmModal" class="modal fade" role="dialog">
                <div class="modal-dialog">

                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                            <h4 class="modal-title">Achtung!</h4>
                        </div>
                        <div class="modal-body">
                            <p>Wollen Sie wirklich das komplette eMail-Log l&ouml;schen?</p>
                        </div>
                        <div class="modal-footer">
                            <div class="btn-group">
                                <button type="button" class="btn btn-success" name="ok" id="submitForm"><i class="fa fa-check"></i>&nbsp;Ok</button>
                                <button type="button" class="btn btn-danger" name="cancel" data-dismiss="modal"><i class="fa fa-close"></i>&nbsp;Abbrechen</button>
                            </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <input name="a" type="hidden" value="delete" />
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">{#emailhistory#}</h3>
                </div>
                <div class="table-responsive">
                    <table class="list table">
                        <thead>
                        <tr>
                            <th></th>
                            <th class="tleft">{#subject#}</th>
                            <th class="tleft">{#fromname#}</th>
                            <th class="tleft">{#fromemail#}</th>
                            <th class="tleft">{#toname#}</th>
                            <th class="tleft">{#toemail#}</th>
                            <th class="tleft">{#date#}</th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach name=emailhistory from=$oEmailhistory_arr item=oEmailhistory}
                            <tr class="tab_bg{$smarty.foreach.emailhistory.iteration%2}">
                                <td class="check">
                                    <input type="checkbox" name="kEmailhistory[]" value="{$oEmailhistory->getEmailhistory()}" />
                                </td>
                                <td>{$oEmailhistory->getSubject()}</td>
                                <td>{$oEmailhistory->getFromName()}</td>
                                <td>{$oEmailhistory->getFromEmail()}</td>
                                <td>{$oEmailhistory->getToName()}</td>
                                <td>{$oEmailhistory->getToEmail()}</td>
                                <td>{SmartyConvertDate date=$oEmailhistory->getSent()}</td>
                            </tr>
                        {/foreach}
                        </tbody>
                        <tfoot>
                        <tr>
                            <td class="check">
                                <input name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessages(this.form);" /></td>
                            <td colspan="8"><label for="ALLMSGS">Alle ausw&auml;hlen</label></td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="panel-footer">
                    <div class="btn-group">
                        <button name="zuruecksetzenBTN" type="submit" class="btn btn-warning"><i class="fa fa-trash"></i> {#deleteSelected#}</button>
                        <button name="remove_all" type="button" class="btn btn-danger" data-target="#confirmModal" data-toggle="modal"><i class="fa fa-trash"></i> {#deleteAll#}</button>
                    </div>
                </div>
            </div>
        </form>
    {else}
        <div class="alert alert-info">{#nodata#}</div>
    {/if}
</div>
