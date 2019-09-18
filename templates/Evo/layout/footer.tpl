{block name="content-all-closingtags"}
    {block name="content-closingtag"}
    </div>{* /content *}
    {/block}
    
    {block name="aside"}
    {has_boxes position='left' assign='hasLeftBox'}
    {if !$bExclusive && $hasLeftBox && isset($boxes) && !empty($boxes.left)}
        {block name="footer-sidepanel-left"}
        <aside id="sidepanel_left"
               class="hidden-print col-xs-12 {if $nSeitenTyp === 2} col-md-4 col-md-pull-8 {/if} col-lg-3 col-lg-pull-9">
            {block name="footer-sidepanel-left-content"}{$boxes.left}{/block}
        </aside>
        {/block}
    {/if}
    {/block}
    
    {block name="content-row-closingtag"}
    </div>{* /row *}
    {/block}
    
    {block name="content-container-block-closingtag"}
    </div>{* /container-block *}
    {/block}
    
    {block name="content-container-closingtag"}
    </div>{* /container *}
    {/block}
    
    {block name="content-wrapper-closingtag"}
    </div>{* /content-wrapper*}
    {/block}
{/block}

{block name="footer"}
{if !$bExclusive}
    <div class="clearfix"></div>
    <footer id="footer"{if isset($Einstellungen.template.theme.pagelayout) && $Einstellungen.template.theme.pagelayout === 'fluid'} class="container-block"{/if}>
        <div class="hidden-print container{if $Einstellungen.template.theme.pagelayout === 'full-width'}-fluid{/if}">
            {if isset($Einstellungen.template.theme.pagelayout) && $Einstellungen.template.theme.pagelayout !== 'fluid'}
                <div class="container-block clearfix">
            {/if}

            {block name="footer-boxes"}
            {load_boxes_raw type='bottom' assign='arrBoxBottom' array=true}
            {if isset($arrBoxBottom) && count($arrBoxBottom) > 0}
                <div class="row" id="footer-boxes">
                    {foreach name=bottomBoxes from=$arrBoxBottom item=box}
                        {if ($box.obj->kBoxvorlage != 0 && $box.obj->anzeigen === 'Y' ) ||
                        ($box.obj->kBoxvorlage == 0 && !empty($box.obj->oContainer_arr))}
                            <div class="{block name="footer-boxes-class"}col-xs-6 col-md-3{/block}">
                                {if isset($box.obj) && isset($box.tpl)}
                                    {if $smarty.foreach.bottomBoxes.iteration < 10}
                                        {assign var=oBox value=$box.obj}
                                        {include file=$box.tpl}
                                    {/if}
                                {/if}
                            </div>
                        {/if}
                    {/foreach}
                </div>
            {/if}
            {/block}

            {block name="footer-additional"}
            {if $Einstellungen.template.footer.socialmedia_footer === 'Y' || $Einstellungen.template.footer.newsletter_footer === 'Y'}
            <div class="row footer-additional">
                {if $Einstellungen.template.footer.newsletter_footer === 'Y'}
                    <div class="{block name="footer-newsletter-class"}col-xs-12 col-md-7 newsletter-footer{/block}">
                        <div class="row">
                            {block name="footer-newsletter"}
                                <div class="col-xs-12 col-sm-4">
                                    <h5>{lang key="newsletter" section="newsletter"} {lang key="newsletterSendSubscribe" section="newsletter"}
                                    </h5>
                                    <p class="info small">
                                        {lang key="unsubscribeAnytime" section="newsletter"}
                                    </p>
                                </div>
                                <form method="post" action="{get_static_route id='newsletter.php'}" class="form col-xs-12 col-sm-6">
                                    <fieldset>
                                        {$jtl_token}
                                        <input type="hidden" name="abonnieren" value="2"/>
                                        <div class="form-group">
                                            <label class="control-label sr-only" for="newsletter_email">{lang key="emailadress"}</label>
                                            <div class="input-group">
                                                <input type="email" size="20" name="cEmail" id="newsletter_email" class="form-control" placeholder="{lang key="emailadress"}">
                                                <span class="input-group-btn">
                                                    <button type="submit" class="btn btn-primary submit">
                                                        <span>{lang key="newsletterSendSubscribe" section="newsletter"}</span>
                                                    </button>
                                                </span>
                                            </div>
                                        </div>
                                    </fieldset>
                                </form>
                            {/block}
                        </div>
                    </div>
                {/if}

                {if $Einstellungen.template.footer.socialmedia_footer === 'Y'}
                    <div class="{block name="footer-socialmedia-class"}col-xs-12 col-md-5 pull-right{/block}">
                        <div class="footer-additional-wrapper pull-right">
                            {block name="footer-socialmedia"}
                                {if !empty($Einstellungen.template.footer.facebook)}
                                    <a href="{if $Einstellungen.template.footer.facebook|strpos:'http' !== 0}https://{/if}{$Einstellungen.template.footer.facebook}" class="btn-social btn-facebook" title="Facebook" target="_blank" rel="noopener"><i class="fa fa-facebook-square"></i></a>
                                {/if}
                                {if !empty($Einstellungen.template.footer.twitter)}
                                    <a href="{if $Einstellungen.template.footer.twitter|strpos:'http' !== 0}https://{/if}{$Einstellungen.template.footer.twitter}" class="btn-social btn-twitter" title="Twitter" target="_blank" rel="noopener"><i class="fa fa-twitter-square"></i></a>
                                {/if}
                                {if !empty($Einstellungen.template.footer.googleplus)}
                                    <a href="{if $Einstellungen.template.footer.googleplus|strpos:'http' !== 0}https://{/if}{$Einstellungen.template.footer.googleplus}" class="btn-social btn-googleplus" title="Google+" target="_blank" rel="noopener"><i class="fa fa-google-plus-square"></i></a>
                                {/if}
                                {if !empty($Einstellungen.template.footer.youtube)}
                                    <a href="{if $Einstellungen.template.footer.youtube|strpos:'http' !== 0}https://{/if}{$Einstellungen.template.footer.youtube}" class="btn-social btn-youtube" title="YouTube" target="_blank" rel="noopener"><i class="fa fa-youtube-square"></i></a>
                                {/if}
                                {if !empty($Einstellungen.template.footer.vimeo)}
                                    <a href="{if $Einstellungen.template.footer.vimeo|strpos:'http' !== 0}https://{/if}{$Einstellungen.template.footer.vimeo}" class="btn-social btn-vimeo" title="Vimeo" target="_blank" rel="noopener"><i class="fa fa-vimeo-square"></i></a>
                                {/if}
                                {if !empty($Einstellungen.template.footer.pinterest)}
                                    <a href="{if $Einstellungen.template.footer.pinterest|strpos:'http' !== 0}https://{/if}{$Einstellungen.template.footer.pinterest}" class="btn-social btn-pinterest" title="PInterest" target="_blank" rel="noopener"><i class="fa fa-pinterest-square"></i></a>
                                {/if}
                                {if !empty($Einstellungen.template.footer.instagram)}
                                    <a href="{if $Einstellungen.template.footer.instagram|strpos:'http' !== 0}https://{/if}{$Einstellungen.template.footer.instagram}" class="btn-social btn-instagram" title="Instagram" target="_blank" rel="noopener"><i class="fa fa-instagram"></i></a>
                                {/if}
                                {if !empty($Einstellungen.template.footer.skype)}
                                    <a href="{if $Einstellungen.template.footer.skype|strpos:'skype:' !== 0}skype:{$Einstellungen.template.footer.skype}?add{else}{$Einstellungen.template.footer.skype}{/if}" class="btn-social btn-skype" title="Skype" target="_blank" rel="noopener"><i class="fa fa-skype"></i></a>
                                {/if}
                                {if !empty($Einstellungen.template.footer.xing)}
                                    <a href="{if $Einstellungen.template.footer.xing|strpos:'http' !== 0}https://{/if}{$Einstellungen.template.footer.xing}" class="btn-social btn-xing" title="Xing" target="_blank" rel="noopener"><i class="fa fa-xing-square"></i></a>
                                {/if}
                                {if !empty($Einstellungen.template.footer.linkedin)}
                                    <a href="{if $Einstellungen.template.footer.linkedin|strpos:'http' !== 0}https://{/if}{$Einstellungen.template.footer.linkedin}" class="btn-social btn-linkedin" title="Linkedin" target="_blank" rel="noopener"><i class="fa fa-linkedin-square"></i></a>
                                {/if}
                            {/block}
                        </div>
                    </div>
                {/if}
            </div>{* /row footer-additional *}
            {/if}
            {/block}{* /footer-additional *}
            <div class="row">
                {block name="footer-language"}
                {if isset($smarty.session.Sprachen) && $smarty.session.Sprachen|@count > 1}
                    <div class="language-dropdown dropdown visible-xs col-xs-6 text-center">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" title="{lang key='selectLang'}">
                            <i class="fa fa-language"></i>
                            <span class="caret"></span>
                        </a>
                        <ul id="language-dropdown-small" class="dropdown-menu dropdown-menu-right">
                            {foreach from=$smarty.session.Sprachen item=Sprache}
                                {if $Sprache->kSprache == $smarty.session.kSprache}
                                    <li class="active lang-{$lang} visible-xs"><a>{if $lang === 'ger'}{$Sprache->cNameDeutsch}{else}{$Sprache->cNameEnglisch}{/if}</a></li>
                                {/if}
                            {/foreach}
                            {foreach from=$smarty.session.Sprachen item=oSprache}
                                {if $oSprache->kSprache != $smarty.session.kSprache}
                                    <li>
                                        <a href="{$oSprache->cURL}" class="link_lang {$oSprache->cISO}" rel="nofollow">{if $lang === 'ger'}{$oSprache->cNameDeutsch}{else}{$oSprache->cNameEnglisch}{/if}</a>
                                    </li>
                                {/if}
                            {/foreach}
                        </ul>
                    </div>
                {/if}
                {/block}
                {block name="footer-currency"}
                {if isset($smarty.session.Waehrungen) && $smarty.session.Waehrungen|@count > 1}
                    <div class="currency-dropdown dropdown visible-xs col-xs-6 text-center">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            {if $smarty.session.Waehrung->cISO === 'EUR'}
                                <i class="fa fa-eur" title="{$smarty.session.Waehrung->cName}"></i>
                            {elseif $smarty.session.Waehrung->cISO === 'USD'}
                                <i class="fa fa-usd" title="{$smarty.session.Waehrung->cName}"></i>
                            {elseif $smarty.session.Waehrung->cISO === 'GBP'}
                                <i class="fa fa-gbp" title="{$smarty.session.Waehrung->cName}"></i>
                            {else}
                                {$smarty.session.Waehrung->cName}
                            {/if} <span class="caret"></span>
                        </a>
                        <ul id="currency-dropdown-small" class="dropdown-menu dropdown-menu-right">
                            {foreach from=$smarty.session.Waehrungen item=oWaehrung}
                                <li>
                                    <a href="{$oWaehrung->cURL}" rel="nofollow">{$oWaehrung->cName}</a>
                                </li>
                            {/foreach}
                        </ul>
                    </div>
                {/if}
                {/block}
            </div>
            <div class="footnote-vat text-center">
                {if $NettoPreise == 1}
                    {lang key="footnoteExclusiveVat" section="global" assign="footnoteVat"}
                {else}
                    {lang key="footnoteInclusiveVat" section="global" assign="footnoteVat"}
                {/if}
                {if $Einstellungen.global.global_versandhinweis === 'zzgl'}
                    {lang key="footnoteExclusiveShipping" section="global" printf=$oSpezialseiten_arr[6]->cURL assign="footnoteShipping"}
                {elseif $Einstellungen.global.global_versandhinweis === 'inkl'}
                    {lang key="footnoteInclusiveShipping" section="global" printf=$oSpezialseiten_arr[6]->cURL assign="footnoteShipping"}
                {/if}
                {block name="footer-vat-notice"}
                    <p class="padded-lg-top">
                        <span class="footnote-reference">*</span> {$footnoteVat}{if isset($footnoteShipping)}{$footnoteShipping}{/if}
                    </p>
                {/block}
            </div>
        {if isset($Einstellungen.template.theme.pagelayout) && $Einstellungen.template.theme.pagelayout !== 'fluid'}
            </div>
        {/if}
        </div>{* /container *}
        <div id="copyright" {if isset($Einstellungen.template.theme.pagelayout) && $Einstellungen.template.theme.pagelayout !== 'boxed'} class="container-block"{/if}>
            {block name="footer-copyright"}
                <div class="container{if $Einstellungen.template.theme.pagelayout === 'full-width'}-fluid{/if}">
                    {if isset($Einstellungen.template.theme.pagelayout) && $Einstellungen.template.theme.pagelayout !== 'fluid'}
                        <div class="container-block clearfix">
                    {/if}
                    <ul class="row list-unstyled">
                        <li class="col-xs-12 col-md-3">
                            {if !empty($meta_copyright)}<span itemprop="copyrightHolder">&copy; {$meta_copyright}</span>{/if}
                            {if $Einstellungen.global.global_zaehler_anzeigen === 'Y'}{lang key="counter" section="global"}: {$Besucherzaehler}{/if}
                        </li>
                        <li class="col-xs-12 col-md-6 text-center">
                            {if !empty($Einstellungen.global.global_fusszeilehinweis)}
                                {$Einstellungen.global.global_fusszeilehinweis}
                            {/if}
                        </li>
                        <li class="col-xs-12 col-md-3 text-right" id="system-credits">
                            {if !Shop::isBrandfree()}
                            Powered by <a href="http://jtl-url.de/jtlshop" title="JTL-Shop" target="_blank" rel="noopener nofollow">JTL-Shop</a>
                            {/if}
                        </li>
                    </ul>
                     {if isset($Einstellungen.template.theme.pagelayout) && $Einstellungen.template.theme.pagelayout !== 'fluid'}
                        </div>
                    {/if}
                </div>
            {/block}
        </div>
    </footer>
{/if}
{/block}

