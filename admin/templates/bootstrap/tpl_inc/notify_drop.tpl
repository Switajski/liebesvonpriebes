{if $notifications->count() > 0}
    {$notifyTypes = [0 => 'info', 1 => 'warning', 2 => 'danger']}
    <a href="#" class="dropdown-toggle parent" data-toggle="dropdown">
        <span class="badge-notify btn-{$notifyTypes[$notifications->getHighestType()]}">{$notifications->count()}</span>
        Mitteilungen
        <span class="caret"></span>
    </a>
    <ul class="dropdown-menu" role="main">
        {foreach $notifications as $notify}
            <li class="nag">
                <div class="nag-split btn-{$notifyTypes[$notify->getType()]}"><i class="fa fa-angle-right" aria-hidden="true"></i></div>
                <div class="nag-content">
                    <a href="{$notify->getUrl()}">
                        <div class="nag-title">{$notify->getTitle()}</div>
                        <div class="nag-text">{$notify->getDescription()}</div>
                    </a>
                </div>
            </li>
        {/foreach}
    </ul>
{/if}