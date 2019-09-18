<div class="settings">
    <form action="" method="post">
        <table class="table">
            <tr>
                <th class="hb_sp hb_sp_1">Rechtstext</th>
                <th class="hb_sp hb_sp_2">&Uuml;bergabe vom H&auml;ndlerbund</th>
                <th class="hb_sp hb_sp_3">Format</th>
                <th class="hb_sp hb_sp_4">Aktiv</th>
                <th class="hb_sp hb_sp_5">Fehlversuche</th>
                <th class="hb_sp hb_sp_6">Aktualisiert</th>
                <th class="hb_sp hb_sp_7">Zur&uuml;cksetzen</th>
            </tr>
            {foreach from=$oTexte_arr item="oText"}
            <tr class="hb_line">
                {assign var="cText" value=$oText->cType}
                <td class="hb_sp hb_sp_1">{$cRechtstext_arr.$cText}</td>
                <td class="hb_sp hb_sp_2"><a href="{$oText->cURL}" target="_blank"><i class="fa fa-external-link"></i> hier</a></td>
                <td class="hb_sp hb_sp_3">{$oText->cFormat}</td>
                <td class="hb_sp hb_sp_4">{if $oText->nAktiv != 1}Nein{else}Ja{/if}</td>
                <td class="hb_sp hb_sp_5">{$oText->nVersuch}</td>
                <td class="hb_sp hb_sp_6">{if $oText->dAktualisiert === '0000-00-00 00:00:00'}-{else}{$oText->dAktualisiert|date_format:'%d.%m.%Y %H:%M:%S'}{/if}</td>
                <td class="hb_sp hb_sp_7"><a href="{$oText->cActivateUrl}" class="button btn btn-danger btn-sm">zur&uuml;cksetzen</a></td>
            </tr>
            {/foreach}
        </table>
        <div class="hb_line">
            <a href="{$cUrlActivateAll}" class="btn btn-danger button orange">alle zur&uuml;cksetzen</a>
        </div>
    </form>
</div>
