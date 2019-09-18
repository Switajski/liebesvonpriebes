<div class="reviews row">
    <div class="col-xs-12">
        {block name="productdetails-review-overview"}
        <div id="reviews-overview">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        {include file='productdetails/rating.tpl' total=$Artikel->Bewertungen->oBewertungGesamt->nAnzahl}
                        {lang key="averageProductRating" section="product rating"}
                    </h3>
                </div>
                <div class="panel-body hidden-print">
                    <form method="post" action="{get_static_route id='bewertung.php'}#tab-votes" id="article_rating">
                        {$jtl_token}
                        <div class="row">
                            {if $Artikel->Bewertungen->oBewertungGesamt->nAnzahl > 0}
                                <div id="article_votes" class="col-xs-12 col-md-6">
                                    {foreach name=sterne from=$Artikel->Bewertungen->nSterne_arr item=nSterne key=i}
                                        {assign var=int1 value=5}
                                        {math equation='x - y' x=$int1 y=$i assign='schluessel'}
                                        {assign var=int2 value=100}
                                        {math equation='a/b*c' a=$nSterne b=$Artikel->Bewertungen->oBewertungGesamt->nAnzahl c=$int2 assign='percent'}
                                        <div class="row">
                                            <div class="col-xs-12 col-sm-6 col-lg-4">
                                                {if isset($bewertungSterneSelected) && $bewertungSterneSelected === $schluessel}
                                                    <strong>
                                                {/if}
                                                {if $nSterne > 0 && (!isset($bewertungSterneSelected) || $bewertungSterneSelected !== $schluessel)}
                                                    <a href="{$Artikel->cURLFull}?btgsterne={$schluessel}#tab-votes">{$schluessel} {if $i == 4}{lang key="starSingular" section="product rating"}{else}{lang key="starPlural" section="product rating"}{/if}</a>
                                                {else}
                                                    {$schluessel} {if $i == 4}{lang key="starSingular" section="product rating"}{else}{lang key="starPlural" section="product rating"}{/if}
                                                {/if}
                                                {if isset($bewertungSterneSelected) && $bewertungSterneSelected === $schluessel}
                                                    </strong>
                                                {/if}
                                            </div>
                                            <div class="col-xs-12 col-sm-6 col-lg-8">
                                                <div class="progress">
                                                    {if $nSterne > 0}
                                                        <div class="progress-bar" role="progressbar"
                                                             aria-valuenow="{$percent|round}" aria-valuemin="0"
                                                             aria-valuemax="100" style="width: {$percent|round}%;">
                                                            {$nSterne}
                                                        </div>
                                                    {/if}
                                                </div>
                                            </div>
                                        </div>
                                    {/foreach}
                                    {if isset($bewertungSterneSelected) && $bewertungSterneSelected > 0}
                                        <p>
                                            <a href="{$Artikel->cURLFull}#tab-votes" class="btn btn-default">
                                                {lang key="allReviews" section="product rating"}
                                            </a>
                                        </p>
                                    {/if}
                                </div>
                            {/if}
                            <div class="col-xs-12 {if $Artikel->Bewertungen->oBewertungGesamt->nAnzahl === 0}col-md-10 col-md-push-1 {else}col-md-6 {/if}">
                                {if $Artikel->Bewertungen->oBewertungGesamt->nAnzahl == 0}
                                    <p>{lang key="firstReview" section="global"}: </p>
                                {else}
                                    <p>{lang key="shareYourExperience" section="product rating"}: </p>
                                {/if}
                                <input name="bfa" type="hidden" value="1" />
                                <input name="a" type="hidden" value="{$Artikel->kArtikel}" />
                                <input name="bewerten" type="submit" value="{lang key="productAssess" section="product rating"}" class="submit btn btn-primary" />
                            </div>
                        </div>
                    </form>
                </div>
            </div>{* /panel *}
        </div>{* /reviews-overview *}
        {/block}

        {if isset($Artikel->HilfreichsteBewertung->oBewertung_arr[0]->nHilfreich) &&
            $Artikel->HilfreichsteBewertung->oBewertung_arr[0]->nHilfreich > 0
        }
            <div class="review-wrapper reviews-mosthelpful panel">
                <form method="post" action="{get_static_route id='bewertung.php'}#tab-votes">
                    {$jtl_token}
                    {block name="productdetails-review-most-helpful"}
                    <input name="bhjn" type="hidden" value="1" />
                    <input name="a" type="hidden" value="{$Artikel->kArtikel}" />
                    <input name="btgsterne" type="hidden" value="{$BlaetterNavi->nSterne}" />
                    <input name="btgseite" type="hidden" value="{$BlaetterNavi->nAktuelleSeite}" />
                    <div class="panel-wrap">
                        <div class="review panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">{lang key="theMostUsefulRating" section="product rating"}</h3>
                            </div>
                            <div class="panel-body">
                                {foreach name=artikelhilfreichstebewertungen from=$Artikel->HilfreichsteBewertung->oBewertung_arr item=oBewertung}
                                    {include file="productdetails/review_item.tpl" oBewertung=$oBewertung bMostUseful=true}
                                {/foreach}
                            </div>
                        </div>
                    </div>
                    {/block}
                </form>
            </div>
        {/if}

        {if $ratingPagination->getPageItemCount() > 0}
            {include file="snippets/pagination.tpl" oPagination=$ratingPagination cThisUrl=$Artikel->cURLFull cAnchor='tab-votes'}
            <form method="post" action="{get_static_route id='bewertung.php'}#tab-votes" class="reviews-list">
                {$jtl_token}
                <input name="bhjn" type="hidden" value="1" />
                <input name="a" type="hidden" value="{$Artikel->kArtikel}" />
                <input name="btgsterne" type="hidden" value="{$BlaetterNavi->nSterne}" />
                <input name="btgseite" type="hidden" value="{$BlaetterNavi->nAktuelleSeite}" />

                {foreach name=artikelbewertungen from=$ratingPagination->getPageItems() item=oBewertung}
                    <div class="review panel panel-default {if $smarty.foreach.artikelbewertungen.last}last{/if}">
                        <div class="panel-body">
                            {include file="productdetails/review_item.tpl" oBewertung=$oBewertung}
                        </div>
                    </div>
                {/foreach}
            </form>
            {include file="snippets/pagination.tpl" oPagination=$ratingPagination cThisUrl=$Artikel->cURLFull cAnchor='tab-votes' showFilter=false}
        {/if}
    </div>{* /col *}
</div>{* /row *}
