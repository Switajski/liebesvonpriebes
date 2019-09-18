<link rel="stylesheet" type="text/css"
      href="https://plugin-dev4-1.ag-websolutions.de/admin/templates/bootstrap/css/colorpicker.css" media="screen"/>
<script type="text/javascript"
        src="https://plugin-dev4-1.ag-websolutions.de/admin/templates/bootstrap/js/colorpicker.js?v=405"></script>
<div id="ts_features_wrapper2" class="{$ts_css_class}">
    {if $ts_id_all_arr|@count != 0}
        {foreach from=$ts_id_all_arr item=ts_id_all}
            {if $ts_message !=""}
                <div class="{$ts_message_class}">
                    <span>{$ts_message}</span>
                </div>
            {/if}
            <form id="ts_id_options" name="ts_id_options" action="" method="post">
            <!-- **** Trusted Shops Integration **** -->
            <div class="panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Trusted Shops Integration</h3>
                </div>
            </div>
            <div class="vspacer20"></div>
            <label class="tsConfig">Trusted Shops ID:</label>
            <div class="input_tsConfig">
                <input class="form-control" type="text" name="ts_id" id="ts_id" size="50" readonly="readonly"
                       value="{$ts_id}"/>
            </div>
            <div class="clear"></div>
            <label class="tsConfig">Shop-Sprache:</label>
            <div class="input_tsConfig">
                {foreach from=$ts_id_shopsprachen item=ts_id_sprache}
                    {if $ts_id_sprache->kSprache == $ts_id_all->ts_sprache}
                        <input class="form-control" type="text" name="ts_sprache_txt" id="ts_sprache_txt" size="50"
                               readonly="readonly"
                               value="{$ts_id_sprache->cNameDeutsch}"/>
                        <input type="hidden" name="ts_sprache" value="{$ts_id_all->ts_sprache}">
                    {/if}
                {/foreach}
            </div>
            <div class="clear"></div>
            <!--
            <label class="tsConfig">Shop-Sprache:</label>
            <div class="select_tsConfig">
                <select class="form-control" name="ts_sprache">
                    <option value="0" disabled {if $ts_id_all->ts_sprache==0}selected="selected"{/if}>Wähle
                        Shop-Sprache
                    </option>
                    {foreach from=$ts_id_shopsprachen item=ts_id_sprache}
                        <option value="{$ts_id_sprache->kSprache}"
                                {if $ts_id_sprache->kSprache == $ts_id_all->ts_sprache}selected="selected"{/if}>{$ts_id_sprache->cNameDeutsch}</option>
                    {/foreach}
                </select>
            </div>
            <div class="clear"></div>
            -->
            <label class="tsConfig">Modus:</label>
            <div class="select_tsConfig">
                <select id="sel_tsmodus" class="form-control" name="ts_modus">
                    <option value="S" {if $ts_id_all->ts_modus=="S"}selected="selected"{/if}>Standard Modus</option>
                    <option value="E" {if $ts_id_all->ts_modus=="E"}selected="selected"{/if}>Experten Modus</option>
                </select>
            </div>
            <div class="clear"></div>
            <div class="vspacer20"></div>
            <div class="expert-intro isExpert">
                <span>Hier können Sie Ihre Trusted Shops Integration detaillierter konfigurieren oder den neuesten Code einbinden:</span><br>
                <ul>
                    <li style="list-style: outside none disc; padding-left: 0px;">Platzieren Sie Ihr Trustbadge wo
                        immer Sie möchten
                    </li>
                    <li style="list-style: outside none disc; padding-left: 0px;">Deaktivieren Sie die mobile
                        Anzeige
                    </li>
                    <li style="list-style: outside none disc; padding-left: 0px;">Springen Sie durch einen Klick von
                        Ihren Produktbewertungssternen direkt zu Ihren Produktbewertungen
                    </li>
                </ul>
                <span>Erfahren Sie mehr über Einstellungsmöglichkeiten des <a target="_blank"
                                                                              href="http://www.trustedshops.de/shopbetreiber/integration/trustbadge/trustbadge-custom/?shop_id={$ts_id}">&nbsp;<i
                                class="fa fa-external-link" aria-hidden="true"></i>&nbsp;Trustbadge</a> oder von <a
                            target="_blank"
                            href="http://www.trustedshops.de/shopbetreiber/integration/product-reviews/">&nbsp;<i
                                class="fa fa-external-link" aria-hidden="true"></i>&nbsp;Produktbewertungen</a> oder <a
                            target="_blank"
                            href="https://www.trustedshops.com/reviewsticker/preview?tsid={$ts_id}">&nbsp;<i
                                class="fa fa-external-link" aria-hidden="true"></i>&nbsp;testen</a> Sie einige Konfigurationsmöglichkeiten und übernehmen Sie den Code direkt.</span>
            </div>
            <div class="clear"></div>
            <div class="vspacer20"></div>
            <div class="needhelp-intro">
                <span>Benötigen Sie Hilfe?&nbsp;&nbsp;<a href="http://support.trustedshops.com/de/apps/jtlshop"
                                                         target="_blank">&nbsp;<i class="fa fa-external-link"
                                                                                  aria-hidden="true"></i>&nbsp;hier klicken</a></span>
            </div>
            <div class="vspacer40"></div>
            <!-- **** Trusted Shops Integration **** -->
            <!-- **** Konfigurieren Sie Ihr Trustbadge **** -->
            <div class="panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Konfigurieren Sie Ihr Trustbadge</h3>
                </div>
            </div>
            <div class="vspacer20"></div>
            <div class="_isStandard">
                <label class="tsConfig">Variante:</label>
                <div class="select_tsConfig">
                    <select class="form-control" name="ts_BadgeVariante">
                        <option value="reviews"
                                {if $ts_id_all->ts_BadgeVariante=="reviews"}selected="selected"{/if}>Trustbadge mit
                            Bewertungssterne anzeigen
                        </option>
                        <option value="default"
                                {if $ts_id_all->ts_BadgeVariante=="default"}selected="selected"{/if}>Trustbadge ohne
                            Bewertungssterne anzeigen
                        </option>
                    </select>
                </div>
                <div class="clear"></div>
                <label class="tsConfig">Y-Offset:&nbsp;<em class="fa fa-info-circle"
                                                           rel="popover" title="Y-Offset"
                                                           data-content="Wählen Sie einen Abstand für das Trustbadge vom rechten unteren Rand Ihres Shops."></em>
                </label>
                <div class="input_tsConfig">
                    <input class="form-control ts_SmallNumberInput" type="number" name="ts_BadgeYOffset" min="0"
                           id="ts_BadgeYOffset"
                           value="{$ts_id_all->ts_BadgeYOffset}"/>&nbsp;px
                </div>
            </div>
            <div class="clear"></div>
            <div class="ts_BadgeCode isExpert">
                    <textarea class="form-control" rows="10" cols="120" name="ts_BadgeCode"
                              placeholder="Bitte TrustBadge-Code hier einfügen ...">{$ts_id_all->ts_BadgeCode}</textarea>
            </div>
            <div class="clear"></div>
            <div class="vspacer20"></div>
            <!-- **** Konfigurieren Sie Ihr Trustbadge **** -->
            <!-- **** Konfigurieren Sie Ihre Produktbewertungen **** -->
            <div class="panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Konfigurieren Sie Ihre Produktbewertungen</h3>
                </div>
            </div>
            <div class="vspacer20"></div>
            <div class="input_tsConfig2">
                <input type="hidden" name="ts_ProduktBewertungAktiv" value="0">
                <input id="ts_ProduktBewertungAktiv" type="checkbox" name="ts_ProduktBewertungAktiv" value="1"
                       {if $ts_id_all->ts_ProduktBewertungAktiv==1}checked="checked"{/if}>
            </div>
            <label for="ts_ProduktBewertungAktiv" class="tsConfig2">Produktbewertungen sammeln</label>
            <div class="clear"></div>
            <div class="ts_ProduktBewertungAktiv_wrapper" style="padding-left:15px;">
                <div class="expert-intro">
                    <span>Mehr Traffic, weniger Retouren:<br>
                            Schalten Sie Produktbewertungen in Ihrem Trusted Shops Paket frei.<br>
                            Loggen Sie sich dazu in Ihrem <a href="https://www.trustedshops.com/de/shop/login.html"
                                                             target="_blank">MyTS-Accounts</a>
                            ein und rufen Sie das Upgrade centre auf (<i>Mehr >> Upgrade centre</i>).</span>
                </div>
                <div class="vspacer20"></div>
                <div class="clear"></div>
                <!-- **** Produktbewertungen Register**** -->
                <div class="input_tsConfig2">
                    <input type="hidden" name="ts_ProduktBewertungRegisterAktiv" value="0">
                    <input id="ts_ProduktBewertungRegisterAktiv" type="checkbox"
                           name="ts_ProduktBewertungRegisterAktiv"
                           value="1"
                           {if $ts_id_all->ts_ProduktBewertungRegisterAktiv==1}checked="checked"{/if}>
                </div>
                <label for="ts_ProduktBewertungRegisterAktiv" class="tsConfig2">Produktbewertungen auf der
                    Produktdetailseite in einem zusätzlichen
                    Reiter anzeigen
                </label>
                <div class="vspacer20"></div>
                <div class="clear"></div>
                <div class="ts_ProduktBewertungRegisterAktiv_wrapper" style="padding-left: 25px;">
                    <label class="tsConfig">Name für Bewertungstab:&nbsp;<em class="fa fa-info-circle"
                                                                             rel="popover"
                                                                             title="Bewertungstab-Name"
                                                                             data-content="Geben Sie eine Bezeichnung für den Reiter ein, auf dem die
