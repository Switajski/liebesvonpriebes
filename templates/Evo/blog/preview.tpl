<div itemprop="blogPost" itemscope itemtype="https://schema.org/BlogPosting" class="panel panel-default">
    <div class="panel-heading hide-overflow">
        <div class="panel-title">
            <a itemprop="url" href="{$oNewsUebersicht->cURL}">
                <strong><span itemprop="headline">{$oNewsUebersicht->cBetreff}</span></strong>
            </a>
            <meta itemprop="mainEntityOfPage" content="{$ShopURL}/{$oNewsUebersicht->cURL}">
            <div class="text-muted pull-right v-box">
                {if empty($oNewsUebersicht->dGueltigVon)}
                    {assign var="dDate" value=$oNewsUebersicht->dErstellt}
                {else}
                    {assign var="dDate" value=$oNewsUebersicht->dGueltigVon}
                {/if}
                {if (isset($oNewsUebersicht->oAuthor))}
                    <div class="hidden-xs v-box">{include file="snippets/author.tpl" oAuthor=$oNewsUebersicht->oAuthor}</div>
                {else}
                    <div itemprop="author publisher" itemscope itemtype="http://schema.org/Organization" class="hidden">
                        <span itemprop="name">{$meta_publisher}</span>
                        <meta itemprop="url" content="{$ShopURL}">
                        <meta itemprop="logo" content="{$ShopURL}/{$ShopLogoURL}">
                    </div>
                {/if}
                {if isset($oNewsUebersicht->dErstellt)}<time itemprop="dateModified" class="hidden">{$oNewsUebersicht->dErstellt}</time>{/if}
                <time itemprop="datePublished" datetime="{$dDate}" class="hidden">{$dDate}</time><span class="v-box">{$oNewsUebersicht->dErstellt_de}</span>
                {if isset($Einstellungen.news.news_kommentare_nutzen) && $Einstellungen.news.news_kommentare_nutzen === 'Y'}
                    |
                    <a class="v-box" href="{$oNewsUebersicht->cURL}#comments" title="{lang key="readComments" section="news"}">
                        <span class="fa fa-comments"></span>
                        <span class="sr-only">
                            {if $oNewsUebersicht->nNewsKommentarAnzahl == 1}
                                {lang key="newsComment" section="news"}
                            {else}
                                {lang key="newsComments" section="news"}
                            {/if}
                        </span>
                        <em itemprop="commentCount">{$oNewsUebersicht->nNewsKommentarAnzahl}</em>
                    </a>
                {/if}
            </div>
        </div>
    </div>
    <div class="panel-body">
        <div class=" row">
            {if !empty($oNewsUebersicht->cPreviewImageFull)}
                <div class="col-sm-4 col-xs-12">
                    <a href="{$oNewsUebersicht->cURLFull}" title="{$oNewsUebersicht->cBetreff}">
                        <img src="{$ShopURL}/{$oNewsUebersicht->cPreviewImage}" alt="" class="img-responsive center-block"/>
                        <meta itemprop="image" content="{$ShopURL}/{$oNewsUebersicht->cPreviewImage}">
                    </a>
                </div>
            {/if}
            {if $oNewsUebersicht->cVorschauText|strip_tags|count_characters > 0}
                <div itemprop="description" class="{if !empty($oNewsUebersicht->cPreviewImage)}col-sm-8 {/if}col-xs-12">
                    {if $oNewsUebersicht->cText|strip_tags|count_characters < 200}
                        {$oNewsUebersicht->cVorschauText}
                    {else}
                        {$oNewsUebersicht->cText|strip_tags|truncate:200:""}
                    {/if}
                    <span class="pull-right top17">
                        <a class="news-more-link" href="{$oNewsUebersicht->cURLFull}">{lang key='moreLink' section='news'}</a>
                    </span>
                </div>
            {/if}
        </div>
    </div>
</div>