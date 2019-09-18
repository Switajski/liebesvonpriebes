<link rel="stylesheet" type="text/css" href="{$URL_ADMINMENU}/template/css/agws_ts_features_admin.css">

<div id="ts_features_wrapper" class="{$ts_css_class}">
    {if $ts_id_all_arr|@count == 0}
        <div id="ts-image">
            <img src="{$URL_ADMINMENU}/template/image/{$smarty.const.TS_GRAFIK_FILENAME}">
        </div>
        <div id="ts-registration-button">
            <div class="btn_tsadd">
                <a class="btn btn-primary" href="https://business.trustedshops.de/shopsoftware/jtl?&a_aid=JTL"
                   target="_blank"><i class="fa fa-external-link fa-fw"></i>&nbsp;Melden Sie sich hier an</a>
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
                <h3 class="panel-title">Trusted Shops IDs installieren</h3>
            </div>
        </div>
        <div class="vspacer20"></div>
        <form id="ts_id_add" name="ts_id_add" action="{$ts_id_add_form_action}" method="post">
            <label class="tsConfig">Neue Trusted Shops ID:</label>
            <div class="input_tsConfig">
                <input class="form-control" type="text" name="ts_id" id="ts_id" size="40" value=""
                       placeholder="Neue Trusted Shops ID einfügen…"/>
            </div>
            <div class="clear"></div>
            <label class="tsConfig">Shop-Sprache:</label>
            <div class="select_tsConfig">
                <select class="form-control" name="ts_sprache">
                    <option value="0" disabled
                            {if isset($ts_id_all) && $ts_id_all->ts_sprache==0}selected="selected"{/if}>Bitte auswählen
                    </option>
                    {foreach from=$ts_id_shopsprachen_free item=ts_id_sprache_free}
                        <option value="{$ts_id_sprache_free->kSprache}">{$ts_id_sprache_free->cNameDeutsch}</option>
                    {/foreach}
                </select>
            </div>
            <div class="clear"></div>
            <input type="hidden" name="ts_id_is_add" value="1">
            <input type="hidden" name="kPlugin" value="{$oPlugin->kPlugin}">
            <div class="btn_tsadd">
                <a class="btn btn-primary" href="javascript:;" onclick="document.getElementById('ts_id_add').submit();"><i
                            class="fa fa-save fa-fw"></i>&nbsp;Hinzuf&uuml;gen</a>
            </div>
        </form>
    </div>
    <div class="vspacer40"></div>
    {if $ts_id_all_arr|@count != 0}
        <div id="ts-id-all">
            <div class="panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Trusted Shops IDs verwalten</h3>
                </div>
            </div>
            <div class="vspacer20"></div>
            <div class="table-responsive">
                <table class="list table">
                    <thead>
                    <th>ID</th>
                    <th>Sprache</th>
                    <!-- th colspan="2">Aktion</th -->
                    <th>ID konfigurieren</th>
                    <th>ID l&ouml;schen</th>
                    </thead>
                    <tbody>
                    {if $ts_id_all_arr|@count != 0}
                        {foreach from=$ts_id_all_arr item=ts_id_all}
                            {assign var="ts_id_config_error" value="0"}
                            {if ($ts_id_all->ts_sprache=="0" || $ts_id_all->cNameDeutsch=="" || $ts_id_all->ts_BadgeCode=="")}{assign var="ts_id_config_error" value="1"}{/if}
                            <tr>
                                <td>{$ts_id_all->ts_id}</td>
                                <td>{if $ts_id_config_error=='1'}
                                        <span class="error">Erweiterte Konfiguration prüfen!</span>
                                    {else}<span>{$ts_id_all->cNameDeutsch}</span>{/if}</td>
                                <td>
                                    <form id="ts_id_edit_{$ts_id_all->ts_id}" name="ts_id_edit"
                                          action="{$ts_id_edit_form_action}" method="post">
                                        <input type="hidden" name="kPlugin" value="{$oPlugin->kPlugin}">
                                        <input type="hidden" name="ts_id" value="{$ts_id_all->ts_id}"/>
                                        <input type="hidden" name="ts_id_is_edit" value="1"/>
                                        <!-- <input name="ts_id_edit" type="submit" class="button edit" value="Ändern"/> -->
                                        <a title="ID konfigurieren" class="btn btn-default btn-sm" href="javascript:;"
                                           onclick="document.getElementById('ts_id_edit_{$ts_id_all->ts_id}').submit();"><i
                                                    class="fa fa-edit fa-fw"></i></a>
                                    </form>
                                </td>
                                <td>
                                    <form id="ts_id_delete_{$ts_id_all->ts_id}" name="ts_id_delete"
                                          action="{$ts_id_delete_form_action}" method="post">
                                        <input type="hidden" name="kPlugin" value="{$oPlugin->kPlugin}">
                                        <input type="hidden" name="ts_id" value="{$ts_id_all->ts_id}"/>
                                        <input type="hidden" name="ts_id_is_delete" value="1"/>
                                        <!-- <input name="ts_id_delete" type="submit" class="button delete" value="Löschen"/> -->
                                        <a title="ID l&ouml;schen" class="btn btn-danger btn-sm" href="javascript:;"
                                           onclick="document.getElementById('ts_id_delete_{$ts_id_all->ts_id}').submit();"><i
                                                    class="fa fa-trash-o fa-fw"></i></a>
                                    </form>
                                </td>
                            </tr>
                        {/foreach}
                    {else}
                        <tr>
                            <td colspan="4"><span
                                        class="box_info">Es wurden noch keine Trusted Shops IDs installiert</span></td>
                        </tr>
                    {/if}
                    </tbody>
                </table>
            </div>
        </div>
    {/if}
</div>