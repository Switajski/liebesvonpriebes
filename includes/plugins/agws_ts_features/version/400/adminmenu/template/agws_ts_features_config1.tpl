<link rel="stylesheet" type="text/css" href="{$URL_ADMINMENU}/template/css/agws_ts_features_main.css">

<div id="ts_features_wrapper" class="{$ts_css_class}">
    {if $ts_id_all_arr|@count == 0}
        <div>
            <div id="ts-image">
                <!-- <a title="Trusted Shops Registration Centre" href="https://www.trustedshops.com/integration/?backend_language={$smarty.const.TS_URL_BACKEND_LANGUAGE}&shopsw={$smarty.const.TS_URL_SHOPSW}&shopsw_version={$smarty.const.TS_URL_SHOPSW_VERSION}&plugin_version={$smarty.const.TS_URL_PLUGIN_VERSION}&context=membership&" target="_blank"> -->
                    <img src="{$URL_ADMINMENU}/template/image/{$smarty.const.TS_GRAFIK_FILENAME}">
                <!-- </a> -->
            </div>
            <div id="ts-intro">
                <b>Ihr Vorteil mit Trusted Shops</b><br><br>
                Mit dem <b>Trustbadge</b> von Europas Vertrauensmarke Nr.1 zeigen Sie Ihren Kunden, dass sie bei Ihnen sicher einkaufen – mit Gütesiegel, echten Kundenbewertungen und Geld-zurück-Garantie.<br><br>

                &bull; <b>Gütesiegel: mehr Vertrauen, mehr Umsatz</b><br>
                Mehr als 60% der Online-Käufer vertrauen Gütesiegeln. Nutzen auch Sie dieses Vertrauen für eine höhere Konversionsrate.<br><br>
                &bull; <b>Kundenbewertung: beste Empfehlungen zahlen sich aus</b><br>
                Setzen Sie auf die überzeugende Komplett-Lösung für professionelles Empfehlungsmarketing in Ihrem Shop.<br><br>
                &bull; <b>Geld-zurück-Garantie: Käuferschutz für weniger Kaufabbrüche</b><br>
                Je geringer das finanzielle Risiko für den Verbraucher ist, desto eher rollt der Einkaufswagen durch die Kasse.<br><br>
                <b>Unser Angebot: günstiger für Sie als JTL-Kunde!</b><br>
                Durch die Vorzertifizierung der Shop-Software profitieren Sie von <a href="http://www.trustedshops.de/shopbetreiber/index.html?registeredOffice=DEU&shopsw=JTL#offer&utm_source=JTL&utm_medium=backend&utm_content=link1&utm_campaign=part&a_aid=5591155a59851" target="_blank">besonders günstigen Konditionen</a>.
            </div>
        </div>
        <div class="clear vspacer30"></div>
    {/if}

    {if $ts_message !=""}
        <div class="{$ts_message_class}">
            <span>{$ts_message}</span>
        </div>
    {/if}
    <div id="ts-id-add">
        <div class="panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Trusted Shops IDs verwalten</h3>
            </div>
        </div>
        <div class="vspacer20"></div>
        <form id="ts_id_add" name="ts_id_add" action="{$ts_id_add_form_action}" method="post">
            <div class="label_tsid">Neue Trusted Shops ID:</div>
            <div class="input_tsid">
                <input type="text" name="ts_id" id="ts_id" size="40" value="" placeholder="Trusted Shops ID einfügen…"/>
            </div>
            <div class="clear"></div>
            <div class="label_tssprache">Shop-Sprache:</div>
            <div class="select_tssprache">
                <select name="ts_sprache">
                    <option value="0" disabled {if $ts_id_all->iTS_Sprache==0}selected="selected"{/if}>Bitte auswählen</option>
                    {foreach from=$ts_id_shopsprachen_free item=ts_id_sprache_free}
                        <option value="{$ts_id_sprache_free->kSprache}">{$ts_id_sprache_free->cNameDeutsch}</option>
                    {/foreach}
                </select>
            </div>
            <input type="hidden" name="ts_id_is_add" value="1">
            <input type="hidden" name="kPlugin" value="{$oPlugin->kPlugin}">
            <div class="btn_tsadd">
                <a class="btn btn-default" href="javascript:;" onclick="document.getElementById('ts_id_add').submit();"><i class="fa fa-plus fa-fw"></i>&nbsp;Hinzuf&uuml;gen</a>
            </div>
        </form>
    </div>
    <div class="vspacer30"></div>
    <div id="ts-id-all">
        <div class="panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Installierte  Trusted Shops IDs</h3>
            </div>
        </div>
        <div class="vspacer20"></div>
        <div class="table-responsive">
            <table class="list table">
                <thead>
                    <th>ID</th>
                    <th>Sprache</th>
                    <th colspan="2">Aktion</th>
                </thead>
                <tbody>
                    {if $ts_id_all_arr|@count != 0}
                        {foreach from=$ts_id_all_arr item=ts_id_all}
                            {assign var="ts_id_config_error" value="0"}
                            {if ($ts_id_all->iTS_Sprache=="0" || $ts_id_all->cNameDeutsch=="" || $ts_id_all->cTS_BadgeCode=="")}{assign var="ts_id_config_error" value="1"}{/if}
                            <tr>
                                <td>{$ts_id_all->cTS_ID}</td>
                                <td>{if $ts_id_config_error=='1'}<span class="error">Erweiterte Konfiguration prüfen!</span>{else}<span>{$ts_id_all->cNameDeutsch}</span>{/if}</td>
                                <td>
                                    <form id="ts_id_edit_{$ts_id_all->cTS_ID}" name="ts_id_edit" action="{$ts_id_edit_form_action}" method="post">
                                        <input type="hidden" name="kPlugin" value="{$oPlugin->kPlugin}">
                                        <input type="hidden" name="ts_id" value="{$ts_id_all->cTS_ID}" />
                                        <input type="hidden" name="ts_id_is_edit" value="1" />
                                        <!-- <input name="ts_id_edit" type="submit" class="button edit" value="Ändern"/> -->
                                        <a title="ID konfigurieren" class="btn btn-default btn-sm" href="javascript:;" onclick="document.getElementById('ts_id_edit_{$ts_id_all->cTS_ID}').submit();"><i class="fa fa-edit fa-fw"></i></a>
                                    </form>
                                </td>
                                <td>
                                    <form id="ts_id_delete_{$ts_id_all->cTS_ID}" name="ts_id_delete" action="{$ts_id_delete_form_action}" method="post">
                                        <input type="hidden" name="kPlugin" value="{$oPlugin->kPlugin}">
                                        <input type="hidden" name="ts_id" value="{$ts_id_all->cTS_ID}" />
                                        <input type="hidden" name="ts_id_is_delete" value="1" />
                                        <!-- <input name="ts_id_delete" type="submit" class="button delete" value="Löschen"/> -->
                                        <a title="ID l&ouml;schen" class="btn btn-default btn-sm" href="javascript:;" onclick="document.getElementById('ts_id_delete_{$ts_id_all->cTS_ID}').submit();"><i class="fa fa-trash-o fa-fw"></i></a>
                                    </form>
                                </td>
                            </tr>
                        {/foreach}
                    {else}
                        <tr>
                            <td colspan="4"><span class="box_info">Es wurden noch keine Trusted Shops IDs installiert</span></td>
                        </tr>
                    {/if}
                </tbody>
            </table>
        </div>
    </div>
</div>