{block name="main-wrapper-closingtag"}
</div> {* /mainwrapper *}
{/block}

{* JavaScripts *}
{block name="footer-js"}
    {assign var="isFluidContent" value=false}
    {if isset($Einstellungen.template.theme.pagelayout) && $Einstellungen.template.theme.pagelayout === 'fluid' && isset($Link) && $Link->bIsFluid}
        {assign var="isFluidContent" value=true}
    {/if}

    {if !$bExclusive && !$isFluidContent && isset($Einstellungen.template.theme.background_image) && $Einstellungen.template.theme.background_image !== ''}
        {if $Einstellungen.template.theme.background_image === 'custom'}
            {assign var="backstretchImgPath" value=$currentTemplateDir|cat:'themes/'|cat:$Einstellungen.template.theme.theme_default|cat:'/background.jpg'}
        {else}
            {assign var="backstretchImgPath" value=$currentTemplateDir|cat:'themes/base/images/backgrounds/background_'|cat:$Einstellungen.template.theme.background_image|cat:'.jpg'}
        {/if}
        <script>
            $(window).load(function() {
                $.backstretch('{$backstretchImgPath}');
            });
        </script>
    {/if}

    {if !empty($Einstellungen.global.global_google_analytics_id)}
        <script type="text/javascript">
            function gaOptout() {
              document.cookie = disableStr + '=true; expires=Thu, 31 Dec 2099 23:59:59 UTC; path=/';
              window[disableStr] = true;
            }

            var gaProperty = '{$Einstellungen.global.global_google_analytics_id}';
            var disableStr = 'ga-disable-' + gaProperty;
            if (document.cookie.indexOf(disableStr + '=true') > -1) {
              window[disableStr] = true;
            } else {
                var _gaq = _gaq || [];
                _gaq.push(['_setAccount', '{$Einstellungen.global.global_google_analytics_id}']);
                _gaq.push(['_gat._anonymizeIp']);
                _gaq.push(['_trackPageview']);
                (function () {ldelim}
                    var ga = document.createElement('script'),
                        s;
                    ga.type = 'text/javascript';
                    ga.async = true;
                    ga.src = ('https:' === document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
                    s = document.getElementsByTagName('script')[0];
                    s.parentNode.insertBefore(ga, s);
                {rdelim})();
            }
        </script>
    {/if}

    <script>
        jtl.load({strip}[
            {* evo js *}
            {if !isset($Einstellungen.template.general.use_minify) || $Einstellungen.template.general.use_minify === 'N'}
                {if isset($cPluginJsHead_arr)}
                    {foreach from=$cPluginJsHead_arr item="cJS"}
                        "{$cJS}?v={$nTemplateVersion}",
                    {/foreach}
                {/if}
            {else}
                {if isset($cPluginJsHead_arr) && $cPluginJsHead_arr|@count > 0}
                    "asset/plugin_js_head?v={$nTemplateVersion}",
                {/if}
            {/if}
            {if !isset($Einstellungen.template.general.use_minify) || $Einstellungen.template.general.use_minify === 'N'}
                {foreach from=$cJS_arr item="cJS"}
                    "{$cJS}?v={$nTemplateVersion}",
                {/foreach}
                {if isset($cPluginJsBody_arr)}
                    {foreach from=$cPluginJsBody_arr item="cJS"}
                        "{$cJS}?v={$nTemplateVersion}",
                    {/foreach}
                {/if}
            {else}
                "asset/jtl3.js?v={$nTemplateVersion}",
                {if isset($cPluginJsBody_arr) && $cPluginJsBody_arr|@count > 0}
                    "asset/plugin_js_body?v={$nTemplateVersion}",
                {/if}
            {/if}

            {assign var="customJSPath" value=$currentTemplateDir|cat:'/js/custom.js'}
            {if file_exists($customJSPath)}
                "{$customJSPath}?v={$nTemplateVersion}",
            {/if}
        ]{/strip});
        {if (!isset($Einstellungen.template.general.use_cron) || $Einstellungen.template.general.use_cron === 'Y') && $smarty.now % 10 === 0}
            $.get('includes/cron_inc.php');
        {/if}
    </script>
{/block}
</body>
</html>
