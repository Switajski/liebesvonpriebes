<script type="text/javascript">
{literal}
$(document).ready(function() {
    $('#tmp_check').bind('click', function() {
        if ($(this).is(':checked')) {
            $('#tmp_date').show();
        } else {
            $('#tmp_date').hide();
        }
    });
    $('#dGueltigBis').datetimepicker({
        showSecond: true,
        timeFormat: 'hh:mm:ss',
        dateFormat: 'dd.mm.yy'
    });

    /** bring the 2FA-canvas in a defined position depending on the state of the 2FA */
    if ('nein' == $('#b2FAauth option:selected').text().toLowerCase()) {
        $('[id$=TwoFAwrapper]').hide();
    } else {
        $('[id$=TwoFAwrapper]').show();
    }

    /** install a "toggle-event-handler" to fold or unfold the 2FA-canvas, via the "Ja/Nein"-select */
    $('[id$=b2FAauth]').on('change', function(e) {
        e.stopImmediatePropagation(); // stop this event during page-load
        if('none' == $('[id$=TwoFAwrapper]').css('display')) {
            $('[id$=TwoFAwrapper]').slideDown();
        } else {
            $('[id$=TwoFAwrapper]').slideUp();
        }
    });
});
{/literal}
</script>

{literal}
<style>
    /* CONSIDER: styles ar mandatory for the QR-code! */

    /* a small space arround the whole code (not mandatory) */
    div.qrcode{
        /* margin: 0 5px; */
        margin: 5px
    }

    /* row element */
    div.qrcode > p {
        margin: 0;
        padding: 0;
        height: 5px;
    }

    /* column element(s) */
    div.qrcode > p > b,
    div.qrcode > p > i {
        display: inline-block;
        width: 5px;
        height: 5px;
    }

    /* color of 'on-elements' - "the color of the QR" */
    div.qrcode > p > b {
        background-color: #000;
    }

    /* color of 'off-elements' - "the color of the background" */
    div.qrcode > p > i {
        background-color: #fff;
    }
</style>
{/literal}

