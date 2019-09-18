{if isset($reset) && $reset}
    <div class="alert alert-success">
        <i class="fa fa-info-circle"></i> Webhooks wurden wiederhergestellt
    </div>
{/if}
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">Installierte Webhooks</h3>
    </div>
    <form method="post" action="{$postUrl}">
        <input type="hidden" name="reset" value="1">
        <div class="panel-body">
            {if !empty($webhookList) && $webhookList->getWebhooks()|@count > 0}
                <div class="table-responsive">
                    <table class="list table">
                        <thead>
                            <th>ID</th>
                            <th>Webhook</th>
                            <th>Events</th>
                        </thead>
                        <tbody>
                            {foreach from=$webhookList->getWebhooks() item=webhook}
                            <tr>
                                <td class="v-center">{$webhook->getId()}</td>
                                <td class="v-center">{$webhook->getUrl()}</td>
                                <td class="v-center">
                                    {foreach from=$webhook->getEventTypes() item=event}
                                        <p>{$event->getDescription()}</p>
                                    {/foreach}
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
                <button type="submit" class="btn btn-danger">{if !empty($webhookList)}Wiederherstellen{else}Installieren{/if}</button>
            </div>
        </div>
    </form>
</div>