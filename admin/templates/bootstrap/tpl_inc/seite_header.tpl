<div class="content-header well">
    <div class="row">
        <div class="col-md-11">
            <h1 class="{if isset($cBeschreibung) && $cBeschreibung|@strlen == 0}nospacing{/if}">{if $cTitel|@strlen > 0}{$cTitel}{else}Unbekannt{/if}</h1>
            {if isset($cBeschreibung) && $cBeschreibung|@strlen > 0}
                <p class="description {if isset($cClass)}{$cClass}{/if}">
                    <span><!-- right border --></span>
                    {if isset($onClick)}<a href="#" onclick="{$onClick}">{/if}{$cBeschreibung}{if isset($onClick)}</a>{/if}
                </p>
            {/if}
        </div>
        <div class="col-md-1 actions text-right">
            <div class="btn-group btn-group-plain btn-group-vertical" role="group">
                {if isset($cDokuURL) && $cDokuURL|@strlen > 0}
                    <a href="{$cDokuURL}" target="_blank" class="btn btn-default" data-toggle="tooltip"
                       data-container="body" data-placement="left" title="Zur Dokumentation">
                        <i class="fa fa-medkit" aria-hidden="true"></i>
                    </a>
                {/if}
                <a href="favs.php" class="btn btn-default" data-toggle="tooltip" data-container="body" data-placement="left" title="Zu Favoriten hinzuf&uuml;gen" id="fav-add"><i class="fa fa-star" aria-hidden="true"></i></a>
            </div>
        </div>
    </div>
    {if isset($oPlugin)}
        <p><strong>{#pluginAuthor#}:</strong> {$oPlugin->cAutor}</p>
        <p><strong>{#pluginHomepage#}:</strong> <a href="{$oPlugin->cURL}" target="_blank"><i class="fa fa-external-link"></i> {$oPlugin->cURL}</a></p>
        <p><strong>{#pluginVersion#}:</strong> {$oPlugin->nVersion}</p>
        <p><strong>{#pluginDesc#}:</strong> {$oPlugin->cBeschreibung}</p>
    {/if}
</div>
{if isset($cHinweis) && $cHinweis|strlen > 0}
    <div class="alert alert-success"><i class="fa fa-info-circle"></i> {$cHinweis}</div>
{elseif isset($hinweis) && $hinweis|strlen > 0}
    <div class="alert alert-success"><i class="fa fa-info-circle"></i> {$hinweis}</div>
{/if}

{if isset($cFehler) && $cFehler|strlen > 0}
    <div class="alert alert-danger"><i class="fa fa-warning"></i> {$cFehler}</div>
{elseif isset($fehler) && $fehler|strlen > 0}
    <div class="alert alert-danger"><i class="fa fa-warning"></i> {$fehler}</div>
{/if}