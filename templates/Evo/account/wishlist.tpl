{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}

<form method="post" action="{get_static_route id='jtl.php'}" name="Wunschliste">
    {$jtl_token}
    {block name="wishlist"}
    <input type="hidden" name="wla" value="1" />
    <input type="hidden" name="wl" value="{$CWunschliste->kWunschliste}" />

    {if isset($wlsearch)}
        <input type="hidden" name="wlsearch" value="1" />
        <input type="hidden" name="cSuche" value="{$wlsearch}" />
    {/if}

    <div id="edit-widhlist-name">
        <div class="input-group">
            <span class="input-group-addon">
                <strong>{lang key="name" section="global"}</strong>
            </span>
            <input id="wishlist-name" type="text" class="form-control" placeholder="name" name="WunschlisteName" value="{$CWunschliste->cName}" autofocus />
            <input type="submit" class="hidden" />
        </div>
    </div>

    {if !empty($CWunschliste->CWunschlistePos_arr)}
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>{lang key="wishlistProduct" section="login"}</th>
                    <th class="hidden-xs hidden-sm">&nbsp;</th>
                    <th>{lang key="wishlistComment" section="login"}</th>
                    <th class="text-center">{lang key="wishlistPosCount" section="login"}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            {foreach name=wunschlistepos from=$CWunschliste->CWunschlistePos_arr item=CWunschlistePos}
                <tr>
                    <td class="img-col hidden-xs hidden-sm">
                        <a href="{$CWunschlistePos->Artikel->cURL}">
                            <img alt="{$CWunschlistePos->Artikel->cName}" src="{$CWunschlistePos->Artikel->cVorschaubild}" class="img-responsive">
                        </a>
                    </td>
                    <td>
                        <div class="text-content">
                            <a href="{$CWunschlistePos->Artikel->cURL}">{$CWunschlistePos->cArtikelName}</a>
                            {if $CWunschlistePos->Artikel->Preise->fVKNetto == 0 && $Einstellungen.global.global_preis0 === 'N'}
                                <p>{lang key="priceOnApplication" section="global"}</p>
                            {else}
                                {if isset($CWunschlistePos->Artikel->Preise->strPreisGrafik_Detail)}
                                    {assign var=priceImage value=$CWunschlistePos->Artikel->Preise->strPreisGrafik_Detail}
                                {else}
                                    {assign var=priceImage value=null}
                                {/if}
                                {include file="productdetails/price.tpl" Artikel=$CWunschlistePos->Artikel price_image=$priceImage tplscope="wishlist"}
                            {/if}
                            {foreach name=eigenschaft from=$CWunschlistePos->CWunschlistePosEigenschaft_arr item=CWunschlistePosEigenschaft}
                                {if $CWunschlistePosEigenschaft->cFreifeldWert}
                                    <p>
                                    <b>{$CWunschlistePosEigenschaft->cEigenschaftName}:</b>
                                    {$CWunschlistePosEigenschaft->cFreifeldWert}{if $CWunschlistePos->CWunschlistePosEigenschaft_arr|@count > 1 && !$smarty.foreach.eigenschaft.last}</p>{/if}
                                {else}
                                    <p>
                                    <b>{$CWunschlistePosEigenschaft->cEigenschaftName}:</b>
                                    {$CWunschlistePosEigenschaft->cEigenschaftWertName}{if $CWunschlistePos->CWunschlistePosEigenschaft_arr|@count > 1 && !$smarty.foreach.eigenschaft.last}</p>{/if}
                                {/if}
                            {/foreach}
                        </div>
                    </td>
                    <td>
                        <div class="text-content">
                            <textarea class="form-control" rows="2" name="Kommentar_{$CWunschlistePos->kWunschlistePos}">{$CWunschlistePos->cKommentar}</textarea>
                        </div>
                    </td>
                    {if $CWunschlistePos->Artikel->Preise->fVKNetto == 0 && $Einstellungen.global.global_preis0 === "N"}
                        <td width="1%"></td>
                        <td class="text-right">
                            <div class="btn-group-vertical">
                                <a href="{get_static_route id='jtl.php'}?wl={$CWunschliste->kWunschliste}&wlplo={$CWunschlistePos->kWunschlistePos}{if isset($wlsearch)}&wlsearch=1&cSuche={$wlsearch}{/if}" class="btn btn-default" title="{lang key="wishlistremoveItem" section="login"}">
                                    <span class="fa fa-trash-o"></span>
                                </a>
                            </div>
                        </td>
                    {else}
                        <td>
                            <input name="Anzahl_{$CWunschlistePos->kWunschlistePos}" class="wunschliste_anzahl form-control" type="text" size="1" value="{$CWunschlistePos->fAnzahl|replace_delim}"><br />{$CWunschlistePos->Artikel->cEinheit}
                        </td>
                        <td class="text-right">
                            <div class="btn-group-vertical">
                                {* @todo: button href? *}
                                {if $CWunschlistePos->Artikel->bHasKonfig}
                                    <a href="{$CWunschlistePos->Artikel->cURL}" class="btn btn-default" title="{lang key="product" section="global"} {lang key="configure" section="global"}">
                                        <span class="fa fa-gears"></span>
                                    </a>
                                {else}
                                    <a href="{get_static_route id='jtl.php'}?wl={$CWunschliste->kWunschliste}&wlph={$CWunschlistePos->kWunschlistePos}{if isset($wlsearch)}&wlsearch=1&cSuche={$wlsearch}{/if}" class="btn btn-primary" title="{lang key="wishlistaddToCart" section="login"}">
                                        <span class="fa fa-shopping-cart"></span>
                                    </a>
                                {/if}
                                <a href="{get_static_route id='jtl.php'}?wl={$CWunschliste->kWunschliste}&wlplo={$CWunschlistePos->kWunschlistePos}{if isset($wlsearch)}&wlsearch=1&cSuche={$wlsearch}{/if}" class="btn btn-default" title="{lang key="wishlistremoveItem" section="login"}">
                                    <span class="fa fa-trash-o"></span>
                                </a>
                            </div>
                        </td>
                    {/if}
                </tr>
            {/foreach}
            </tbody>
        </table>
        <div class="row">
            <div class="col-xs-6 col-md-9">
                {block name="wishlist-body"}
                    {if $CWunschliste->nOeffentlich == 1}
                        <div class="input-group input-group">
                            <input type="text" name="wishlist-url" readonly="readonly" value="{$ShopURL}/index.php?wlid={$CWunschliste->cURLID}" class="form-control">
                            <span class="input-group-btn">
                                {if $Einstellungen.global.global_wunschliste_freunde_aktiv === 'Y'}
                                    <button type="submit" name="wlvm" value="1" class="btn btn-default" title="{lang key="wishlistViaEmail" section="login"}">
                                       <i class="fa fa-envelope"></i>
                                   </button>
                                {/if}
                                <button type="submit" name="wlAction" value="setPrivate" class="btn btn-default" title="{lang key="wishlistSetPrivate" section="login"}">
                                    <i class="fa fa-eye-slash"></i> <span class="hidden-xs">{lang key="wishlistSetPrivate" section="login"}</span>
                                </button>
                            </span>
                        </div>
                    {else}
                        <button type="submit" name="wlAction" value="setPublic" class="btn btn-default">
                            <i class="fa fa-eye"></i> <span class="hidden-xs">{lang key="wishlistSetPublic" section="login"}</span>
                        </button>
                    {/if}
                {/block}
            </div>
            <div class="col-xs-6 col-md-3 text-right">
                <div class="btn-group">
                    <a href="{get_static_route id='jtl.php'}?wl={$CWunschliste->kWunschliste}&wlpah=1{if isset($wlsearch)}&wlsearch=1&cSuche={$wlsearch}{/if}" class="btn btn-primary submit" title="{lang key="wishlistAddAllToCart" section="login"}"><i class="fa fa-shopping-cart"></i></a>
                    <button type="submit" title="{lang key="wishlistUpdate" section="login"}" class="btn btn-default">
                        <i class="fa fa-refresh"></i>
                    </button>
                    <a href="{get_static_route id='jtl.php'}?wl={$CWunschliste->kWunschliste}&wldl=1" class="btn btn-default submit" title="{lang key="wishlistDelAll" section="login"}"><i class="fa fa-trash-o"></i></a>
                </div>
            </div>
        </div>
    {/if}

    <script>
        $(function() {
            $('input[name="wishlist-url"]').on('focus', function() {
                $(this).select();
            });

        });
    </script>

    {/block}
</form>