{if isset($reset) && $reset}
    <div class="alert alert-success">
        <i class="fa fa-info-circle"></i> Webhooks wurden wiederhergestellt
    </div>
{/if}
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">Webhooks</h3>
    </div>
    <form method="post" action="{$postUrl}">
        <input type="hidden" name="reset" value="1">
        <div class="panel-body">
            {if !empty($webhooks) && $webhooks|@count > 0}
                <div class="table-responsive">
                    <table class="list table">
                        <thead>
                            <th>Zahlungsart</th>
                            <th>Url</th>
                            <th></th>
                        </thead>
                        <tbody>
                            {foreach $webhooks as $webhook}
                                <tr>
                                    <td class="v-center">{$webhook->name}</td>
                                    <td class="v-center">{$webhook->url}</td>
                                    <td class="v-center text-right">
                                        {if $webhook->configured}
                                            {if $webhook->hook}
                                                <span class="label label-success">installiert</span>
                                            {else}
                                                <span class="label label-danger">nicht installiert</span>
                                            {/if}
                                        {else}
                                            <span class="label label-warning">nicht konfiguriert</span>
                                        {/if}
                                    </td>
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
                </div>
            {else}
                <div class="alert alert-danger">
                    <i class="fa fa-exclamation-triangle"></i> Zur Zeit sind keine Webhooks installiert.
                </div>
            {/if}
        </div>
        <div class="panel-footer">
            <div class="save btn-group">
                <button type="submit" class="btn btn-primary">{if !empty($webhooks)}Aktualisieren{else}Installieren{/if}</button>
            </div>
        </div>
    </form>
</div>