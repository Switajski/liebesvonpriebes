{if isset($smarty.session.Kunde->kKunde) && isset($oBox->CWunschlistePos_arr) && $oBox->CWunschlistePos_arr|@count > 0}
    {assign var=wishlistItems value=$oBox->CWunschlistePos_arr}
{elseif isset($smarty.session.Kunde->kKunde) && isset($Boxen.Wunschliste->CWunschlistePos_arr) && $Boxen.Wunschliste->CWunschlistePos_arr|@count > 0}
    {assign var=wishlistItems value=$Boxen.Wunschliste->CWunschlistePos_arr}
{/if}

{if isset($wishlistItems)}
    <section class="panel panel-default box box-wishlist" id="sidebox{$oBox->kBox}">
        <div class="panel-heading">
            <h5 class="panel-title">{lang key="wishlist" section="global"}</h5>
        </div>
        <div class="panel-body">
            {if isset($Boxen.Wunschliste->nAnzeigen)}
                {assign var=maxItems value=$Boxen.Wunschliste->nAnzeigen}
            {else}
                {assign var=maxItems value=$oBox->nAnzeigen}
            {/if}
            <ul class="comparelist list-unstyled">
                {foreach name=wunschzettel from=$wishlistItems item=oWunschlistePos}
                    {if $smarty.foreach.wunschzettel.iteration <= $maxItems}
                        <li>
                            <a class="remove pull-right" href="{$oWunschlistePos->cURL}"><span class="fa fa-trash-o"></span></a>
                            <a href="{$oWunschlistePos->Artikel->cURL}" title="{$oWunschlistePos->cArtikelName|escape:'quotes'}">
                                {if (isset($Boxen.Wunschliste->nBilderAnzeigen) && $Boxen.Wunschliste->nBilderAnzeigen === 'Y') || (isset($oBox) && $oBox->nBilderAnzeigen === 'Y')}
                                    <img alt="" src="{$oWunschlistePos->Artikel->Bilder[0]->cPfadMini}" class="img-xs">
                                {/if}
                                {$oWunschlistePos->fAnzahl|replace_delim} &times; {$oWunschlistePos->cArtikelName|truncate:25:"..."}
                            </a>
                        </li>
                    {/if}
                {/foreach}
            </ul>
            <hr>
            <a href="{get_static_route id='jtl.php'}?wl={if isset($Boxen.Wunschliste->CWunschlistePos_arr)}{$Boxen.Wunschliste->CWunschlistePos_arr[0]->kWunschliste}{else}{$oBox->CWunschlistePos_arr[0]->kWunschliste}{/if}" class="btn btn-default btn-block btn-sm">{lang key="goToWishlist" section="global"}</a>
        </div>
    </section>
{/if}  