{literal}
<script type="text/javascript">
    $(document).ready(function () {
        $('.edit').click(function () {
            var kWarenlager = $(this).attr('id').replace('btn_', ''),
                row = $('.row_' + kWarenlager);
            if (row.css('display') === 'none') {
                row.fadeIn();
            } else {
                row.fadeOut();
            }
        });
    });
</script>
{/literal}

<div id="content" class="container-fluid">
    {if $oWarenlager_arr|@count > 0}
        <form method="post" action="warenlager.php">
            {$jtl_token}
            <input name="a" type="hidden" value="update" />
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">{#warenlager#}</h3>
                </div>
                <div class="table-responsive">
                    <table class="list table">
                        <thead>
                        <tr>
                            <th class="checkext">{#watenlagerActive#}</th>
                            <th>{#warenlagerIntern#}</th>
                            <th>{#warenlagerDescInt#}</th>
                            <th>{#warenlagerOption#}</th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach name=warenlager from=$oWarenlager_arr item=oWarenlager}
                            <tr>
                                <td class="checkext">
                                    <input name="kWarenlager[]" type="checkbox" value="{$oWarenlager->kWarenlager}"{if $oWarenlager->nAktiv == 1} checked{/if} />
                                </td>
                                <td class="tcenter large">{$oWarenlager->cName}</td>
                                <td class="tcenter">{$oWarenlager->cBeschreibung}</td>
                                <td class="tcenter">
                                    <a class="btn btn-default" data-toggle="collapse" href="#collapse-{$oWarenlager->kWarenlager}" title="{#edit#}"><i class="fa fa-edit"></i></a>
                                </td>
                            </tr>
                            <tr class="collapse" id="collapse-{$oWarenlager->kWarenlager}">
                                <td colspan="4">
                                {foreach name=sprachen from=$oSprache_arr item=oSprache}
                                    {assign var="kSprache" value=$oSprache->kSprache}
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                <label for="cNameSprache[{$oWarenlager->kWarenlager}][{$oSprache->kSprache}]">{$oSprache->cNameDeutsch}</label>
                                            </span>
                                            <input id="cNameSprache[{$oWarenlager->kWarenlager}][{$oSprache->kSprache}]" name="cNameSprache[{$oWarenlager->kWarenlager}][{$oSprache->kSprache}]" type="text" value="{if isset($oWarenlager->cSpracheAssoc_arr[$kSprache])}{$oWarenlager->cSpracheAssoc_arr[$kSprache]}{/if}" class="form-control large" />
                                        </div>
                                {/foreach}
                                </td>
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                </div>
                <div class="panel-footer">
                    <button name="update" type="submit" title="{#warenlagerUpdate#}" class="btn btn-primary"><i class="fa fa-refresh"></i> {#warenlagerUpdate#}</button>
                </div>
            </div>
        </form>
    {else}
        <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
    {/if}
</div>