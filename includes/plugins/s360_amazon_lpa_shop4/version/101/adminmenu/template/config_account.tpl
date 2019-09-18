<script type="text/javascript">
    var s360_lpa_admin_url = '{$oPlugin->cAdminmenuPfadURL}';
</script>
<script type="text/javascript" src="{$oPlugin->cAdminmenuPfadURL}js/admin-mws-access-config.js" charset="UTF8"></script>

<div id="settings">

    <div class="panel panel-default" style="display: none;">
        <!-- not rolled out by amazon yet -->
        <div class="panel-heading"><h3>Automatische Konfiguration (Amazon Simple-Path)</h3></div>
        <div class="panel-body">
            <form method="post" id="lpa-simple-path-form" target="_blank" action="https://sellercentral.amazon.com/hz/me/sp/redirect">
                <input type="hidden" name="spId" value="{$s360_sp_id}" />
                <input type="hidden" name="uniqueId" value="{$s360_sp_unique_id}" />
                <input type="hidden" name="locale" value="{$s360_sp_locale}" />
                <input type="hidden" name="allowedLoginDomains[]" value="{$s360_lpa_config.lpa_allowed_js_origin}" />
                {foreach item=url from=$s360_lpa_config.lpa_allowed_return_urls}
                    <input type="hidden" name="loginRedirectURLs[]" value="{$url}" />
                {/foreach}
                <input type="hidden" name="privacyNoticeUrl" value="{$s360_sp_privacy_notice_url}" />
                <input type="hidden" name="storeDescription" value="{$s360_sp_store_description}" />
                <input type="hidden" name="language" value="de-DE" />
                {* Not supported, the plugin is an "unhosted" solution <input type="hidden" name="returnMethod" value="GET" /> *}
                <input type="hidden" name="sandboxMerchantIPNURL" value="{$s360_lpa_config.lpa_ipn_url}" />
                {* Not supported, the plugin is an "unhosted" solution <input type="hidden" name="sandboxIntegratorIPNURL" value="" /> *}
                <input type="hidden" name="productionMerchantIPNURL" value="{$s360_lpa_config.lpa_ipn_url}" />
                {* Not supported, the plugin is an "unhosted" solution <input type="hidden" name="productionIntegratorIPNURL" value="" /> *}
                <button type="submit" value="submit" class="btn btn-primary">Simple-Path starten/fortsetzen</button>
            </form>
        </div>
        <div class="panel-body">
            Bitte f&uuml;gen Sie hier den Code ein, der Ihnen von Amazon Payments angezeigt wurde:
            <form method="post" id="lpa-simple-path-return-form" action="{$pluginAdminUrl}cPluginTab=Einstellungen%20Account">
                {$s360_jtl_token}
                <input type="hidden" name="lpa_simplepath_return" value="1"/>
                <textarea name="lpa_simplepath_json" rows="10" class="form-control"></textarea>
                <button type="submit" value="submit" class="btn btn-primary">Daten &uuml;bernehmen</button>
            </form>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading"><h3>Manuelle Konfiguration</h3></div>
        <div class="panel-body">
            <form method="post" id="lpa-account-settings-form" action="{$pluginAdminUrl}cPluginTab=Einstellungen%20Account">
                {$s360_jtl_token}
                <input type="hidden" name="{$session_name}" value="{$session_id}" />
                <input type="hidden" name="kPlugin" value="{$oPlugin->kPlugin}" />
                {* Remnant from Shop 3 *
                <input type="hidden" name="kPluginAdminMenu" value="{$oPluginAdminMenu->kPluginAdminMenu}" />
                *}
                <input type="hidden" name="Setting" value="1" />
                <input type="hidden" name="update_lpa_account_settings" value="1" />
                <div class="category first">
                    Amazon MWS-Account-Daten
                </div>
                <p class="box_info">Geben Sie hier Ihre Amazon MWS-Account-Daten ein, wie Sie sie in der Seller Central vorfinden. Sie k&ouml;nnen Ihre Eingaben pr&uuml;fen, indem
                    Sie nach der Eingabe den entsprechenden Button dr&uuml;cken.</p>
                <div class="item">
                    <div class="name">
                        <label>Merchant-ID / H&auml;ndlernummer</label>
                        <span>&nbsp;(<a href="https://sellercentral-europe.amazon.com/gp/pyop/seller/account/settings/user-settings-view.html/ref=ps_pyopiset_dnav_pyopiset_" target="_blank">Link</a>)</span>
                    </div>
                    <input class="form-control" type="text" name="merchant_id" value="{$s360_lpa_config.merchant_id}" />
                </div>
                <div class="item">
                    <div class="name">
                        <label>MWS Access Key</label>
                        <span>&nbsp;(<a href="https://sellercentral-europe.amazon.com/gp/pyop/seller/mwsaccess/ref=py_pyopacc_dnav_home_" target="_blank">Link</a>)</span>
                    </div>
                    <input class="form-control" type="text" name="mws_access_key" value="{$s360_lpa_config.mws_access_key}" size="60" />
                </div>
                <div class="item">
                    <div class="name">
                        <label>MWS Secret Key</label>
                    </div>
                    <input class="form-control" type="password" name="mws_secret_key" value="{$s360_lpa_config.mws_secret_key}" size="60" />
                </div>
                <div class="item">
                    <div class="name">
                        <label>Sandbox</label>
                    </div>
                    <select name="mws_environment" class="form-control combo">
                        <option value="sandbox" {if $s360_lpa_config.mws_environment === "sandbox" || empty($s360_lpa_config.mws_environment)}selected{/if}>Sandbox</option>
                        <option value="production" {if $s360_lpa_config.mws_environment !== "sandbox" && !empty($s360_lpa_config.mws_environment)}selected{/if}>Produktion</option>
                    </select>
                </div>
                <div class="item">
                    <div class="name">
                        <label>Land</label>
                    </div>
                    <select name="mws_region" class="form-control combo">
                        <option value="de" {if $s360_lpa_config.mws_region === "de" || empty($s360_lpa_config.mws_region)}selected{/if}>DE</option>
                        <option value="uk" {if $s360_lpa_config.mws_region === "uk"}selected{/if}>UK</option>
                        <option value="us" {if $s360_lpa_config.mws_region === "us"}selected{/if}>US</option>
                    </select>
                </div>

                <div id="check-mws-access-button" class="btn btn-default">Account-Daten jetzt &uuml;berpr&uuml;fen</div>
                <div id="check-mws-access-feedback" style="display:none;"></div>

                <div class="category">
                    Amazon Login-mit-Amazon-Daten
                </div>
                <p class="box_info">Geben Sie hier Ihre Login-mit-Amazon-Daten ein, wie Sie sie in der Seller Central vorfinden.</p>
                <div class="item">
                    <div class="name">
                        <label>Client ID</label>
                        <span>&nbsp;(<a href="https://sellercentral-europe.amazon.com/gp/utilities/set-rainier-prefs.html?ie=UTF8&url=&marketplaceID=A3M0X91256QL5Q" target="_blank">Link</a>)</span>
                    </div>
                    <input class="form-control" type="text" name="lpa_client_id" value="{$s360_lpa_config.lpa_client_id}" size="60"/>
                </div>
                <div class="item">
                    <div class="name">
                        <label>Client Secret</label>
                    </div>
                    <input class="form-control" type="password" name="lpa_client_secret" value="{$s360_lpa_config.lpa_client_secret}" size="60"/>
                </div>

                <div class="category">
                    Konfigurationsinformationen
                </div>
                <div class="item">
                    <div class="name">
                        <label>Ihre H&auml;ndler-URL</label>
                    </div>
                    <div>
                        {$s360_lpa_config.lpa_ipn_url}
                    </div>
                </div>
                <div class="item">
                    <div class="name">
                        <label>Ihr Allowed JavaScript Origin</label>
                    </div>
                    <div style="display: inline-block;">
                        {$s360_lpa_config.lpa_allowed_js_origin}
                    </div>
                </div>
                <div class="item">
                    <div class="name">
                        <label>Ihre Return-URLs</label>
                    </div>
                    <div style="display: inline-block;">
                        {foreach item=url from=$s360_lpa_config.lpa_allowed_return_urls}
                            {$url}<br />
                        {/foreach}
                    </div>
                </div>
                <div class="save_wrapper">
                    <button name="speichern" type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Speichern</button>
                </div>
            </form>
        </div>
    </div>
</div>