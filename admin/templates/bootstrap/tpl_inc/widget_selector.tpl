{foreach from=$oAvailableWidget_arr item=oAvailableWidget}
    <div class="media widget">
        <div class="media-body">
            <h4 class="media-heading">{$oAvailableWidget->cTitle}</h4>
            <span class="text-muted">{$oAvailableWidget->cDescription}</span>
        </div>
        <div class="media-right text-vcenter">
            <a href="#"
               onclick="addWidget({$oAvailableWidget->kWidget})"
               class="badge badge-widget"><i class="fa fa-plus"></i></a>
        </div>
    </div>
{/foreach}
{if $oAvailableWidget_arr|@count == 0}
    <div class="widget_item">
        <p class="title">Keine weiteren Widgets vorhanden.</p>
    </div>
{/if}