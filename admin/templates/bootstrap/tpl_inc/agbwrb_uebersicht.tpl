{include file='tpl_inc/seite_header.tpl' cTitel=#agbwrb# cDokuURL=#agbwrbURL#}
<div id="content" class="container-fluid">
    <div class="block">
        <form name="sprache" method="post" action="agbwrb.php">
            {$jtl_token}
            <input type="hidden" name="sprachwechsel" value="1" />
            <div class="p25 input-group left">
                <span class="input-group-addon">
                    <label for="{#changeLanguage#}">{#changeLanguage#}:</strong></label>
                </span>
                <span class="input-group-wrap last">
                    <select id="{#changeLanguage#}" name="kSprache" class="form-control selectBox" onchange="document.sprache.submit();">
                        {foreach name=sprachen from=$Sprachen item=sprache}
                            <option value="{$sprache->kSprache}" {if $sprache->kSprache == $smarty.session.kSprache}selected{/if}>{$sprache->cNameDeutsch}</option>
                        {/foreach}
                    </select>
                </span>
            </div>
        </form>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">Vorhandene {#agbwrb#}</h3>
        </div>
        <table class="table">
            <thead>
            <tr>
                <th class="tleft">{#agbwrbCustomerGrp#}</th>
                <th>Aktion</th>
            </tr>
            </thead>
            <tbody>
            {foreach name=kundengruppe from=$oKundengruppe_arr item=oKundengruppe}
                {assign var=kKundengruppe value=$oKundengruppe->kKundengruppe}
                <tr class="tab_bg{$smarty.foreach.kundengruppe.iteration%2}">
                    <td class="">{$oKundengruppe->cName}</td>
                    <td class="tcenter">
                        <a href="agbwrb.php?agbwrb=1&agbwrb_edit=1&kKundengruppe={$oKundengruppe->kKundengruppe}&token={$smarty.session.jtl_token}"
                           class="btn btn-default" title="{#modify#}">
                            <i class="fa fa-edit"></i>
                        </a>
                    </td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    </div>
</div>