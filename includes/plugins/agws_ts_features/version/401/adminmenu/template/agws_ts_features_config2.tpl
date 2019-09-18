<div id="ts_features_wrapper2" class="{$ts_css_class}">
    {if $ts_id_all_arr|@count != 0}
        {if $ts_message !=""}
            <div class="{$ts_message_class}">
                <span>{$ts_message}</span>
            </div>
        {/if}
        <div class="panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Trusted Shops ID konfigurieren</h3>
            </div>
        </div>
        <div class="vspacer20"></div>
        <form id="ts_id_options" name="ts_id_options" action="" method="post">
            {foreach from=$ts_id_all_arr item=ts_id_all}
                <div class="label_tsid">Trusted Shops ID:</div>
                <div class="input_tsid">
                    <input type="text" name="ts_id" id="ts_id" size="50" readonly="readonly" value="{$ts_id}"/>
                </div>
                <div class="clear"></div>
                <div class="label_tssprache">Shop-Sprache:</div>
                <div class="select_tssprache">
                    <select name="ts_sprache">
                        <option value="0" disabled {if $ts_id_all->iTS_Sprache==0}selected="selected"{/if}>Wähle Shop-Sprache</option>
                        {foreach from=$ts_id_shopsprachen item=ts_id_sprache}
                            <option value="{$ts_id_sprache->kSprache}" {if $ts_id_sprache->kSprache == $ts_id_all->iTS_Sprache}selected="selected"{/if}>{$ts_id_sprache->cNameDeutsch}</option>
                        {/foreach}
                    </select>
                </div>
                <div class="clear"></div>
                <h4>Das Trustbadge für Ihren Shop</h4>
                <span>So einfach nutzen Sie den Service von Trusted Shops (<a href="https://www.trustedshops.com/integration/?shop_id={$ts_id}&backend_language={$smarty.const.TS_URL_BACKEND_LANGUAGE}&shopsw={$smarty.const.TS_URL_SHOPSW}&shopsw_version={$smarty.const.TS_URL_SHOPSW_VERSION}&plugin_version={$smarty.const.TS_URL_PLUGIN_VERSION}&context=trustbadge" target="_blank">Integrationsanleitung</a>):</span><br>
                <ol>
                    <li style="list-style: outside none decimal; padding-left: 0px;">Erstellen Sie <a href="https://www.trustedshops.com/integration/?shop_id={$ts_id}&backend_language={$smarty.const.TS_URL_BACKEND_LANGUAGE}&shopsw={$smarty.const.TS_URL_SHOPSW}&shopsw_version={$smarty.const.TS_URL_SHOPSW_VERSION}&plugin_version={$smarty.const.TS_URL_PLUGIN_VERSION}&context=trustbadge" target="_blank">hier</a> zuerst Ihren individuellen Trustbadge Code</li>
                    <li style="list-style: outside none decimal; padding-left: 0px;">Fügen Sie den generierten Code anschließend hier ein:</li>
                </ol>
                <div class="ts_badgecode">
                    <textarea rows="10" cols="120" name="ts_BadgeCode" placeholder="Bitte TrustBadge-Code hier einfügen ...">{$ts_id_all->cTS_BadgeCode}</textarea>
                </div>
                <span>
                    Dieser Code zeigt das Trustbadge mit Gütesiegel und Kundenbewertungen in Ihrem Shop an, sobald Sie unten rechts auf "Speichern" geklickt haben.
                </span>
                <div class="vspacer30"></div>
                <h4>Wählen Sie aus, welche weiteren Services Sie nutzen möchten:</h4>
                <div style="width: 60%; float: left;">
                    <b>Kundenbewertungswidget</b>
                    <p>
                        <small>Das Kundenbewertungswidget zeigt Ihre Note mit Sternen und der letzten Bewertung zusätzlich zum Trustbadge an der von Ihnen gewählten Position.</small>
                    </p>
                </div>
                <div style="width: 30%; float: right; text-align: left;">
                    <input type="hidden" name="ts_RatingWidgetShow" value="0">
                    <input type="checkbox" name="ts_RatingWidgetShow" value="1" {if $ts_id_all->bTS_RatingWidgetShow==1}checked="checked"{/if} >&nbsp;&nbsp;&nbsp;
                    <select name="ts_RatingWidgetPosition">
                        <option value="2" {if $ts_id_all->iTS_RatingWidgetPosition=="2"}selected="selected"{/if}>Sidebar rechts (empfohlen)</option>
                        <option value="1" {if $ts_id_all->iTS_RatingWidgetPosition=="1"}selected="selected"{/if}>Sidebar links (alternativ)</option>
                        <option value="3" {if $ts_id_all->iTS_RatingWidgetPosition=="3"}selected="selected"{/if}>Footer</option>
                    </select>
                </div>
                <div class="clear vspacer10"></div>
                <div style="width: 60%; float: left;">
                    <b>Review Sticker aktivieren</b>
                    <p>
                        <small>Der Review Sticker zeigt Ihre aktuellsten Bewertungen im Bannerformat in Ihrem Shop.<br>Hinweis:<br>Entscheiden Sie sich für die Darstellungsvariante "Sidebar links" oder "Sidebar rechts" müssen Sie weiter unten den Review Sticker Code editieren und den Parameter "variant" auf "skyscraper_vertical" ändern.</small>
                    </p>
                </div>
                <div style="width: 30%; float: right; text-align: left;">
                    <input type="hidden" name="ts_ReviewStickerShow" value="0">
                    <input type="checkbox" name="ts_ReviewStickerShow" value="1" {if $ts_id_all->bTS_ReviewStickerShow==1}checked="checked"{/if}>&nbsp;&nbsp;&nbsp;
                    <select name="ts_ReviewStickerPosition">
                        <option value="3" {if $ts_id_all->iTS_ReviewStickerPosition=="3"}selected="selected"{/if}>Footer (empfohlen)</option>
                        <option value="1" {if $ts_id_all->iTS_ReviewStickerPosition=="1"}selected="selected"{/if}>Sidebar links</option>
                        <option value="2" {if $ts_id_all->iTS_ReviewStickerPosition=="2"}selected="selected"{/if}>Sidebar rechts</option>
                    </select>
                </div>
                <div class="clear vspacer10"></div>
                <div style="width: 60%; float: left;">
                    <b>Product Sticker aktivieren</b>
                    <p>
                        <small>Der Product Sticker zeigt Ihre Artikel-Bewertungen auf der Artikeldetailseite im Register-Tab an.</small>
                    </p>
                </div>
                <div style="width: 30%; float: right; text-align: left;">
                    <input type="hidden" name="ts_ProductStickerShow" value="0">
                    <input type="checkbox" name="ts_ProductStickerShow" value="1" {if $ts_id_all->bTS_ProductStickerShow==1}checked="checked"{/if}>&nbsp;&nbsp;&nbsp;
                    <select name="ts_ProductStickerArt">
                        <option value="1" {if $ts_id_all->iTS_ProductStickerArt=="1"}selected="selected"{/if}>Standard-, Vater- und Kind-Artikel</option>
                        <option value="2" {if $ts_id_all->iTS_ProductStickerArt=="2"}selected="selected"{/if}>Standard- und Kind-Artikel</option>
                    </select>
                </div>
                <div class="clear vspacer10"></div>
                <div style="width: 60%; float: left;">
                    <b>Rich Snippets</b></label>
                    <p>
                        <small>Rich Snippets sorgen dafür, dass bei Suchergebnissen in Google Ihre Sterne angezeigt werden. Wählen sie aus, wo Sie Rich Snippet aktivieren möchten:</small>
                    </p>
                    <small><a href="https://support.google.com/webmasters/answer/146750?hl=en" target="_blank">Google Rich Snippets Dokumentation</a></small>
                </div>
                <div style="width: 30%; float: right; text-align: left;">
                    <input type="hidden" name="ts_RichSnippetsCategory" value="0">
                    <input type="checkbox" name="ts_RichSnippetsCategory" value="1" {if $ts_id_all->bTS_RichSnippetsCategory==1}checked="checked"{/if}><span>&nbsp;&nbsp;&nbsp;Kategorieseiten</span><br/>
                    <input type="hidden" name="ts_RichSnippetsProduct" value="0">
                    <input type="checkbox" name="ts_RichSnippetsProduct" value="1" {if $ts_id_all->bTS_RichSnippetsProduct==1}checked="checked"{/if}><span>&nbsp;&nbsp;&nbsp;Produktdetailseiten</span><br/>
                    <input type="hidden" name="ts_RichSnippetsMain" value="0">
                    <input type="checkbox" name="ts_RichSnippetsMain" value="1" {if $ts_id_all->bTS_RichSnippetsMain==1}checked="checked"{/if}><span>&nbsp;&nbsp;&nbsp;Startseite (nicht empfohlen)</span>
                </div>
                <div class="clear vspacer10"></div>
                <div class="clear">
                    <a class="btn btn-default btn-sm-txt" href="#" onclick="return false;" id="btn_show_reviewstickercode"><i class="fa fa-cogs fa-fw"></i><small>&nbsp;Review Sticker Code editieren</small></a>
                </div>
                <hr>
                <div id="hide_reviewstickercode">
                    <div>
                        <div class="label_tsreviestickercode">Review Sticker Code editieren</div>
                        <textarea rows="10" cols="120" name="ts_ReviewStickerCode" placeholder="Bitte ReviewSticker-Code hier einfügen ...">{$ts_id_all->cTS_ReviewStickerCode}</textarea>
                    </div>
                    <div class="clear vspacer10"></div>
                    <div>
                        <a class="btn btn-default btn-sm-txt" href="#" onclick="return false;" id="btn_hide_reviewstickercode"><i class="fa fa-cogs fa-fw"></i>&nbsp;Schließen</a>
                    </div>
                    <hr>
                </div>
                <div class="tright">
                    <a id="btn_ts_id_cancel" class="btn btn-default" href="#" onclick="return false;"><i class="fa fa-ban fa-fw"></i>&nbsp;Abbrechen</a>
                    <a id="btn_ts_id_save" class="btn btn-default" href="#" onclick="return false;"><i class="fa fa-plus fa-fw"></i>&nbsp;Speichern</a>
                </div>
            {/foreach}
        </form>
        <script type="text/javascript">
            {literal}
                $(document).ready(function(){
                    $('#btn_show_reviewstickercode').click(function(){
                        $('#hide_reviewstickercode').fadeIn({duration: "fast", easing: "linear"});
                    })

                    $('#btn_hide_reviewstickercode').click(function(){
                        $('#hide_reviewstickercode').fadeOut({duration: "fast", easing: "linear"});
                    })

                    $('#hide_reviewstickercode').hide();

                    $('#btn_ts_id_cancel').click(function(){
                        $('form[name=ts_id_options]').attr('action','{/literal}{$ts_id_cancel_form_action}{literal}');
                        $('form[name=ts_id_options]').append('<input type="hidden" name="ts_id_options_cancel" value="1">');
                        $('form[name=ts_id_options]').submit();
                    });

                    $('#btn_ts_id_save').click(function(){
                        $('form[name=ts_id_options]').attr('action','{/literal}{$ts_id_save_form_action}{literal}');
                        $('form[name=ts_id_options]').append('<input type="hidden" name="ts_id_options_save" value="1">');
                        $('form[name=ts_id_options]').submit();
                    });
                });
            {/literal}
        </script>
    {else}
        <div class="box_info">
            <span>Bitte fügen Sie eine <b>neue Trusted Shops ID</b> ein oder wählen Sie aus der Liste der <b>installierten IDs</b> eine aus!</span>
        </div>

    {/if}

    <script language="javascript" type="text/javascript">
        {literal}
            $(document).ready(function() {
                (function($){
                    var faSpan = $('<span class="fa" style="display:none"></span>').appendTo('body');
                    if (faSpan .css('fontFamily') !== 'FontAwesome' ) {
                        // Fallback Link
                        $('head').append('<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css" rel="stylesheet">');
                    }
            //        faSpan.remove();
                })(jQuery);
            });
        {/literal}
    </script>
</div>