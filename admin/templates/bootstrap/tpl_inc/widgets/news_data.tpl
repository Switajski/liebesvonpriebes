{if is_array($oNews_arr)}
    <ul class="linklist">
        {strip}
        {foreach name="news" from=$oNews_arr item=oNews}
            <li>
                <p>
                    <a class="" href="{$oNews->cUrlExt|urldecode}" target="_blank"><span class="date label label-default pull-right">{$oNews->dErstellt|date_format:"%d.%m.%Y"}</span>{$oNews->cBetreff}</a>
                </p>
            </li>
        {/foreach}
        {/strip}
    </ul>
{else}
    <div class="widget-container"><div class="alert alert-error">Keine Daten verf&uuml;gbar</div></div>
{/if}