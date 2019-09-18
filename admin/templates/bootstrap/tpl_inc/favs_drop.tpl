<a href="#" class="dropdown-toggle parent" data-toggle="dropdown" title="Favoriten">
    <i class="fa fa-star" aria-hidden="true"></i>
    <span class="caret"></span>
</a>
<ul class="dropdown-menu" role="main">
    {if isset($favorites) && is_array($favorites) && count($favorites) > 0}

        {foreach $favorites as $favorite}
            <li{if $favorite->bExtern} class="icon"{/if}>
                <a href="{$favorite->cAbsUrl}" rel="{$favorite->kAdminfav}"{if $favorite->bExtern} target="_blank"{/if}>{$favorite->cTitel}{if $favorite->bExtern} <i class="fa fa-external-link"></i>{/if}</a>
            </li>
        {/foreach}

        <li role="separator" class="divider"></li>
    {/if}
    <li class="icon">
        <a href="favs.php">Favoriten verwalten <i class="fa fa-pencil"></i></a>
    </li>
</ul>