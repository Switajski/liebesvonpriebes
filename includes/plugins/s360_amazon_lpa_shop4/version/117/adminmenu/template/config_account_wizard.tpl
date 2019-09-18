<script type="text/javascript">
    var s360_lpa_admin_url = '{$oPlugin->cAdminmenuPfadURL}';
</script>
<script type="text/javascript" src="{$oPlugin->cAdminmenuPfadURL}js/admin-mws-access-config.js" charset="UTF8"></script>

<div id="settings">
    {if isset($lpaSimplepathReturnSuccess) && $lpaSimplepathReturnSuccess|count_characters > 0}
        <div class="alert alert-success">{$lpaSimplepathReturnSuccess}</div>
    {/if}
    {if isset($lpaSimplepathReturnError) && $lpaSimplepathReturnError|count_characters > 0}
        <div class="alert alert-danger">{$lpaSimplepathReturnError}</div>
    {/if}
    <div class="panel panel-default">
        <div class="panel-heading"><h3 class="panel-title">0. Registrieren Sie sich bei Amazon Pay.</h3></div>
        <div class="panel-body">
            <div>Falls Sie noch gar keinen Amazon Pay Account haben, klicken Sie bitte auf <b>Amazon Pay Account registrieren</b> und folgen Sie den Anweisungen.</div>
            <form method="post" id="lpa-simple-path-form" target="_blank"
                  action="https://sellercentral-europe.amazon.com/hz/me/sp/redirect?ld=SPEXDEAPA-JTLPL">
                <input type="hidden" name="spId" value="{$s360_sp_id}"/>
                <input type="hidden" name="uniqueId" value="{$s360_sp_unique_id}"/>
                <input type="hidden" name="locale" value="{$s360_sp_locale}"/>
                <input type="hidden" name="allowedLoginDomains[]" value="{$s360_lpa_config.lpa_allowed_js_origin}"/>
                {foreach item=url from=$s360_lpa_config.lpa_allowed_return_urls}
                    <input type="hidden" name="loginRedirectURLs[]" value="{$url}"/>
                {/foreach}
                <input type="hidden" name="privacyNoticeUrl" value="{$s360_sp_privacy_notice_url}"/>
                <input type="hidden" name="storeDescription" value="{$s360_sp_store_description}"/>
                <input type="hidden" name="language" value="de-DE"/>
                {* Not supported, the plugin is an "unhosted" solution <input type="hidden" name="returnMethod" value="GET" /> *}
                <input type="hidden" name="sandboxMerchantIPNURL" value="{$s360_lpa_config.lpa_ipn_url}"/>
                {* Not supported, the plugin is an "unhosted" solution <input type="hidden" name="sandboxIntegratorIPNURL" value="" /> *}
                <input type="hidden" name="productionMerchantIPNURL" value="{$s360_lpa_config.lpa_ipn_url}"/>
                {* Not supported, the plugin is an "unhosted" solution <input type="hidden" name="productionIntegratorIPNURL" value="" /> *}
                <button type="submit" value="submit" class="btn btn-primary"><i class="fa fa-external-link"></i> Amazon
                    Pay Account registrieren
                </button>
            </form>
        </div>
    </div>
    <div class="s360-video s360-video-general">
        <div class="s360-video-title"><i class="fa fa-eye"></i>&nbsp;Sehen Sie das Einf&uuml;hrungsvideo hier.</div>
        <div class="s360-video-container">
            <iframe width="560" height="315" data-src="https://www.youtube-nocookie.com/embed/dqxTrcI9MSo" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">1. Importieren Sie Ihre Access Keys.</h3>
        </div>
        <div class="panel-body">
            <form method="post" id="lpa-simple-path-return-form"
                  action="{$pluginAdminUrl}cPluginTab=Einstellungen%20Account">
                {$s360_jtl_token}
                <input type="hidden" name="lpa_simplepath_return" value="1"/>
                <!-- Video not available
                <div class="s360-video">
                    <div class="s360-video-title"><i class="fa fa-eye"></i>&nbsp;Sehen Sie sich das Video an.</div>
                </div>
                -->
                <ol class="s360-ordered-list">
                    <li>
                        <a href="https://sellercentral-europe.amazon.com" target="s360amazonpay">Melden Sie sich in
                            Ihrem Konto
                            in der Seller Central in einem neuen Browserfenster an.</a>
                    </li>
                    <li>
                        Stellen Sie sicher, dass Sie sich in der Ansicht <b>Amazon Pay</b> (Produktion oder Sandbox) befinden. Sie k&ouml;nnen die aktuelle Ansicht im Dropdown oben rechts in der Seller Central sehen.
                    </li>
                    <li>Klicken Sie auf <b>Integration</b> und anschlie&szlig;end auf <b>MWS Access Key</b>.
                    </li>
                    <li>Klicken Sie auf die Schaltfl&auml;che <b>Zugangsdaten kopieren</b> in der oberen
                        rechten Ecke, um die Schl&uuml;ssel zu anzuzeigen.
                    </li>
                    <li>Markieren Sie den Text <i>inklusive der Klammern</i> im Pop-up-Fenster und kopieren
                        Sie diesen in die Zwischenablage. (Strg+C oder Rechtsklick -> Kopieren)
                    </li>
                    <li>F&uuml;gen Sie den Text in das nachfolgende Feld ein:<br/>
                        <textarea name="lpa_simplepath_json" rows="10" class="form-control"></textarea>
                    </li>
                    <li>Klicken Sie auf <b>Daten &uuml;bernehmen</b>.<br/>
                        <div class="">
                            <button type="submit" value="submit" class="btn btn-primary"><i class="fa fa-save"></i>
                                Daten &uuml;bernehmen
                            </button>
                        </div>
                    </li>
                    <li>Die Zugangsdaten sind dann automatisch gespeichert und sollten in den folgenden Feldern zu sehen sein.<br/>Falls n&ouml;tig, passen Sie den Sandbox/Produktion-Modus und Ihr Land an. Sollten Sie hier Anpassungen
                        vornehmen, denken Sie daran, mit <b>Speichern</b> die ge&auml;nderten Einstellungen erneut zu speichern.
                    </li>
                </ol>
            </form>
            <form method="post" id="lpa-account-settings-form"
                  action="{$pluginAdminUrl}cPluginTab=Einstellungen%20Account">
                {$s360_jtl_token}
                <input type="hidden" name="{$session_name}" value="{$session_id}"/>
                <input type="hidden" name="kPlugin" value="{$oPlugin->kPlugin}"/>
                <input type="hidden" name="Setting" value="1"/>
                <input type="hidden" name="update_lpa_account_settings" value="1"/>
                <table class="table s360-borderless">
                    <tr>
                        <td><label for="mws_environment">Sandbox/Produktion</label></td>
                        <td>
                            <select id="mws_environment" name="mws_environment" class="form-control combo">
                                <option value="sandbox"
                                        {if !isset($s360_lpa_config.mws_environment) || $s360_lpa_config.mws_environment === "sandbox" || empty($s360_lpa_config.mws_environment)}selected{/if}>
                                    Sandbox
                                </option>
                                <option value="production"
                                        {if isset($s360_lpa_config.mws_environment) && $s360_lpa_config.mws_environment !== "sandbox" && !empty($s360_lpa_config.mws_environment)}selected{/if}>
                                    Produktion
                                </option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="mws_region">Land</label></td>
                        <td>
                            <select id="mws_region" name="mws_region" class="form-control combo">
                                <option value="de"
                                        {if !isset($s360_lpa_config.mws_region) || $s360_lpa_config.mws_region === "de" || empty($s360_lpa_config.mws_region)}selected{/if}>
                                    Deutschland
                                </option>
                                <option value="uk"
                                        {if isset($s360_lpa_config.mws_region) && $s360_lpa_config.mws_region === "uk"}selected{/if}>
                                    UK
                                </option>
                                <option value="us"
                                        {if isset($s360_lpa_config.mws_region) && $s360_lpa_config.mws_region === "us"}selected{/if}>
                                    US
                                </option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="merchant_id">Merchant-ID / H&auml;ndlernummer</label></td>
                        <td><input id="merchant_id" class="form-control" type="text" name="merchant_id"
                                   value="{if isset($s360_lpa_config.merchant_id)}{$s360_lpa_config.merchant_id}{/if}"/>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="mws_access_key">MWS Access Key</label></td>
                        <td><input id="mws_access_key" class="form-control" type="text" name="mws_access_key"
                                   value="{if isset($s360_lpa_config.mws_access_key)}{$s360_lpa_config.mws_access_key}{/if}"
                                   size="60"/></td>
                    </tr>
                    <tr>
                        <td><label for="mws_secret_key">MWS Secret Key</label></td>
                        <td><input id="mws_secret_key" class="form-control" type="text" name="mws_secret_key"
                                   value="{if isset($s360_lpa_config.mws_secret_key)}{$s360_lpa_config.mws_secret_key}{/if}"
                                   size="60"/></td>
                    </tr>
                    <tr>
                        <td><label for="lpa_client_id">Client ID</label></td>
                        <td><input id="lpa_client_id" class="form-control" type="text" name="lpa_client_id"
                                   value="{if isset($s360_lpa_config.lpa_client_id)}{$s360_lpa_config.lpa_client_id}{/if}"
                                   size="60"/></td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            Falls Sie &Auml;nderungen an den Einstellungen vorgenommen haben, Speichern Sie bitte, damit
                            diese permanent &uuml;bernommen werden.
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div class="">
                                <div id="check-mws-access-button" class="btn btn-default"><i class="fa fa-search"></i>
                                    Account-Daten jetzt &uuml;berpr&uuml;fen
                                </div>
                                <div id="check-mws-access-feedback" style="display:none;"></div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div class="">
                                <button name="speichern" type="submit" class="btn btn-primary"><i
                                            class="fa fa-save"></i> Speichern
                                </button>
                            </div>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">2. F&uuml;gen Sie die URL Ihrer sofortigen Zahlungsbenachrichtigung (Instant Payment
                Notification / IPN / H&auml;ndler-URL) zu Ihrem Konto in der Seller Central hinzu.</h3>
        </div>
        <div class="panel-body">
            <div class="s360-video">
                <div class="s360-video-title"><i class="fa fa-eye"></i>&nbsp;Sehen Sie sich das Video an.</div>
                <div class="s360-video-container">
                    <iframe width="560" height="315" data-src="https://www.youtube-nocookie.com/embed/VVuULp_AtZ4" frameborder="0"
                            allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen></iframe>
                </div>
            </div>
            <div>
                Ihre URL f&uuml;r sofortige Zahlungsbenachrichtigung (H&auml;ndler-URL):
                <div class="input-group">
                    <span class="input-group-btn">
                         <button title="In Zwischenablage kopieren" class="btn btn-default s360-copy-to-clipboard" data-target="#s360-ipn-url-ro"><i class="fa fa-clipboard"></i>&nbsp;</button>
                    </span>
                    <input type="text" id="s360-ipn-url-ro" class="form-control" readonly="readonly" value="{$s360_lpa_config.lpa_ipn_url}"/>
                </div>
            </div>
            <ol class="s360-ordered-list">
                <li>Kopieren Sie die H&auml;ndler-URL in Ihre Zwischenablage: <a href="#"
                                                                                          class="s360-copy-to-clipboard"
                                                                                          data-target="#s360-ipn-url-ro">Klicken
                        Sie hier</a></li>
                <li>
                    <a href="https://sellercentral-europe.amazon.com"
                       target="s360amazonpay">Melden Sie sich in Ihrem Konto
                        in der Seller Central in einem neuen Browserfenster an.</a>
                </li>
                <li>
                    Stellen Sie sicher, dass Sie sich in der Ansicht <b>Amazon Pay</b> (Produktion oder Sandbox) befinden. Sie k&ouml;nnen die aktuelle Ansicht im Dropdown oben rechts in der Seller Central sehen.
                </li>
                <li>
                    Gehen Sie auf <i>Einstellungen > Integrationseinstellungen</i>.
                </li>
                <li>
                    F&uuml;gen Sie die URL in das Feld <b>H&auml;ndler-URL</b> (engl.: Merchant URL) ein.
                </li>
                <li>
                    Wechseln Sie oben rechts die Auswahl der Ansicht von Produktion auf Sandbox
                    (oder umgekehrt) und wiederholen Sie den letzten Schritt.
                </li>
                <li>Pr&uuml;fen Sie, ob Ihre H&auml;ndler-URL erreichbar ist: <a href="{$s360_lpa_config.lpa_ipn_url}?lpacheck" target="_blank">Klicken Sie hier</a><br/>
                    Es sollte Ihnen eine Meldung mit dem Inhalt "IPN is reachable" gezeigt werden.
                </li>
            </ol>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">3. F&uuml;gen Sie Ihren "Zul&auml;ssigen JavaScript-Ursprung" zu Ihrem Konto in der Seller Central hinzu.</h3>
        </div>
        <div class="panel-body">
            <div class="s360-video">
                <div class="s360-video-title"><i class="fa fa-eye"></i>&nbsp;Sehen Sie sich das Video an.</div>
                <div class="s360-video-container">
                    <iframe width="560" height="315" data-src="https://www.youtube.com/embed/om6T24hTPvg" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                </div>
            </div>
            <div>
                Zul&auml;ssiger JavaScript-Ursprung:
                <div class="input-group">
                    <span class="input-group-btn">
                         <button title="In Zwischenablage kopieren" class="btn btn-default s360-copy-to-clipboard" data-target="#s360-allowed-origin-url-ro"><i class="fa fa-clipboard"></i>&nbsp;</button>
                    </span>
                    <input type="text" id="s360-allowed-origin-url-ro" class="form-control" readonly="readonly" value="{$s360_lpa_config.lpa_allowed_js_origin}" style="max-width: 50%;"/>
                </div>
            </div>
            <ol class="s360-ordered-list">
                <li>Kopieren Sie die URL in Ihre Zwischenablage: <a href="#" class="s360-copy-to-clipboard" data-target="#s360-allowed-origin-url-ro">Klicken Sie hier</a></li>
                <li><a href="https://sellercentral-europe.amazon.com" target="s360amazonpay">Melden Sie sich in Ihrem Konto in der Seller Central in einem neuen Browserfenster an.</a></li>
                <li>Stellen Sie sicher, dass als Ansicht in der Seller Central <b>Login mit Amazon</b> ausgew&auml;hlt ist.</li>
                <li>Falls Sie in der Mitte keine Anwendungsinformationen sehen, registrieren Sie zun&auml;chst eine neue Anwendung.</li>
                <li>Klicken Sie unten auf <b>Bearbeiten</b>.</li>
                <li>F&uuml;gen Sie die kopierte URL in das Feld <b>Zul&auml;ssige JavaScript-Urspr&uuml;nge</b> ein und speichern Sie.</li>
            </ol>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">4. F&uuml;gen Sie Ihre "Zul&auml;ssigen R&uuml;ckleitungs-URLs"
                zu Ihrem Konto in der Seller Central hinzu.</h3>
        </div>
        <div class="panel-body">
            <div class="s360-video">
                <div class="s360-video-title"><i class="fa fa-eye"></i>&nbsp;Sehen Sie sich das Video an.</div>
                <div class="s360-video-container">
                    <iframe width="560" height="315" data-src="https://www.youtube.com/embed/om6T24hTPvg" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                </div>
            </div>
            <div>
                {foreach item=url from=$s360_lpa_config.lpa_allowed_return_urls name="returnurls"}
                    {$smarty.foreach.returnurls.iteration}. zul&auml;ssige R&uuml;ckleitungs-URL:
                    <div class="input-group">
                        <span class="input-group-btn">
                             <button title="In Zwischenablage kopieren" class="btn btn-default s360-copy-to-clipboard" data-target="#s360-return-url-{$smarty.foreach.returnurls.iteration}-ro"><i class="fa fa-clipboard"></i>&nbsp;</button>
                        </span>
                        <input type="text" id="s360-return-url-{$smarty.foreach.returnurls.iteration}-ro" class="form-control" readonly="readonly" value="{$url}" style="max-width: 50%;"/>
                    </div>
                {/foreach}
            </div>
            <ol class="s360-ordered-list">
                <li><a href="https://sellercentral-europe.amazon.com" target="s360amazonpay">Melden Sie
                        sich in Ihrem
                        Konto in der Seller Central in einem neuen Browserfenster an.</a></li>
                <li>
                    Stellen Sie sicher, dass als Ansicht in der Seller Central <b>Login mit Amazon</b> ausgew&auml;hlt ist.
                </li>
                <li>
                    Falls Sie in der Mitte keine Anwendungsinformationen sehen, registrieren Sie zun&auml;chst eine neue Anwendung.
                </li>
                <li>
                    Klicken Sie unten auf <b>Bearbeiten</b>.
                </li>
                <li>Kopieren Sie die URLs jeweils in die Zwischenablage (Icon hinter der URL klicken)</li>
                <li>F&uuml;gen Sie jede URL in das Feld "Zul&auml;ssige R&uuml;ckleitungs-URLs" ein.</li>
                <li>Speichern Sie abschlie&szlig;end die Einstellungen.</li>
            </ol>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">5. &Uuml;berpr&uuml;fen Sie, ob Ihr Amazon Pay-H&auml;ndlerkonto verifiziert
                wurde.</h3>
        </div>
        <div class="panel-body">
            <!-- Video not available
            <div class="s360-video">
                <div class="s360-video-title"><i class="fa fa-eye"></i>&nbsp;Sehen Sie sich das Video an.</div>
            </div>
            -->
            <ul class="s360-unordered-list">
                <li>Nachdem Sie alle erforderlichen Informationen eingegeben haben, erfolgt die Kontoverifizierung durch Amazon Pay.</li>
                <li>&Uuml;berpr&uuml;fen Sie regelm&auml;&szlig;ig Ihre E-Mails. Sollte Amazon Pay zus&auml;tzliche Informationen von Ihnen ben&ouml;tigen, um mit der Verifizierung fortzufahren, erhalten Sie eine Benachrichtigung und eine E-Mail.</li>
                <li>Benachrichtigungen werden auch in Ihrem Konto in der Seller Central gespeichert. Um die Benachrichtigungen in der Seller Central anzuzeigen, klicken Sie auf <b>Kundenzufriedenheit</b> und anschlie&szlig;end auf <b>Benachrichtigungen.</b></li>
            </ul>
        </div>
    </div>
</div>
