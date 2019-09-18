 {include file='tpl_inc/seite_header.tpl' cTitel=#benutzer# cBeschreibung=#benutzerDesc# cDokuURL=#benutzerURL#}
<div id="content" class="container-fluid">
    <ul class="nav nav-tabs" role="tablist">
        <li class="tab{if empty($cTab) || $cTab === 'account_view'} active{/if}">
            <a data-toggle="tab" role="tab" href="#account_view">{#benutzerTab#}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'group_view'} active{/if}">
            <a data-toggle="tab" role="tab" href="#group_view">{#gruppenTab#}</a>
        </li>
    </ul>
    <div class="tab-content">
        <div id="account_view" class="tab-pane fade{if empty($cTab) || $cTab === 'account_view'} active in{/if}">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">{#benutzerKategorie#}</h3>
                </div>
                <div class="table-responsive">
                    <table class="list table">
                        <thead>
                        <tr>
                            <th class="tleft">#</th>
                            <th class="tcenter">{#benutzerLogin#}</th>
                            <th class="tcenter">{#benutzer2FA#}</th>
                            <th class="tcenter">{#benutzerMail#}</th>
                            <th class="tcenter">{#benutzerGruppe#}</th>
                            <th class="tcenter">{#benutzerLoginVersuche#}</th>
                            <th class="tcenter">{#benutzerLetzterLogin#}</th>
                            <th class="tcenter">{#benutzerGueltigBis#}</th>
                            <th class="tcenter" width="135">{#benutzerAktion#}</th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach from=$oAdminList_arr item="oAdmin" name="admin"}
                            <tr>
                                <td class="tleft">{$oAdmin->kAdminlogin}</td>
                                <td class="tcenter">{$oAdmin->cLogin}</td>
                                <td class="tcenter">{if $oAdmin->b2FAauth}{#stateON#}{else}{#stateOFF#}{/if}</td>
                                <td class="tcenter">{$oAdmin->cMail}</td>
                                <td class="tcenter">
                                    {if $oAdmin->kAdminlogingruppe > 1}
                                        <form method="post" action="benutzerverwaltung.php">
                                            {$jtl_token}
                                            <input type="hidden" name="id" value="{$oAdmin->kAdminlogingruppe}" />
                                            <button type="submit" class="btn btn-default" name="action" value="group_edit">{$oAdmin->cGruppe}</button>
                                        </form>
                                    {else}
                                        {$oAdmin->cGruppe}
                                    {/if}
                                </td>
                                <td class="tcenter">{$oAdmin->nLoginVersuch}</td>
                                <td class="tcenter">{if $oAdmin->dLetzterLogin && $oAdmin->dLetzterLogin != '0000-00-00 00:00:00'}{$oAdmin->dLetzterLogin|date_format:"%d.%m.%Y %H:%M:%S"}{else}---{/if}</td>
                                <td class="tcenter">{if !$oAdmin->bAktiv}gesperrt{else}{if $oAdmin->dGueltigBis && $oAdmin->dGueltigBis != '0000-00-00 00:00:00'}{$oAdmin->dGueltigBis|date_format:"%d.%m.%Y %H:%M:%S"}{else}---{/if}{/if}</td>
                                <td class="tcenter">
                                    <form method="post" action="benutzerverwaltung.php">
                                        {$jtl_token}
                                        <input type="hidden" name="id" value="{$oAdmin->kAdminlogin}" />
                                        <div class="btn-group">
                                            <button class="notext btn btn-default" name="action" value="account_edit" title="{#modify#}"><i class="fa fa-edit"></i></button>
                                            {if $oAdmin->bAktiv}
                                                <button class="notext btn btn-warning" name="action" value="account_lock" title="{#sperrenLabel#}"><i class="fa fa-lock"></i></button>
                                            {else}
                                                <button class="notext btn btn-warning" name="action" value="account_unlock" title="{#entsperrenLabel#}"><i class="fa fa-unlock"></i></button>
                                            {/if}
                                            <button class="notext btn btn-danger" name="action" value="account_delete" onclick="return confirm('Sind Sie sicher, dass der Benutzer entfernt werden soll?');" title="{#loeschenLabel#}"><i class="fa fa-trash"></i></button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                </div>
                <div class="panel-footer">
                    <form action="benutzerverwaltung.php" method="post">
                        {$jtl_token}
                        <input type="hidden" name="action" value="account_edit" />
                        <button type="submit" class="btn btn-primary"><i class="fa fa-share"></i> {#benutzerNeu#}</button>
                    </form>
                </div>
            </div>
        </div>
        <div id="group_view" class="tab-pane fade{if isset($cTab) && $cTab === 'group_view'} active in{/if}">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">{#gruppenKategorie#}</h3>
                </div>
                <div class="table-responsive">
                    <table class="list table">
                        <thead>
                        <tr>
                            <th class="tleft">#</th>
                            <th class="tleft">{#gruppenName#}</th>
                            <th class="tleft">{#gruppenDesc#}</th>
                            <th class="tcenter">{#gruppenBenutzer#}</th>
                            <th class="tcenter">{#benutzerAktion#}</th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach from=$oAdminGroup_arr item="oGroup"}
                            <tr>
                                <td class="tleft">{$oGroup->kAdminlogingruppe}</td>
                                <td class="tleft">{$oGroup->cGruppe}</td>
                                <td class="tleft">{$oGroup->cBeschreibung}</td>
                                <td class="tcenter">{$oGroup->nCount}</td>
                                <td class="tcenter">
                                    {if $oGroup->kAdminlogingruppe !== '1'}
                                        <form method="post" action="benutzerverwaltung.php">
                                            {$jtl_token}
                                            <input type="hidden" value="{$oGroup->kAdminlogingruppe}" name="id" />
                                            <div class="btn-group">
                                                <button type="submit" class="edit btn btn-default" name="action" value="group_edit" title="{#modify#}"><i class="fa fa-edit"></i></button>
                                                <button type="submit" class="delete btn btn-danger" name="action" value="group_delete" onclick="return confirm('Sind Sie sicher, dass die Gruppe entfernt werden soll?');" {if 0 < (int)$oGroup->nCount}title="{#loeschenLabelDeaktiviert#}" disabled="disabled"{else}title="{#loeschenLabel#}"{/if}><i class="fa fa-trash"></i></button>
                                            </div>
                                        </form>
                                    {/if}
                                </td>
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                </div>
                <div class="panel-footer">
                    <form action="benutzerverwaltung.php" method="post">
                        <input type="hidden" name="action" value="group_edit" />
                        {$jtl_token}
                        <button type="submit" class="btn btn-primary"><i class="fa fa-share"></i> {#gruppeNeu#}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>