Produktbewertungen angezeigt werden sollen."></em>
                    </label>
                    <div class="input_tsConfig"><input class="form-control" type="text"
                                                       name="ts_ProduktBewertungRegisterNameTab"
                                                       id="ts_ProduktBewertungRegisterNameTab" size="7"
                                                       value="{$ts_id_all->ts_ProduktBewertungRegisterNameTab}"/>
                    </div>
                    <div class="clear"></div>
                    <div class="clear"></div>
                    <span class="input-group-colorpicker-wrap" style="display:inline-block;">
                        <div id="ts_ProduktBewertungRegisterRahmenfarbe_ColorPicker" style="display:inline-block">
                            <div style="background-color: {$ts_id_all->ts_ProduktBewertungRegisterRahmenfarbe}"
                                 class="colorSelector"></div>
                        </div>
                        <script type="text/javascript">
                            $('#ts_ProduktBewertungRegisterRahmenfarbe_ColorPicker').ColorPicker({
                                color: '#ffffff',
                                onShow: function (colpkr) {
                                    $(colpkr).fadeIn(500);
                                    return false;
                                },
                                onHide: function (colpkr) {
                                    $(colpkr).fadeOut(500);
                                    return false;
                                },
                                onChange: function (hsb, hex, rgb) {
                                    $('#ts_ProduktBewertungRegisterRahmenfarbe_ColorPicker div').css('backgroundColor', '#' + hex);
                                    $('#ts_ProduktBewertungRegisterRahmenfarbe').val('#' + hex);
                                }
                            });
                        </script>
                        </span>
                    <label class="tsConfig">Rahmenfarbe:</label>
                    <div class="input_tsConfigSmall"><input class="form-control" type="text"
                                                            name="ts_ProduktBewertungRegisterRahmenfarbe"
                                                            id="ts_ProduktBewertungRegisterRahmenfarbe" size="7"
                                                            value="{$ts_id_all->ts_ProduktBewertungRegisterRahmenfarbe}"/>
                    </div>
                    <div class="clear"></div>
                    <span class="input-group-colorpicker-wrap" style="display:inline-block;">
                        <div id="ts_ProduktBewertungRegisterSternfarbe_ColorPicker" style="display:inline-block">
                            <div style="background-color: {$ts_id_all->ts_ProduktBewertungRegisterSternfarbe}"
                                 class="colorSelector"></div>
                        </div>
                        <script type="text/javascript">
                            $('#ts_ProduktBewertungRegisterSternfarbe_ColorPicker').ColorPicker({
                                color: '#ffffff',
                                onShow: function (colpkr) {
                                    $(colpkr).fadeIn(500);
                                    return false;
                                },
                                onHide: function (colpkr) {
                                    $(colpkr).fadeOut(500);
                                    return false;
                                },
                                onChange: function (hsb, hex, rgb) {
                                    $('#ts_ProduktBewertungRegisterSternfarbe_ColorPicker div').css('backgroundColor', '#' + hex);
                                    $('#ts_ProduktBewertungRegisterSternfarbe').val('#' + hex);
                                }
                            });
                        </script>
                        </span>
                    <label class="tsConfig">Farbe der Sterne:</label>
                    <div class="input_tsConfigSmall"><input class="form-control" type="text"
                                                            name="ts_ProduktBewertungRegisterSternfarbe"
                                                            id="ts_ProduktBewertungRegisterSternfarbe" size="7"
                                                            value="{$ts_id_all->ts_ProduktBewertungRegisterSternfarbe}"/>
                    </div>
                    <div class="clear"></div>
                    <label class="tsConfig">Größe der Sterne:</label>
                    <div class="input_tsConfig">
                        <input class="form-control ts_SmallNumberInput" type="number"
                               name="ts_ProduktBewertungRegisterSterngroesse" min="0" id=""
                               value="{$ts_id_all->ts_ProduktBewertungRegisterSterngroesse}"/>&nbsp;px
                    </div>
                    <div class="clear"></div>
                    <!-- siehe Testprotokoll vom 08.08.17 - Checkbox soll raus
                    <div class="input_tsConfig2">
                        <input type="hidden" name="ts_ProduktBewertungRegisterLeer" value="0">
                        <input id="ts_ProduktBewertungRegisterLeer" type="checkbox"
                               name="ts_ProduktBewertungRegisterLeer"
                               value="1"
                               {if $ts_id_all->ts_ProduktBewertungRegisterLeer==1}checked="checked"{/if}>
                    </div>
                    <label for="ts_ProduktBewertungRegisterLeer" class="tsConfig2">Leeren Sticker ausblenden
                        &nbsp;<em class="fa fa-info-circle"
                                  rel="popover" title="Sticker ausblenden"
                                  data-content="Produktbewertungen ausblenden bis die erste Produktbewertung eingegangen ist; andernfalls wird ein entsprechender Hinweis eingeblendet."></em>
                    </label>
                    -->
                    <div class="clear"></div>
                    <div class="ts_producstickercode isExpert">
                            <textarea class="form-control" rows="10" cols="120" name=" ts_ProduktBewertungRegisterCode"
                                      placeholder="Bitte Produktbewertung-Code hier einfügen ...">{$ts_id_all->ts_ProduktBewertungRegisterCode}</textarea>
                    </div>
                    <div class="clear"></div>
                    <div class="vspacer20"></div>
                    <div class="ts_Sterne_jQuery_wrapper isExpert">
                        <label class="tsConfig">jQuery-Selector:&nbsp; <em class="fa fa-info-circle"
                                                                           rel="popover" title="jQuery-Selector"
                                                                           data-content="Wählen Sie, wo Ihre Produktbewertungen auf der Produktdetailseite angezeigt werden sollen."></em>
                        </label>
                        <div class="input_tsConfig"><input class="form-control" type="text"
                                                           name="ts_ProduktBewertungRegisterSelector" id=""
                                                           size="50"
                                                           value="{$ts_id_all->ts_ProduktBewertungRegisterSelector}"/>
                        </div>
                        <!-- siehe Testprotokoll vom 08.08.17/18.08.2017 - jQuery-Methode soll raus
                        <div class="clear"></div>
                        <label class="tsConfig">jQuery-Methode:&nbsp; <em class="fa fa-info-circle"
                                                                          rel="popover" title="jQuery-Methode"
                                                                          data-content="Definition fehlt"></em>
                        </label>
                        <div class="select_tsConfig">
                            <select class="form-control" name="ts_ProduktBewertungRegisterMethode">
                                <option value="append"
                                        {if $ts_id_all->ts_ProduktBewertungRegisterMethode=="append"}selected="selected"{/if}>
                                    Anhängen an Selector
                                </option>
                                <option value="prepend"
                                        {if $ts_id_all->ts_ProduktBewertungRegisterMethode=="prepend"}selected="selected"{/if}>
                                    Vorhängen an Selector
                                </option>
                                <option value="before"
                                        {if $ts_id_all->ts_ProduktBewertungRegisterMethode=="before"}selected="selected"{/if}>
                                    Vor dem Selector
                                </option>
                                <option value="after"
                                        {if $ts_id_all->ts_ProduktBewertungRegisterMethode=="after"}selected="selected"{/if}>
                                    Nach dem Selector
                                </option>
                                <option value="replaceWith"
                                        {if $ts_id_all->ts_ProduktBewertungRegisterMethode=="replaceWith"}selected="selected"{/if}>
                                    Selector ersetzen
                                </option>
                            </select>&nbsp;
                        </div>
                        -->
                        <div class="vspacer20"></div>
                        <div class="clear"></div>
                    </div>
                </div>
                <!-- **** Produktbewertungen Register**** -->
                <!-- **** Produktbewertungen Sterne**** -->
                <div class="vspacer40"></div>
                <div class="input_tsConfig2">
                    <input type="hidden" name="ts_ProduktBewertungSterneAktiv" value="0">
                    <input id="ts_ProduktBewertungSterneAktiv" type="checkbox" name="ts_ProduktBewertungSterneAktiv"
                           value="1"
                           {if $ts_id_all->ts_ProduktBewertungSterneAktiv==1}checked="checked"{/if}>
                </div>
                <label for="ts_ProduktBewertungSterneAktiv" class="tsConfig2">Produktbewertungssterne auf der
                    Produktdetailseite anzeigen</label>
                <div class="vspacer20"></div>
                <div class="clear"></div>
                <div class="ts_ProduktBewertungSterneAktiv_wrapper" style="padding-left: 25px;">
                    <div class="clear"></div>
                    <span class="input-group-colorpicker-wrap" style="display:inline-block;">
                        <div id="ts_ProduktBewertungSterneSternfarbe_ColorPicker" style="display:inline-block">
                            <div style="background-color: {$ts_id_all->ts_ProduktBewertungSterneSternfarbe}"
                                 class="colorSelector"></div>
                        </div>
                        <script type="text/javascript">
                            $('#ts_ProduktBewertungSterneSternfarbe_ColorPicker').ColorPicker({
                                color: '#ffffff',
                                onShow: function (colpkr) {
                                    $(colpkr).fadeIn(500);
                                    return false;
                                },
                                onHide: function (colpkr) {
                                    $(colpkr).fadeOut(500);
                                    return false;
                                },
                                onChange: function (hsb, hex, rgb) {
                                    $('#ts_ProduktBewertungSterneSternfarbe_ColorPicker div').css('backgroundColor', '#' + hex);
                                    $('#ts_ProduktBewertungSterneSternfarbe').val('#' + hex);
                                }
                            });
                        </script>
                        </span>
                    <label class="tsConfig">Farbe der Sterne:</label>
                    <div class="input_tsConfigSmall"><input class="form-control" type="text"
                                                            name="ts_ProduktBewertungSterneSternfarbe"
                                                            id="ts_ProduktBewertungSterneSternfarbe" size="50"
                                                            value="{$ts_id_all->ts_ProduktBewertungSterneSternfarbe}"/>
                    </div>
                    <div class="clear"></div>
                    <label class="tsConfig">Größe der Sterne:</label>
                    <div class="input_tsConfig">
                        <input class="form-control ts_SmallNumberInput" type="number"
                               name="ts_ProduktBewertungSterneSterngroesse" min="0"
                               id="ts_ProduktBewertungSterneSterngroesse"
                               value="{$ts_id_all->ts_ProduktBewertungSterneSterngroesse}"/>&nbsp;px
                    </div>
                    <div class="clear"></div>
                    <label class="tsConfig">Schriftgröße:</label>
                    <div class="input_tsConfig">
                        <input class="form-control ts_SmallNumberInput" type="number"
                               name="ts_ProduktBewertungSterneSchriftgroesse" min="0"
                               id="ts_ProduktBewertungSterneSchriftgroesse"
                               value="{$ts_id_all->ts_ProduktBewertungSterneSchriftgroesse}"/>&nbsp;px
                    </div>
                    <div class="clear"></div>
                    <div class="input_tsConfig2">
                        <input type="hidden" name="ts_ProduktBewertungSterneLeer" value="0">
                        <input id="ts_ProduktBewertungSterneLeer" type="checkbox"
                               name="ts_ProduktBewertungSterneLeer"
                               value="1"
                               {if $ts_id_all->ts_ProduktBewertungSterneLeer==1}checked="checked"{/if}>
                    </div>
                    <label for="ts_ProduktBewertungSterneLeer" class="tsConfig2">Leere Produktbewertungssterne
                        ausblenden
                        &nbsp;<em class="fa fa-info-circle"
                                  rel="popover" title="Sterne ausblenden"
                                  data-content="Produktbewertungssterne ausblenden bis die erste Produktbewertung eingegangen ist; andernfalls werden graue Platzhalter angezeigt."></em>
                    </label>
                    <div class="clear"></div>
                    <div class="ts_producstarcode isExpert">
                            <textarea class="form-control" rows="10" cols="120" name="ts_ProduktBewertungSterneCode"
                                      placeholder="Bitte Produktbewertungssterne-Code hier einfügen ...">{$ts_id_all->ts_ProduktBewertungSterneCode}</textarea>
                    </div>
                    <div class="clear"></div>
                    <div class="vspacer20"></div>
                    <div class="ts_ProduktBewertungSterne_jQuery_wrapper isExpert">
                        <label class="tsConfig">jQuery-Selector:&nbsp; <em class="fa fa-info-circle"
                                                                           rel="popover" title="jQuery-Selector"
                                                                           data-content="Wählen Sie, wo Ihre Produktbewertungssterne auf der Produktdetailseite angezeigt werden sollen."></em>
                        </label>
                        <div class="input_tsConfig"><input class="form-control" type="text"
                                                           name="ts_ProduktBewertungSterneSelector" id="" size="50"
                                                           value="{$ts_id_all->ts_ProduktBewertungSterneSelector}"/>
                        </div>
                        <!-- siehe Testprotokoll vom 08.08.17/18.08.2017 - jQuery-Methode soll raus
                        <div class="clear"></div>
                        <label class="tsConfig">jQuery-Methode:&nbsp; <em class="fa fa-info-circle"
                                                                          rel="popover" title="jQuery-Methode"
                                                                          data-content="Definition fehlt."></em>
                        </label>
                        <div class="select_tsConfig">
                            <select class="form-control" name="ts_ProduktBewertungSterneMethode">
                                <option value="append"
                                        {if $ts_id_all->ts_ProduktBewertungSterneMethode=="append"}selected="selected"{/if}>
                                    Anhängen an Selector
                                </option>
                                <option value="prepend"
                                        {if $ts_id_all->ts_ProduktBewertungSterneMethode=="prepend"}selected="selected"{/if}>
                                    Vorhängen an Selector
                                </option>
                                <option value="before"
                                        {if $ts_id_all->ts_ProduktBewertungSterneMethode=="before"}selected="selected"{/if}>
                                    Vor dem Selector
                                </option>
                                <option value="after"
                                        {if $ts_id_all->ts_ProduktBewertungSterneMethode=="after"}selected="selected"{/if}>
                                    Nach dem Selector
                                </option>
                                <option value="replaceWith"
                                        {if $ts_id_all->ts_ProduktBewertungSterneMethode=="replaceWith"}selected="selected"{/if}>
                                    Selector ersetzen
                                </option>
                            </select>
                        </div>
                        -->
                    </div>

                </div>
                <div class="vspacer20"></div>
                <div class="clear"></div>
                <!-- **** Produktbewertungen Sterne**** -->
            </div>
            <!-- **** Konfigurieren Sie Ihre Produktbewertungen **** -->
            <!-- **** Weitere Services nutzen **** -->
            <div class="vspacer40"></div>
            <div class="panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Konfigurieren Sie Ihre weiteren Produkte</h3>
                </div>
            </div>
            <div class="vspacer20"></div>
            <!-- **** Kundenbewertungswidget (deprecated) **** -->
            <div id="tsRatingWidget_wrapper" class="{if $ts_id_all->ts_RatingWidgetAktiv!=1}hidden{/if}">
                <div class="panel-default2">
                    <div class="panel-heading2">
                        <h4 class="panel-title2"><span>Kundenbewertungswidget (veraltet)</span></h4>
                        <p>
                            <small>Das Kundenbewertungswidget zeigt Ihre Note mit Sternen und der letzten Bewertung
                                zusätzlich zum Trustbadge an der von Ihnen gewählten Position.<br>
                                Das Kundebewertungswidget steht nur Kunden zur Verfügung, die es bisher schon
                                aktiviert hatten. Bei Neuinstallation bzw. Deaktivierung kann das Widget nicht mehr
                                aktiviert werden!
                            </small>
                        </p>
                    </div>
                </div>
                <div style="padding-left: 20px;">
                    <div class="input_tsConfig2">
                        <input type="hidden" name="ts_RatingWidgetAktiv" value="0">
                        <input id="ts_RatingWidgetAktiv" type="checkbox" name="ts_RatingWidgetAktiv" value="1"
                               {if $ts_id_all->ts_RatingWidgetAktiv==1}checked="checked"{/if}>
                    </div>
                    <label for="ts_RatingWidgetAktiv" class="tsConfig2">Kundenbewertungswidget aktivieren</label>
                    <div class="clear"></div>
                    <label class="tsConfig">Widget-Position:</label>
                    <div class="select_tsConfig">
                        <select class="form-control" name="ts_RatingWidgetPosition">
                            <option value="2" {if $ts_id_all->ts_RatingWidgetPosition=="2"}selected="selected"{/if}>
                                Sidebar rechts (empfohlen)
                            </option>
                            <option value="1" {if $ts_id_all->ts_RatingWidgetPosition=="1"}selected="selected"{/if}>
                                Sidebar links (alternativ)
                            </option>
                            <option value="3" {if $ts_id_all->ts_RatingWidgetPosition=="3"}selected="selected"{/if}>
                                Footer
                            </option>
                        </select>
                    </div>
                </div>
                <div class="clear"></div>
                <div class="vspacer20"></div>
            </div>
            <!-- **** Kundenbewertungswidget (deprecated) **** -->
            <!-- **** Review-Sticker **** -->
            <div id="tsReviewSticker_wrapper" class="">
                <div class="panel-default2">
                    <div class="panel-heading2">
                        <h4 class="panel-title2"><span>Review Sticker</span></h4>
                        <p>
                            <small>Der Review Sticker zeigt Ihre aktuellsten Bewertungen im Bannerformat in Ihrem
                                Shop
                                an.<br>
                                Aktivieren Sie ihn und konfigurieren Sie ihn passend zu Ihrem Shop
                            </small>
                        </p>
                    </div>
                </div>
                <div style="padding-left: 20px;">
                    <div class="input_tsConfig2">
                        <input type="hidden" name="ts_ReviewStickerAktiv" value="0">
                        <input type="checkbox" id="ts_ReviewStickerAktiv" name="ts_ReviewStickerAktiv" value="1"
                               {if $ts_id_all->ts_ReviewStickerAktiv==1}checked="checked"{/if}>
                    </div>
                    <label for="ts_ReviewStickerAktiv" class="tsConfig2">Review Sticker aktivieren</label>
                    <div class="clear"></div>
                    <div class="ts_ReviewStickerAktiv_wrapper">
                        <label class="tsConfig">Variante:</label>
                        <div class="select_tsConfig">
                            <select class="form-control" name="ts_ReviewStickerModus" id="ts_ReviewStickerModus">
                                <option value="bewertung"
                                        {if $ts_id_all->ts_ReviewStickerModus=="bewertung"}selected="selected"{/if}>
                                    Bewertungs-Sticker
                                </option>
                                <option value="kommentar"
                                        {if $ts_id_all->ts_ReviewStickerModus=="kommentar"}selected="selected"{/if}>
                                    Kommentar-Sticker
                                </option>
                            </select>
                        </div>
                        <div class="clear"></div>
                        <label class="tsConfig">Position:<br><span id="ts_ReviewStickerPositionPruefen"
                                                                   style="color:red;"><small>Bitte Position prüfen!</small>&nbsp;<em
                                        class="fa fa-info-circle"
                                        rel="popover" title="Position prüfen"
                                        data-content="Der Kommentar-Sticker kann nur in der sidebar (rechts oder links) angezeigt werden!"></em></span></label>
                        <div class="select_tsConfig">
                            <select class="form-control" name="ts_ReviewStickerPosition"
                                    id="ts_ReviewStickerPosition">
                                <option value="3"
                                        {if $ts_id_all->ts_ReviewStickerPosition=="3"}selected="selected"{/if}>
                                    Footer
                                    (empfohlen)
                                </option>
                                <option value="1"
                                        {if $ts_id_all->ts_ReviewStickerPosition=="1"}selected="selected"{/if}>
                                    Sidebar links
                                </option>
                                <option value="2"
                                        {if $ts_id_all->ts_ReviewStickerPosition=="2"}selected="selected"{/if}>
                                    Sidebar rechts
                                </option>
                            </select>
                        </div>
                        <div class="clear"></div>
                        <label class="tsConfig isTestimonial">Schriftart:</label>
                        <div class="select_tsConfig isTestimonial">
                            <select class="form-control" name="ts_ReviewStickerSchriftart">
                                <option value="Arial"
                                        {if $ts_id_all->ts_ReviewStickerSchriftart=="Arial"}selected="selected"{/if}>
                                    Arial
                                </option>
                                <option value="Geneva"
                                        {if $ts_id_all->ts_ReviewStickerSchriftart=="Geneva"}selected="selected"{/if}>
                                    Geneva
                                </option>
                                <option value="Georgia"
                                        {if $ts_id_all->ts_ReviewStickerSchriftart=="Georgia"}selected="selected"{/if}>
                                    Georgia
                                </option>
                                <option value="Helvetica"
                                        {if $ts_id_all->ts_ReviewStickerSchriftart=="Helvetica"}selected="selected"{/if}>
                                    Helvetica
                                </option>
                                <option value="Sans-serif"
                                        {if $ts_id_all->ts_ReviewStickerSchriftart=="Sans-serif"}selected="selected"{/if}>
                                    Sans-serif
                                </option>
                                <option value="Serif"
                                        {if $ts_id_all->ts_ReviewStickerSchriftart=="Serif"}selected="selected"{/if}>
                                    Serif
                                </option>
                                <option value="Trebuchet MS"
                                        {if $ts_id_all->ts_ReviewStickerSchriftart=="Trebuchet MS"}selected="selected"{/if}>
                                    Trebuchet MS
                                </option>
                                <option value="Verdana"
                                        {if $ts_id_all->ts_ReviewStickerSchriftart=="Verdana"}selected="selected"{/if}>
                                    Verdana
                                </option>
                            </select>
                        </div>
                        <div class="clear"></div>
                        <label class="tsConfig isTestimonial">Bewertungsanzahl:</label>
                        <div class="select_tsConfig isTestimonial">
                            <select class="form-control" name="ts_ReviewStickerBewertungsanzahl">
                                <option value="1"
                                        {if $ts_id_all->ts_ReviewStickerBewertungsanzahl=="1"}selected="selected"{/if}>
                                    1
                                </option>
                                <option value="2"
                                        {if $ts_id_all->ts_ReviewStickerBewertungsanzahl=="2"}selected="selected"{/if}>
                                    2
                                </option>
                                <option value="3"
                                        {if $ts_id_all->ts_ReviewStickerBewertungsanzahl=="3"}selected="selected"{/if}>
                                    3
                                </option>
                                <option value="4"
                                        {if $ts_id_all->ts_ReviewStickerBewertungsanzahl=="4"}selected="selected"{/if}>
                                    4
                                </option>
                                <option value="5"
                                        {if $ts_id_all->ts_ReviewStickerBewertungsanzahl=="5"}selected="selected"{/if}>
                                    5
                                </option>
                            </select>
                        </div>
                        <div class="clear"></div>
                        <label class="tsConfig isTestimonial">Mindestnote:</label>
                        <div class="select_tsConfig isTestimonial">
                            <select class="form-control" name="ts_ReviewStickerMindestnote">
                                <option value="0.0"
                                        {if $ts_id_all->ts_ReviewStickerMindestnote=="0.0"}selected="selected"{/if}>
                                    0.0
                                </option>
                                <option value="0.5"
                                        {if $ts_id_all->ts_ReviewStickerMindestnote=="0.5"}selected="selected"{/if}>
                                    0.5
                                </option>
                                <option value="1.0"
                                        {if $ts_id_all->ts_ReviewStickerMindestnote=="1.0"}selected="selected"{/if}>
                                    1.0
                                </option>
                                <option value="1.5"
                                        {if $ts_id_all->ts_ReviewStickerMindestnote=="1.5"}selected="selected"{/if}>
                                    1.5
                                </option>
                                <option value="2.0"
                                        {if $ts_id_all->ts_ReviewStickerMindestnote=="2.0"}selected="selected"{/if}>
                                    2.0
                                </option>
                                <option value="2.5"
                                        {if $ts_id_all->ts_ReviewStickerMindestnote=="2.5"}selected="selected"{/if}>
                                    2.5
                                </option>
                                <option value="3.0"
                                        {if $ts_id_all->ts_ReviewStickerMindestnote=="3.0"}selected="selected"{/if}>
                                    3.0
                                </option>
                                <option value="3.5"
                                        {if $ts_id_all->ts_ReviewStickerMindestnote=="3.5"}selected="selected"{/if}>
                                    3.5
                                </option>
                                <option value="4.0"
                                        {if $ts_id_all->ts_ReviewStickerMindestnote=="4.0"}selected="selected"{/if}>
                                    4.0
                                </option>
                                <option value="4.5"
                                        {if $ts_id_all->ts_ReviewStickerMindestnote=="4.5"}selected="selected"{/if}>
                                    4.5
                                </option>
                                <option value="5.0"
                                        {if $ts_id_all->ts_ReviewStickerMindestnote=="5.0"}selected="selected"{/if}>
                                    5.0
                                </option>
                            </select>
                        </div>
                        <div class="clear"></div>
                        <span class="input-group-colorpicker-wrap isTestimonial" style="display:inline-block;">
                        <div id="ts_ReviewStickerHintergrundfarbe_ColorPicker" style="display:inline-block"
                             class="isTestimonial">
                            <div style="background-color: {$ts_id_all->ts_ReviewStickerHintergrundfarbe}"
                                 class="colorSelector"></div>
                        </div>
                        <script type="text/javascript">
                            $('#ts_ReviewStickerHintergrundfarbe_ColorPicker').ColorPicker({
                                color: '#ffffff',
                                onShow: function (colpkr) {
                                    $(colpkr).fadeIn(500);
                                    return false;
                                },
                                onHide: function (colpkr) {
                                    $(colpkr).fadeOut(500);
                                    return false;
                                },
                                onChange: function (hsb, hex, rgb) {
                                    $('#ts_ReviewStickerHintergrundfarbe_ColorPicker div').css('backgroundColor', '#' + hex);
                                    $('#ts_ReviewStickerHintergrundfarbe').val('#' + hex);
                                }
                            });
                        </script>
                        </span>
                        <label class="tsConfig isTestimonial">Hintergrundfarbe:</label>
                        <div class="input_tsConfigSmall isTestimonial"><input class="form-control" type="text"
                                                                              name="ts_ReviewStickerHintergrundfarbe"
                                                                              id="ts_ReviewStickerHintergrundfarbe"
                                                                              size="50"
                                                                              value="{$ts_id_all->ts_ReviewStickerHintergrundfarbe}"/>
                        </div>
                        <div class="clear"></div>
                        <div class="ts_ReviewStickerCode isExpert">
                                    <textarea id="ts_ReviewStickerCode" class="form-control" rows="10" cols="120"
                                              name="ts_ReviewStickerCode"
                                              placeholder="Bitte Review Sticker-Code hier einfügen ...">{$ts_id_all->ts_ReviewStickerCode}</textarea>
                            <div id="ts_ReviewStickerCodeError" class="alert alert-danger">Fehler: Hinter der letzten
                                Variablen darf kein Komma stehen.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="clear"></div>
            <div class="vspacer20"></div>
            <!-- **** Review-Sticker **** -->
            <!-- **** Rich Snippets **** -->
            <div id="tsRichSnippets_wrapper" class="">
                <div class="panel-default2">
                    <div class="panel-heading2">
                        <h4 class="panel-title2"><span>Rich Snippets</span></h4>
                        <p>
                            <small>Rich Snippets sorgen dafür, dass bei Suchergebnissen in Google Ihre Sterne
                                angezeigt
                                werden.<br>
                                <a href="https://support.google.com/webmasters/answer/146750?hl=en" target="_blank">&nbsp;<i
                                            class="fa fa-external-link" aria-hidden="true"></i>&nbsp;Google
                                    Rich Snippets Dokumentation</a><br>
                                Aktivieren Sie die Snippets und wählen aus, auf welchen Seiten die Rich Snippets
                                angezeigt
                                werden sollen.
                            </small>
                        </p>
                    </div>
                </div>
                <div style="padding-left: 20px;">
                    <div class="input_tsConfig2">
                        <input type="hidden" name="ts_RichSnippetsAktiv" value="0">
                        <input type="checkbox" id="ts_RichSnippetsAktiv" name="ts_RichSnippetsAktiv" value="1"
                               {if $ts_id_all->ts_RichSnippetsAktiv==1}checked="checked"{/if}>
                    </div>
                    <label for="ts_RichSnippetsAktiv" class="tsConfig2">Rich Snippets aktivieren</label>
                    <div class="clear"></div>
                    <div class="ts_RichSnippetsAktiv_wrapper">
                        <div class="input_tsConfig2">
                            <input type="hidden" name="ts_RichSnippetsKategorieseite" value="0">
                            <input id="ts_RichSnippetsKategorieseite" type="checkbox"
                                   name="ts_RichSnippetsKategorieseite"
                                   value="1"
                                   {if $ts_id_all->ts_RichSnippetsKategorieseite==1}checked="checked"{/if}>
                        </div>
                        <label for="ts_RichSnippetsKategorieseite" class="tsConfig2">Kategorieseiten</label>
                        <div class="clear"></div>
                        <div class="input_tsConfig2">
                            <input type="hidden" name="ts_RichSnippetsArtikelseite" value="0">
                            <input id="ts_RichSnippetsArtikelseite" type="checkbox"
                                   name="ts_RichSnippetsArtikelseite"
                                   value="1"
                                   {if $ts_id_all->ts_RichSnippetsArtikelseite==1}checked="checked"{/if}>
                        </div>
                        <label for="ts_RichSnippetsArtikelseite" class="tsConfig2">Produktdetailseiten</label>
                        <div class="clear"></div>
                        <div class="input_tsConfig2">
                            <input type="hidden" name="ts_RichSnippetsStartseite" value="0">
                            <input id="ts_RichSnippetsStartseite" type="checkbox" name="ts_RichSnippetsStartseite"
                                   value="1"
                                   {if $ts_id_all->ts_RichSnippetsStartseite==1}checked="checked"{/if}>
                        </div>
                        <label for="ts_RichSnippetsStartseite" class="tsConfig2">Startseite (nicht
                            empfohlen)</label>
                        <div class="clear"></div>
                        <div class="ts_RichSnippetsCode isExpert">
                                    <textarea class="form-control" rows="10" cols="120" name="ts_RichSnippetsCode"
                                              placeholder="Bitte Rich Snippets-Code hier einfügen ...">{$ts_id_all->ts_RichSnippetsCode}</textarea>
                        </div>
                    </div>
                </div>
            </div>
            <!-- **** Rich Snippets **** -->
            <div class="clear"></div>
            <div class="vspacer20"></div>
            <div class="tright btn-group right">
                <a id="btn_ts_id_cancel" class="btn btn-danger" href="#" onclick="return false;"><i
                            class="fa fa-ban fa-fw"></i>&nbsp;Abbrechen</a>
                <a id="btn_ts_id_save" class="btn btn-primary" href="#" onclick="return false;"><i
                            class="fa fa-save fa-fw"></i>&nbsp;Speichern und weiter bearbeiten</a>
                <a id="btn_ts_id_save2" class="btn btn-default" href="#" onclick="return false;"><i
                            class="fa fa-save fa-fw"></i>&nbsp;Speichern und beenden</a>
            </div>
        {/foreach}
        </form>
    {else}
        <div class="box_info">
            <span>Bitte fügen Sie eine <b>neue Trusted Shops ID</b> ein oder wählen Sie aus der Liste der <b>installierten IDs</b> eine aus!</span>
        </div>
    {/if}
    <script type="text/javascript">
        {literal}
        $(document).ready(function () {
            /** Button Speichern / Abbrechen **/
            $('#btn_ts_id_cancel').click(function () {
                $('form[name=ts_id_options]').attr('action', '{/literal}{$ts_id_cancel_form_action}{literal}');
                $('form[name=ts_id_options]').append('<input type="hidden" name="ts_id_options_cancel" value="1">');
                $('form[name=ts_id_options]').submit();
            });
            $('#btn_ts_id_save').click(function () {
                var checkReviewCode = $('#ts_ReviewStickerCode').val();
                var checkReviewCodeStart = checkReviewCode.indexOf("reviewMinLength");
                var checkReviewCodeLength = checkReviewCode.indexOf("};") - checkReviewCode.indexOf("reviewMinLength");
                var checkReviewCodeTeilString = checkReviewCode.substr(checkReviewCodeStart, checkReviewCodeLength);
                if (checkReviewCodeTeilString.indexOf(",") == -1) {
                    $('#ts_ReviewStickerCodeError').hide();
                    $('#btn_ts_id_save').removeAttr("disabled");
                    $('form[name=ts_id_options]').attr('action', '{/literal}{$ts_id_save_form_action}{literal}');
                    $('form[name=ts_id_options]').append('<input type="hidden" name="ts_id_options_save" value="1">');
                    $('form[name=ts_id_options]').submit();
                } else {
                    $('#btn_ts_id_save').attr("disabled", 'disabled');
                    $('#ts_ReviewStickerCodeError').fadeIn({duration: "slow", easing: "linear"});
                }

            });
            $('#btn_ts_id_save2').click(function () {
                var checkReviewCode = $('#ts_ReviewStickerCode').val();
                var checkReviewCodeStart = checkReviewCode.indexOf("reviewMinLength");
                var checkReviewCodeLength = checkReviewCode.indexOf("};") - checkReviewCode.indexOf("reviewMinLength");
                var checkReviewCodeTeilString = checkReviewCode.substr(checkReviewCodeStart, checkReviewCodeLength);
                if (checkReviewCodeTeilString.indexOf(",") == -1) {
                    $('#ts_ReviewStickerCodeError').hide();
                    $('#btn_ts_id_save2').removeAttr("disabled");
                    $('form[name=ts_id_options]').attr('action', '{/literal}{$ts_id_save2_form_action}{literal}');
                    $('form[name=ts_id_options]').append('<input type="hidden" name="ts_id_options_save" value="1">');
                    $('form[name=ts_id_options]').submit();
                } else {
                    $('#btn_ts_id_save2').attr("disabled", 'disabled');
                    $('#ts_ReviewStickerCodeError').fadeIn({duration: "slow", easing: "linear"});
                }
            });

            /** Tooltip - Steuerung **/
            $("[rel=popover]").popover({'trigger': 'hover'});

            /** Toogle Standard- / Expert-Modus **/
            if ($('#sel_tsmodus').val() == 'E') {
                $('.isExpert').hide();
            }

            $('#sel_tsmodus').change(function () {
                if ($('#sel_tsmodus').val() == 'E') {
                    $('.isStandard').fadeOut({duration: "fast", easing: "linear"});
                    $('.isExpert').fadeIn({duration: "slow", easing: "linear"});
                } else {
                    $('.isExpert').fadeOut({duration: "fast", easing: "linear"});
                    $('.isStandard').fadeIn({duration: "slow", easing: "linear"});
                }
            });
            if ($('#sel_tsmodus').val() == 'E') {
                $('.isStandard').fadeOut({duration: "fast", easing: "linear"});
                $('.isExpert').fadeIn({duration: "slow", easing: "linear"});
            } else {
                $('.isExpert').fadeOut({duration: "fast", easing: "linear"});
                $('.isStandard').fadeIn({duration: "slow", easing: "linear"});
            }

            /** Toogle Bewertungs-/Kommentar-Sticker **/
            $('#ts_ReviewStickerPositionPruefen').hide();
            $('#ts_ReviewStickerModus').change(function () {
                if ($('#ts_ReviewStickerModus').val() == 'kommentar') {
                    $("#ts_ReviewStickerPosition option[value='3']").removeAttr("selected").attr("disabled", "disabled").text("Footer (nicht möglich)");
                    $("#ts_ReviewStickerPosition option[value='1']").attr("selected", "selected").prop("selected", true).text("Sidebar links (empfohlen)");
//                    $('#ts_ReviewStickerPositionPruefen').fadeIn({duration: "slow", easing: "linear"});
                    $('.isTestimonial').fadeIn({duration: "slow", easing: "linear"});
                } else {
                    $("#ts_ReviewStickerPosition option[value='1']").removeAttr("selected").text("Sidebar links");
                    $("#ts_ReviewStickerPosition option[value='3']").attr("selected", "selected").prop("selected", true).removeAttr("disabled").text("Footer (empfohlen)");
//                    $('#ts_ReviewStickerPositionPruefen').hide();
                    $('.isTestimonial').hide();
                }
            });
            if ($('#ts_ReviewStickerModus').val() == 'kommentar') {
                $("#ts_ReviewStickerPosition option[value='3']").prop('selected', false).prop('disabled', true).text("Footer (nicht möglich)");
                $("#ts_ReviewStickerPosition option[value='1']").text("Sidebar links (empfohlen)");
                $('.isTestimonial').fadeIn({duration: "slow", easing: "linear"});
            } else {
                $("#ts_ReviewStickerPosition option[value='3']").prop('disabled', false).text("Footer (empfohlen)");
                $("#ts_ReviewStickerPosition option[value='1']").text("Sidebar links");
                $('.isTestimonial').hide();
            }

            /** Produktbewertung **/
            $('.ts_ProduktBewertungAktiv_wrapper').hide();
            if ($("#ts_ProduktBewertungAktiv").is(':checked')) {
                $('.ts_ProduktBewertungAktiv_wrapper').fadeIn({duration: "slow", easing: "linear"});
            } else {
                $('.ts_ProduktBewertungAktiv_wrapper').hide();
            }
            $('#ts_ProduktBewertungAktiv').click(function () {
                if ($("#ts_ProduktBewertungAktiv").is(':checked')) {
                    $('.ts_ProduktBewertungAktiv_wrapper').fadeIn({duration: "slow", easing: "linear"});
                } else {
                    $("#ts_ProduktBewertungRegisterAktiv").prop("checked", false);
                    $("#ts_ProduktBewertungRegisterLeer").prop("checked", false);
                    $("#ts_ProduktBewertungSterneAktiv").prop("checked", false);
                    $("#ts_ProduktBewertungSterneLeer").prop("checked", false);
                    $('.ts_ProduktBewertungAktiv_wrapper').hide();
                }
            });

            /** Produktbewertungsregister **/
            $('.ts_ProduktBewertungRegisterAktiv_wrapper').hide();
            if ($("#ts_ProduktBewertungRegisterAktiv").is(':checked')) {
                $('.ts_ProduktBewertungRegisterAktiv_wrapper').fadeIn({duration: "slow", easing: "linear"});
            } else {
                $('.ts_ProduktBewertungRegisterAktiv_wrapper').hide();
            }
            $('#ts_ProduktBewertungRegisterAktiv').click(function () {
                if ($("#ts_ProduktBewertungRegisterAktiv").is(':checked')) {
                    $('.ts_ProduktBewertungRegisterAktiv_wrapper').fadeIn({duration: "slow", easing: "linear"});
                } else {
                    $("#ts_ProduktBewertungRegisterLeer").prop("checked", false);
                    $('.ts_ProduktBewertungRegisterAktiv_wrapper').hide();
                }
            });

            /** Produktbewertungssterne **/
            $('.ts_ProduktBewertungSterneAktiv_wrapper').hide();
            if ($("#ts_ProduktBewertungSterneAktiv").is(':checked')) {
                $('.ts_ProduktBewertungSterneAktiv_wrapper').fadeIn({duration: "slow", easing: "linear"});
            } else {
                $('.ts_ProduktBewertungSterneAktiv_wrapper').hide();
            }
            $('#ts_ProduktBewertungSterneAktiv').click(function () {
                if ($("#ts_ProduktBewertungSterneAktiv").is(':checked')) {
                    $('.ts_ProduktBewertungSterneAktiv_wrapper').fadeIn({duration: "slow", easing: "linear"});
                } else {
                    $("#ts_ProduktBewertungSterneLeer").prop("checked", false);
                    $('.ts_ProduktBewertungSterneAktiv_wrapper').hide();
                }
            });

            /** Review Sticker **/
            $('.ts_ReviewStickerAktiv_wrapper').hide();
            if ($("#ts_ReviewStickerAktiv").is(':checked')) {
                $('.ts_ReviewStickerAktiv_wrapper').fadeIn({duration: "slow", easing: "linear"});
            } else {
                $('.ts_ReviewStickerAktiv_wrapper').hide();
            }
            $('#ts_ReviewStickerAktiv').click(function () {
                if ($("#ts_ReviewStickerAktiv").is(':checked')) {
                    $('.ts_ReviewStickerAktiv_wrapper').fadeIn({duration: "slow", easing: "linear"});
                } else {
                    $('.ts_ReviewStickerAktiv_wrapper').hide();
                }
            });

            var checkReviewCode = $('#ts_ReviewStickerCode').val();
            var checkReviewCodeStart = checkReviewCode.indexOf("reviewMinLength");
            var checkReviewCodeLength = checkReviewCode.indexOf("};") - checkReviewCode.indexOf("reviewMinLength");
            var checkReviewCodeTeilString = checkReviewCode.substr(checkReviewCodeStart, checkReviewCodeLength);
            if (checkReviewCodeTeilString.indexOf(",") == -1) {
                $('#ts_ReviewStickerCodeError').hide();
                $('#btn_ts_id_save').removeAttr("disabled");
                $('#btn_ts_id_save2').removeAttr("disabled");
            } else {
                $('#btn_ts_id_save').attr("disabled", 'disabled');
                $('#btn_ts_id_save2').attr("disabled", 'disabled');
                $('#ts_ReviewStickerCodeError').fadeIn({duration: "slow", easing: "linear"});
            }

            $("#ts_ReviewStickerCode").on("change input paste keyup", function () {
                var checkReviewCode = $('#ts_ReviewStickerCode').val();
                var checkReviewCodeStart = checkReviewCode.indexOf("reviewMinLength");
                var checkReviewCodeLength = checkReviewCode.indexOf("};") - checkReviewCode.indexOf("reviewMinLength");
                var checkReviewCodeTeilString = checkReviewCode.substr(checkReviewCodeStart, checkReviewCodeLength);
                if (checkReviewCodeTeilString.indexOf(",") == -1) {
                    $('#ts_ReviewStickerCodeError').hide();
                    $('#btn_ts_id_save').removeAttr("disabled");
                    $('#btn_ts_id_save2').removeAttr("disabled");
                } else {
                    $('#btn_ts_id_save').attr("disabled", 'disabled');
                    $('#btn_ts_id_save2').attr("disabled", 'disabled');
                    $('#ts_ReviewStickerCodeError').fadeIn({duration: "slow", easing: "linear"});
                }
            });

            /** RichSnippets **/
            $('.ts_RichSnippetsAktiv_wrapper').hide();
            if ($("#ts_RichSnippetsAktiv").is(':checked')) {
                $('.ts_RichSnippetsAktiv_wrapper').fadeIn({duration: "slow", easing: "linear"});
            } else {
                $('.ts_RichSnippetsAktiv_wrapper').hide();
            }
            $('#ts_RichSnippetsAktiv').click(function () {
                if ($("#ts_RichSnippetsAktiv").is(':checked')) {
                    $('.ts_RichSnippetsAktiv_wrapper').fadeIn({duration: "slow", easing: "linear"});
                } else {
                    $("#ts_RichSnippetsKategorieseite").prop("checked", false);
                    $("#ts_RichSnippetsArtikelseite").prop("checked", false);
                    $("#ts_RichSnippetsStartseite").prop("checked", false);
                    $('.ts_RichSnippetsAktiv_wrapper').hide();
                }
            });
        });
        {/literal}
    </script>
</div>