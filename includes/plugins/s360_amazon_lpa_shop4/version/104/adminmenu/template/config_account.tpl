<script type="text/javascript">
    var s360_lpa_admin_url = '{$oPlugin->cAdminmenuPfadURL}';
</script>
<script type="text/javascript" src="{$oPlugin->cAdminmenuPfadURL}js/admin-mws-access-config.js" charset="UTF8"></script>

<div id="settings">
    {* Simple Path form *}
    <div class="panel panel-default">
        <div class="panel-heading"><h3 class="panel-title">Automatische Konfiguration</h3></div>
        <div class="panel-body">
            <form method="post" id="lpa-simple-path-form" target="_blank" action="https://sellercentral-europe.amazon.com/hz/me/sp/redirect?ld=SPEXDEAPA-JTLPL">
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
                <button type="submit" value="submit" class="btn btn-primary"><i class="fa fa-external-link"></i> Amazon Payments Account registrieren</button>
            </form>
        </div>
        <div class="panel-body">
            <p style="font-weight: bold;">Sie haben bereits einen Amazon Payments Account?</p>
            <p>Gehen Sie zur <a href="https://sellercentral-europe.amazon.com/hz/me/integration/details" target="_blank">&Uuml;bersichtsseite in der Sellercentral</a> und f&uuml;gen Sie hier den Code ein, der Ihnen beim Klick auf "Zugangsdaten kopieren" angezeigt wird:</p>
            <form method="post" id="lpa-simple-path-return-form" action="{$pluginAdminUrl}cPluginTab=Einstellungen%20Account">
                {$s360_jtl_token}
                <input type="hidden" name="lpa_simplepath_return" value="1"/>
                <textarea name="lpa_simplepath_json" rows="10" class="form-control"></textarea>
                <div class="save_wrapper">
                    <button type="submit" value="submit" class="btn btn-primary"><i class="fa fa-save"></i> Daten &uuml;bernehmen</button>
                </div>
            </form>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading"><h3 class="panel-title">Manuelle Konfiguration</h3></div>
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
                        <label for="merchant_id">Merchant-ID / H&auml;ndlernummer</label>
                        <span>&nbsp;(<a href="https://sellercentral-europe.amazon.com/gp/pyop/seller/account/settings/user-settings-view.html/ref=ps_pyopiset_dnav_pyopiset_" target="_blank">Link</a>)</span>
                    </div>
                    <input id="merchant_id" class="form-control" type="text" name="merchant_id" value="{if isset($s360_lpa_config.merchant_id)}{$s360_lpa_config.merchant_id}{/if}" />
                </div>
                <div class="item">
                    <div class="name">
                        <label for="mws_access_key">MWS Access Key</label>
                        <span>&nbsp;(<a href="https://sellercentral-europe.amazon.com/gp/pyop/seller/mwsaccess/ref=py_pyopacc_dnav_home_" target="_blank">Link</a>)</span>
                    </div>
                    <input id="mws_access_key" class="form-control" type="text" name="mws_access_key" value="{if isset($s360_lpa_config.mws_access_key)}{$s360_lpa_config.mws_access_key}{/if}" size="60" />
                </div>
                <div class="item">
                    <div class="name">
                        <label for="mws_secret_key">MWS Secret Key</label>
                    </div>
                    <input id="mws_secret_key" class="form-control" type="password" name="mws_secret_key" value="{if isset($s360_lpa_config.mws_secret_key)}{$s360_lpa_config.mws_secret_key}{/if}" size="60" />
                </div>
                <div class="item">
                    <div class="name">
                        <label for="mws_environment">Sandbox</label>
                    </div>
                    <select id="mws_environment" name="mws_environment" class="form-control combo">
                        <option value="sandbox" {if !isset($s360_lpa_config.mws_environment) || $s360_lpa_config.mws_environment === "sandbox" || empty($s360_lpa_config.mws_environment)}selected{/if}>Sandbox</option>
                        <option value="production" {if isset($s360_lpa_config.mws_environment) && $s360_lpa_config.mws_environment !== "sandbox" && !empty($s360_lpa_config.mws_environment)}selected{/if}>Produktion</option>
                    </select>
                </div>
                <div class="item">
                    <div class="name">
                        <label for="mws_region">Land</label>
                    </div>
                    <select id="mws_region" name="mws_region" class="form-control combo">
                        <option value="de" {if !isset($s360_lpa_config.mws_region) || $s360_lpa_config.mws_region === "de" || empty($s360_lpa_config.mws_region)}selected{/if}>DE</option>
                        <option value="uk" {if isset($s360_lpa_config.mws_region) && $s360_lpa_config.mws_region === "uk"}selected{/if}>UK</option>
                        <option value="us" {if isset($s360_lpa_config.mws_region) && $s360_lpa_config.mws_region === "us"}selected{/if}>US</option>
                    </select>
                </div>
                <div class="save_wrapper">
                    <div id="check-mws-access-button" class="btn btn-default"><i class="fa fa-search"></i> Account-Daten jetzt &uuml;berpr&uuml;fen</div>
                    <div id="check-mws-access-feedback" style="display:none;"></div>
                </div>

                <div class="category">
                    Amazon Login-mit-Amazon-Daten
                </div>
                <p class="box_info">Geben Sie hier Ihre Login-mit-Amazon-Daten ein, wie Sie sie in der Seller Central vorfinden.</p>
                <div class="item">
                    <div class="name">
                        <label for="lpa_client_id">Client ID</label>
                        <span>&nbsp;(<a href="https://sellercentral-europe.amazon.com/gp/utilities/set-rainier-prefs.html?ie=UTF8&url=&marketplaceID=A3M0X91256QL5Q" target="_blank">Link</a>)</span>
                    </div>
                    <input id="lpa_client_id" class="form-control" type="text" name="lpa_client_id" value="{if isset($s360_lpa_config.lpa_client_id)}{$s360_lpa_config.lpa_client_id}{/if}" size="60"/>
                </div>
                <div class="item">
                    <div class="name">
                        <label for="lpa_client_secret">Client Secret</label>
                    </div>
                    <input id="lpa_client_secret" class="form-control" type="password" name="lpa_client_secret" value="{if isset($s360_lpa_config.lpa_client_secret)}{$s360_lpa_config.lpa_client_secret}{/if}" size="60"/>
                </div>

                <div class="category">
                    Konfigurationsinformationen
                </div>
                <div class="item">
                    <div class="name">
                        <label for="lpa_ipn_url">Ihre H&auml;ndler-URL</label>
                    </div>
                    <div id="lpa_ipn_url">
                        {$s360_lpa_config.lpa_ipn_url}
                    </div>
                </div>
                <div class="item">
                    <div class="name">
                        <label for="lpa_allowed_js_origin">Ihr Allowed JavaScript Origin</label>
                    </div>
                    <div id="lpa_allowed_js_origin" style="display: inline-block;">
                        {$s360_lpa_config.lpa_allowed_js_origin}
                    </div>
                </div>
                <div class="item">
                    <div class="name">
                        <label for="lpa_allowed_return_urls">Ihre Return-URLs</label>
                    </div>
                    <div id="lpa_allowed_return_urls" style="display: inline-block;">
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