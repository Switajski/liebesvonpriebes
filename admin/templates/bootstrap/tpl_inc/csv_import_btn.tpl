{*
    Display a CSV import button for a CSV importer with the unique $importerId

    @param string importerId - the id string for this CSV importer
    @param bool bCustomStrategy - Show modal dialog to choose the import strategy (default: false)
*}
{assign var='bCustomStrategy' value=$bCustomStrategy|default:true}
<script>
    var $form_{$importerId} = null;
    var $fileInput_{$importerId} = null;

    $(function ()
    {
        var $importcsvInput = $('<input>', { type: 'hidden', name: 'importcsv', value: '{$importerId}' });
        var $tokenInput     = $('{$jtl_token}');

        $fileInput_{$importerId} = $('<input>', { type: 'file', name: 'csvfile', accept: '.csv,.slf' });
        $fileInput_{$importerId}.hide();
        $fileInput_{$importerId}.change(function () {
            {if $bCustomStrategy === true}
                $('#modal-{$importerId}').modal('show');
            {else}
                $form_{$importerId}.submit();
            {/if}
        });

        $form_{$importerId} = $(
            '<form>',
            {
                method: 'post', enctype: 'multipart/form-data',
                action: window.location.pathname
            }
        );
        $form_{$importerId}.append($importcsvInput, $fileInput_{$importerId}, $tokenInput);

        $('body').append($form_{$importerId});
    });

    function onClickCsvImport_{$importerId} ()
    {
        $fileInput_{$importerId}.click();
    }

    {if $bCustomStrategy === true}
        function onModalCancel_{$importerId} ()
        {
            $('#modal-{$importerId}').modal('hide');
        }

        function onModalSubmit_{$importerId} ()
        {
            $('#modal-{$importerId}').modal('hide');
            $form_{$importerId}
                .append($('#importType-{$importerId}'))
                .submit();
        }
    {/if}
</script>
{if $bCustomStrategy === true}
    <div class="modal fade" tabindex="-1" role="dialog" id="modal-{$importerId}">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{#importCsvChooseType#}</h4>
                </div>
                <div class="modal-body">
                    <label for="importType-{$importerId}" class="sr-only">{#importCsvChooseType#}</label>
                    <select class="form-control" name="importType" id="importType-{$importerId}">
                        <option value="0">{#importCsvType0#}</option>
                        <option value="1">{#importCsvType1#}</option>
                        <option value="2">{#importCsvType2#}</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <div class="btn-group">
                        <button type="button" class="btn btn-danger" onclick="onModalCancel_{$importerId}();">
                            <i class="fa fa-times"></i> {#cancel#}
                        </button>
                        <button type="button" class="btn btn-primary" onclick="onModalSubmit_{$importerId}();">
                            <i class="fa fa-upload"></i> {#importCsv#}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
{/if}
<button type="button" class="btn btn-default" onclick="onClickCsvImport_{$importerId}()">
    <i class="fa fa-upload"></i> {#importCsv#}
</button>