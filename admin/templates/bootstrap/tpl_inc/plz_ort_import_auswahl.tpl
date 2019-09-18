{config_load file="$lang.conf" section='plz_ort_import'}
<div id="modalSelect" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4>{#plz_ort_import_select#}</h4>
            </div>
            <div class="modal-body">
                {if isset($oLand_arr) && count($oLand_arr) > 0}
                <ul class="list-group">
                    <li class="boxRow panel-heading">
                        <div class="col-xs-1"><strong>{#plz_ort_iso#}</strong></div>
                        <div class="col-xs-4"><strong>{#plz_ort_country#}</strong></div>
                        <div class="col-xs-3"><strong>{#plz_ort_date#}</strong></div>
                        <div class="col-xs-3"><strong>{#plz_ort_size#}</strong></div>
                        <div class="col-xs-1"></div>
                    </li>
                    {foreach from=$oLand_arr item="oLand"}
                    <li class="list-group-item boxRow">
                        <div class="col-xs-1">{$oLand->cISO}</div>
                        <div class="col-xs-4">{$oLand->cDeutsch}</div>
                        <div class="col-xs-3">{$oLand->cDate}</div>
                        <div class="col-xs-3">{$oLand->cSize}</div>
                        <div class="col-xs-1"><a href="#" data-callback="plz_ort_import" data-ref="{$oLand->cURL}"><i class="fa fa-download"></i></a></div>
                    </li>
                    {/foreach}
                </ul>
                {else}
                <div class="alert alert-warning"><i class="fa fa-warning"></i> {#plz_ort_import_select_failed#}</div>
                {/if}
            </div>
            <div class="modal-footer">
                <a href="#" class="btn btn-primary" data-dismiss="modal"><i class="fa fa-close"></i> {#plz_ort_import_cancel#}</a>
            </div>
        </div>
    </div>
</div>