{assign var="cTitel" value=#benutzerNeu#}
{if isset($oAccount->kAdminlogin) && $oAccount->kAdminlogin > 0}
    {assign var="cTitel" value=#benutzerBearbeiten#}
{/if}

{include file='tpl_inc/seite_header.tpl' cTitel=$cTitel cBeschreibung=#benutzerDesc#}
<div id="content" class="container-fluid">
    <form class="navbar-form" action="benutzerverwaltung.php" method="post">
        {$jtl_token}
        <div id="settings" class="settings">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Allgemein</h3>
                </div>
                <div class="panel-body">
                    <div class="item">
                        <div class="input-group{if isset($cError_arr.cName)} error{/if}">
                            <span class="input-group-addon">
                                <label for="cName">Vor- und Nachname</label>
                            </span>
                            <span class="input-group-wrap">
                                <input id="cName" class="form-control" type="text" name="cName" value="{if isset($oAccount->cName)}{$oAccount->cName}{/if}" />
                            </span>
                            {if isset($cError_arr.cName)}<span class="input-group-addon error" title="Bitte ausf&uuml;llen"><i class="fa fa-exclamation-triangle"></i></span>{/if}
                        </div>
                    </div>
                    <div class="item">
                        <div class="input-group{if isset($cError_arr.cMail)} error{/if}">
                            <span class="input-group-addon">
                                <label for="cMail">E-Mail Adresse</label>
                            </span>
                            <span class="input-group-wrap">
                                <input id="cMail" class="form-control" type="text" name="cMail" value="{if isset($oAccount->cMail)}{$oAccount->cMail}{/if}" />
                            </span>
                            {if isset($cError_arr.cMail)}<span class="input-group-addon error" title="Bitte ausf&uuml;llen"><i class="fa fa-exclamation-triangle"></i></span>{/if}
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Anmeldedaten</h3>
                </div>
                <div class="panel-body">
                    <div class="item">
                        <div class="input-group{if isset($cError_arr.cLogin)} error{/if}">
                            <span class="input-group-addon">
                                <label for="cLogin">Benutzername</label>
                            </span>
                            <span class="input-group-wrap">
                                <input id="cLogin" class="form-control" type="text" name="cLogin" value="{if isset($oAccount->cLogin)}{$oAccount->cLogin}{/if}">
                            </span>
                            {if isset($cError_arr.cLogin) && $cError_arr.cLogin == 1}
                                <span class="input-group-addon error" title="Bitte ausf&uuml;llen"><i class="fa fa-exclamation-triangle"></i></span>
                            {elseif isset($cError_arr.cLogin) && $cError_arr.cLogin == 2}
                                <span class="input-group-addon error">Benutzername <strong>'{$oAccount->cLogin}'</strong> bereits vergeben</span>
                                <span class="input-group-addon error" title="Benutzername bereits vergeben"><i class="fa fa-exclamation-triangle"></i></span>
                            {/if}
                        </div>
                    </div>

                    <div class="item">
                        <div class="input-group{if isset($cError_arr.cPass)} error{/if}">
                            <span class="input-group-addon">
                                <label for="cPass">Passwort</label>
                            </span>
                            <span class="input-group-wrap">
                                <input id="cPass" class="form-control" type="text" name="cPass" autocomplete="off" />
                            </span>
                            <span class="input-group-addon">
                                <button type="button" onclick="ioCall('getRandomPassword');return false;"
                                        class="btn btn-info btn-xs" title="Passwort generieren">
                                    <i class="fa fa-random"></i>
                                </button>
                            </span>
                            {if isset($cError_arr.cPass)}<span class="input-group-addon error" title="Bitte ausf&uuml;llen"><i class="fa fa-exclamation-triangle"></i></span>{else}<span class="input-group-addon"><i class="fa fa-wrench"></i></span>{/if}
                        </div>
                    </div>

                    {if isset($oAccount->kAdminlogingruppe) && $oAccount->kAdminlogingruppe > 1}
                        <div class="item">
                            <div class="input-group">
                            <span class="input-group-addon">
                                <label for="tmp_check">Zeitlich begrenzter Zugriff</label>
                            </span>
                            <span class="input-group-wrap">
                                <span class="input-group-checkbox-wrap">
                                    <input class="" type="checkbox" id="tmp_check" name="dGueltigBisAktiv" value="1"{if (isset($oAccount->dGueltigBis) && $oAccount->dGueltigBis !== '0000-00-00 00:00:00')} checked="checked"{/if} />
                                </span>
                            </span>
                            </div>
                        </div>

                        <div class="item{if !empty($cError_arr.dGueltigBis)} error{/if}"{if !$oAccount->dGueltigBis || $oAccount->dGueltigBis == '0000-00-00 00:00:00'} style="display: none;"{/if} id="tmp_date">
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <label for="dGueltigBis">... bis einschlie&szlig;lich</label>
                                </span>
                                <input class="form-control" type="text" name="dGueltigBis" value="{if $oAccount->dGueltigBis}{$oAccount->dGueltigBis|date_format:"%d.%m.%Y %H:%M:%S"}{/if}" id="dGueltigBis" />
                                {if !empty($cError_arr.dGueltigBis)}<span class="input-group-addon error" title="Bitte ausf&uuml;llen"><i class="fa fa-exclamation-triangle"></i></span>{/if}
                            </div>
                        </div>
                    {/if}
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">2-Faktor-Authentifizierung</h3>
                </div>
                <div class="panel-body">
                    <div class="item">
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="b2FAauth">Aktivieren</label>
                            </span>
                            <span class="input-group-wrap">
                                <select id="b2FAauth" class="form-control" name="b2FAauth">
                                    <option value="0"{if !isset($oAccount->b2FAauth) || (isset($oAccount->b2FAauth) && (bool)$oAccount->b2FAauth === false)} selected="selected"{/if}>Nein</option>
                                    <option value="1"{if isset($oAccount->b2FAauth) && (bool)$oAccount->b2FAauth === true} selected="selected"{/if}>Ja</option>
                                </select>
                            </span>
                        </div>

                        {literal}
                        <script>
                            function createNewSecret() {
                                if('' == $('[id$=cLogin]').val()) {
                                    alert('Bitte legen Sie zuerst, in den Anmeldedaten, einen Benutzernamen fest!');
                                    return(false);
                                }

                                if(confirm("Das bisherige 'Authentication Secret' wird ersetzt!\nWirklich fortfahren?")) {
                                    var userName = $('#cLogin').val();
                                    $('#QRcode').html('<img src="templates/bootstrap/gfx/widgets/ajax-loader.gif">');
                                    ioCall('getNewTwoFA', [userName], function (data) {
                                        // display the new RQ-code
                                        $('#QRcode').html(data.szQRcode);
                                        $('#c2FAsecret').val(data.szSecret);

                                        // toggle code-canvas
                                        if('none' == $('#QRcodeCanvas').css('display')) {
                                            $('#QRcodeCanvas').css('display', 'block');
                                        }
                                    });
                                }
                            }

                            function showEmergencyCodes(action) {
                                var userName = $('#cLogin').val();
                                ioCall('genTwoFAEmergencyCodes', [userName], function (data) {
                                    var iframeHtml = '';

                                    iframeHtml += '<h4>JTL-shop Backend Notfall-Codes</h4>';
                                    iframeHtml += 'Account: <b>' + data.loginName + '</b><br>';
                                    iframeHtml += 'Shop: <b>' + data.shopName + '</b><br><br>';
                                    iframeHtml += '<pre>';

                                    data.vCodes.forEach(function (code, i) {
                                        iframeHtml += code + ' ';
                                        if (i%2 === 1) {
                                            iframeHtml += '\n';
                                        }
                                    });

                                    iframeHtml += '</pre>';

                                    $('#printframe').contents().find('body')[0].innerHTML = iframeHtml;
                                    $('#EmergencyCodeModal').modal('show');
                                });
                            }

                        </script>
                        {/literal}
                        <div id="TwoFAwrapper" {if isset($cError_arr.c2FAsecret)}class="error"{/if} style="border:1px solid {if isset($cError_arr.c2FAsecret)}red{else}lightgrey{/if};padding:10px;">
                            <div id="QRcodeCanvas" style="display:{if '' !== $QRcodeString }block{else}none{/if}">
                                <div class="alert alert-danger" role="alert">
                                    <strong>Achtung:</strong> Bitte beachten Sie, dass Sie mit diesem Account keine M&ouml;glichkeit mehr haben, in das Shop-Backend zu gelangen,<br>
                                    falls Sie keinen Zugriff mehr auf die Google-Authenticator-App auf Ihrem Mobilger&auml;t haben sollten!<br>
                                </div>
                                Scannen Sie den hier abgebildeten QR-Code mit der "Google-Authenticator"-app auf Ihrem Handy.<br>
                                <div id="QRcode" class="qrcode">{$QRcodeString}</div><br>
                                <input type="hidden" id="c2FAsecret" name="c2FAsecret" value="{$cKnownSecret}">
                                <br>
                            </div>
                            {* Emergency-Code-Modal  BEGIN *}
                            <div class="modal fade" id="EmergencyCodeModal">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                            <h4 class="modal-title">Notfall-Codes</h4>
                                        </div>
                                        <div class="modal-body">
                                            <div id="EmergencyCodes">
                                                <div class="iframewrapper">
                                                    <iframe src="" id="printframe" name="printframe" frameborder="0" width="100%" height="300" align="middle"></iframe>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <div class="btn-group">
                                                <button class="btn btn-default" type="button" data-dismiss="modal">Schlie&szlig;en</button>
                                                <button class="btn btn-primary" type="button" onclick="printframe.print();">Drucken</button>
                                                <button class="btn btn-danger" type="button" onclick="showEmergencyCodes('forceReload');">Codes neu erzeugen</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {* Emergency-Code-Modal  END *}
                            Um einen neuen QR-Code zu erzeugen, klicken Sie bitte hier:<br>
                            <br>
                            <div class="btn-group">
                                <button class="btn btn-primary" type="button" onclick="createNewSecret();">Neuen Code erstellen</button>
                                <button class="btn btn-warning" type="button" onclick="showEmergencyCodes();">Notfall-Codes neu erstellen</button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            {if !isset($oAccount->kAdminlogingruppe) || (isset($nAdminCount) && !($oAccount->kAdminlogingruppe == 1 && $nAdminCount <= 1))}
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Berechtigungen</h3>
                    </div>
                    <div class="panel-body">
                        <div class="item">
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <label for="kAdminlogingruppe">Benutzergruppe</label>
                                </span>
                                <span class="input-group-wrap">
                                    <select id="kAdminlogingruppe" class="form-control" name="kAdminlogingruppe">
                                        {foreach from=$oAdminGroup_arr item="oGroup"}
                                            <option value="{$oGroup->kAdminlogingruppe}" {if isset($oAccount->kAdminlogingruppe) && $oAccount->kAdminlogingruppe == $oGroup->kAdminlogingruppe}selected="selected"{/if}>{$oGroup->cGruppe} ({$oGroup->nCount})</option>
                                        {/foreach}
                                    </select>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            {else}
                <input type="hidden" name="kAdminlogingruppe" value="1" />
            {/if}

            {if !empty($extContent)}
                {$extContent}
            {/if}
        </div>
        <div class="panel-footer">
            <div class="btn-group">
                <input type="hidden" name="action" value="account_edit" />
                {if isset($oAccount->kAdminlogin) && $oAccount->kAdminlogin > 0}
                    <input type="hidden" name="kAdminlogin" value="{$oAccount->kAdminlogin}" />
                {/if}
                <input type="hidden" name="save" value="1" />
                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {#save#}</button>
                <a class="btn btn-danger" href="benutzerverwaltung.php"><i class="fa fa-exclamation"></i> Abbrechen</a>
            </div>
        </div>
    </form>
</div>
