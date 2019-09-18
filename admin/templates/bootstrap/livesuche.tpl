{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="livesuche"}
{include file='tpl_inc/seite_header.tpl' cTitel=#livesearch# cBeschreibung=#livesucheDesc# cDokuURL=#livesucheURL#}
<div id="content" class="container-fluid">
    <form name="sprache" method="post" action="livesuche.php">
        {$jtl_token}
        <input type="hidden" name="sprachwechsel" value="1" />
        <div class="block">
            <div class="input-group p25 left">
                <span class="input-group-addon">
                    <label for="{#changeLanguage#}">{#changeLanguage#}</label>
                </span>
                <span class="input-group-wrap last">
                    <select id="{#changeLanguage#}" name="kSprache" class="form-control selectBox" onchange="document.sprache.submit();">
                        {foreach name=sprachen from=$Sprachen item=sprache}
                            <option value="{$sprache->kSprache}" {if $sprache->kSprache==$smarty.session.kSprache}selected{/if}>{$sprache->cNameDeutsch}</option>
                        {/foreach}
                    </select>
                </span>
            </div>
        </div>
    </form>
    <ul class="nav nav-tabs" role="tablist">
        <li class="tab{if !isset($tab) || $tab === 'suchanfrage'} active{/if}">
            <a data-toggle="tab" role="tab" href="#suchanfrage">{#searchrequest#}</a>
        </li>
        <li class="tab{if isset($tab) && $tab === 'erfolglos'} active{/if}">
            <a data-toggle="tab" role="tab" href="#erfolglos">{#searchmiss#}</a>
        </li>
        <li class="tab{if isset($tab) && $tab === 'mapping'} active{/if}">
            <a data-toggle="tab" role="tab" href="#mapping">{#mapping#}</a>
        </li>
        <li class="tab{if isset($tab) && $tab === 'blacklist'} active{/if}">
            <a data-toggle="tab" role="tab" href="#blacklist">{#blacklist#}</a>
        </li>
        <li class="tab{if isset($tab) && $tab === 'einstellungen'} active{/if}">
            <a data-toggle="tab" role="tab" href="#einstellungen">{#livesucheSettings#}</a>
        </li>
    </ul>
    <div class="tab-content">
        <div id="suchanfrage" class="tab-pane fade {if !isset($tab) || $tab === 'suchanfrage'} active in{/if}">
            {if isset($Suchanfragen) && $Suchanfragen|@count > 0}
                {include file='tpl_inc/pagination.tpl' oPagination=$oPagiSuchanfragen cAnchor='suchanfrage'}
                <form name="suche" method="post" action="livesuche.php">
                    {$jtl_token}
                    <input type="hidden" name="Suche" value="1" />
                    <input type="hidden" name="tab" value="suchanfrage" />
                    {if isset($cSuche) && $cSuche|strlen > 0}
                        <input name="cSuche" type="hidden" value="{$cSuche}" />
                    {/if}

                    <div class="block">
                        <div class="input-group p25">
                            <span class="input-group-addon">
                                <label for="cSuche">{#livesucheSearchItem#}:</label>
                            </span>
                            <input class="form-control" id="cSuche" name="cSuche" type="text" value="{if isset($cSuche) && $cSuche|strlen > 0}{$cSuche}{/if}" />
                            <span class="input-group-btn">
                                <button name="submitSuche" type="submit" value="{#livesucheSearchBTN#}" class="btn btn-primary"><i class="fa fa-search"></i> {#livesucheSearchBTN#}</button>
                            </span>
                        </div>
                    </div>
                </form>
                <form name="login" method="post" action="livesuche.php">
                    {$jtl_token}
                    <input type="hidden" name="livesuche" value="1" />
                    <input type="hidden" name="cSuche" value="{if isset($cSuche)}{$cSuche}{/if}" />
                    <input type="hidden" name="nSort" value="{$nSort}" />
                    <input type="hidden" name="tab" value="suchanfrage" />
                    {if isset($cSuche) && $cSuche|strlen > 0}
                        {assign var=pAdditional value="cSuche="|cat:$cSuche}
                    {else}
                        {assign var=pAdditional value=''}
                    {/if}
                    {if isset($cSuche)}
                        {assign var=cSuchStr value="&Suche=1&cSuche="|cat:$cSuche|cat:"&"}
                    {else}
                        {assign var=cSuchStr value=""}
                    {/if}
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">{#searchrequest#}</h3>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <tr>
                                    <th class="th-1"></th>
                                    <th class="tleft">
                                        (<a href="livesuche.php?{$cSuchStr}nSort=1{if $nSort == 1}1{/if}&tab=suchanfrage">{if $nSort == 1}Z...A{else}A...Z{/if}</a>) {#search#}
                                    </th>
                                    <th class="tleft">
                                        (<a href="livesuche.php?{$cSuchStr}nSort=2{if $nSort == 2 || $nSort == -1}2{/if}&tab=suchanfrage">{if $nSort == 2 || $nSort == -1}1...9{else}9...1{/if}</a>) {#searchcount#}
                                    </th>
                                    <th class="th-4">
                                        (<a href="livesuche.php?{$cSuchStr}nSort=3{if $nSort == 3 || $nSort == -1}3{/if}&tab=suchanfrage">{if $nSort == 3 || $nSort == -1}0...1{else}1...0{/if}</a>) {#active#}
                                    </th>
                                    <th class="th-5">{#mapping#}</th>
                                </tr>

                                {foreach name=suchanfragen from=$Suchanfragen item=suchanfrage}
                                    <input name="kSuchanfrageAll[]" type="hidden" value="{$suchanfrage->kSuchanfrage}" />
                                    <tr class="tab_bg{$smarty.foreach.suchanfragen.iteration%2}">
                                        <td class="TD1">
                                            <input type="checkbox" name="kSuchanfrage[]" value="{$suchanfrage->kSuchanfrage}" />
                                        </td>
                                        <td class="TD2">{$suchanfrage->cSuche}</td>
                                        <td class="TD3">
                                            <input class="form-control fieldOther" name="nAnzahlGesuche_{$suchanfrage->kSuchanfrage}" type="text" value="{$suchanfrage->nAnzahlGesuche}" style="width:50px;" />
                                        </td>
                                        <td class="tcenter">
                                            <input type="checkbox" name="nAktiv[]" id="nAktiv_{$suchanfrage->kSuchanfrage}" value="{$suchanfrage->kSuchanfrage}" {if $suchanfrage->nAktiv==1}checked="checked"{/if} />
                                        </td>
                                        <td class="tcenter">
                                            <input class="form-control fieldOther" type="text" name="mapping_{$suchanfrage->kSuchanfrage}" />
                                        </td>
                                    </tr>
                                {/foreach}
                                <tr>
                                    <td class="TD1">
                                        <input name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessagesExcept(this.form, 'nAktiv_');" />
                                    </td>
                                    <td colspan="5" class="TD7"><label for="ALLMSGS">{#livesucheSelectAll#}</label></td>
                                </tr>
                            </table>
                        </div>
                        <div class="panel-footer">
                            <div class="btn-group p50">
                                <button name="suchanfragenUpdate" type="submit" value="{#update#}" class="btn btn-default reset"><i class="fa fa-refresh"></i> {#update#}</button>
                                <button name="delete" type="submit" value="{#delete#}" class="btn btn-danger"><i class="fa fa-trash"></i> {#deleteSelected#}</button>
                            </div>
                            <div class="input-group right p50">
                                <span class="input-group-addon">
                                    <label for="cMapping">{#livesucheMappingOn#}</label>
                                </span>
                                <input class="form-control" name="cMapping" type="text">
                                <span class="input-group-btn">
                                    <button name="submitMapping" type="submit" value="{#livesucheMappingOnBTN#}" class="btn btn-primary">{#livesucheMappingOnBTN#}</button>
                                </span>
                            </div>
                        </div>
                    </div>
                </form>
            {else}
                <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
            {/if}
        </div>
        <div id="erfolglos" class="tab-pane fade {if isset($tab) && $tab === 'erfolglos'} active in{/if}">
            {if $Suchanfragenerfolglos && $Suchanfragenerfolglos|@count > 0}
                {include file='tpl_inc/pagination.tpl' oPagination=$oPagiErfolglos cAnchor='erfolglos'}
                <form name="login" method="post" action="livesuche.php">
                    {$jtl_token}
                    <input type="hidden" name="livesuche" value="2">
                    <input type="hidden" name="tab" value="erfolglos">
                    <input type="hidden" name="nErfolglosEditieren" value="{if isset($nErfolglosEditieren)}{$nErfolglosEditieren}{/if}">
                    <div class="panel panel-default settings">
                        <div class="panel-heading">
                            <h3 class="panel-title">{#searchmiss#}</h3>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <tr>
                                    <th class="th-1" style="width: 40px;">&nbsp;</th>
                                    <th class="th-1" align="left">{#search#}</th>
                                    <th class="th-2" align="left">{#searchcount#}</th>
                                    <th class="th-3" align="left">{#lastsearch#}</th>
                                    <th class="th-4" align="left">{#mapping#}</th>
                                </tr>
                                {foreach name=suchanfragenerfolglos from=$Suchanfragenerfolglos item=Suchanfrageerfolglos}
                                    <tr class="tab_bg{$smarty.foreach.suchanfragenerfolglos.iteration%2}">
                                        <td class="TD1">
                                            <input name="kSuchanfrageErfolglos[]" type="checkbox" value="{$Suchanfrageerfolglos->kSuchanfrageErfolglos}" />
                                        </td>
                                        <td class="TD1">
                                            {if isset($nErfolglosEditieren) && $nErfolglosEditieren == 1}
                                                <input class="form-control" name="cSuche_{$Suchanfrageerfolglos->kSuchanfrageErfolglos}" type="text" value="{$Suchanfrageerfolglos->cSuche}" />
                                            {else}
                                                {$Suchanfrageerfolglos->cSuche}
                                            {/if}
                                        </td>
                                        <td class="TD2">{$Suchanfrageerfolglos->nAnzahlGesuche}</td>
                                        <td class="TD3">{$Suchanfrageerfolglos->dZuletztGesucht}</td>
                                        <td class="TD4">
                                            {if !isset($nErfolglosEditieren) || $nErfolglosEditieren != 1}
                                                <input class="form-control fieldOther" name="mapping_{$Suchanfrageerfolglos->kSuchanfrageErfolglos}" type="text" />
                                            {/if}
                                        </td>
                                    </tr>
                                {/foreach}
                                <tr>
                                    <td class="TD1">
                                        <input name="ALLMSGS" id="ALLMSGS2" type="checkbox" onclick="AllMessagesExcept(this.form, 'nAktiv_');" />
                                    </td>
                                    <td colspan="4" class="TD7"><label for="ALLMSGS2">{#livesucheSelectAll#}</label></td>
                                </tr>
                            </table>
                        </div>
                        <div class="panel-footer">
                            <div class="btn-group">
                                <button class="btn btn-primary" name="erfolglosUpdate" type="submit"><i class="fa fa-refresh"></i> {#update#}</button>
                                <button class="btn btn-default" name="erfolglosEdit" type="submit"><i class="fa fa-edit"></i> {#livesucheEdit#}</button>
                                <button class="btn btn-danger" name="erfolglosDelete" type="submit"><i class="fa fa-trash"></i> {#delete#}</button>
                            </div>
                        </div>
                    </div>
                </form>
            {else}
                <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
            {/if}
        </div>
        <div id="mapping" class="tab-pane fade {if isset($tab) && $tab === 'mapping'} active in{/if}">
            {if $Suchanfragenmapping && $Suchanfragenmapping|@count > 0}
                {include file='tpl_inc/pagination.tpl' oPagination=$oPagiMapping cAnchor='mapping'}
                <form name="login" method="post" action="livesuche.php">
                    {$jtl_token}
                    <input type="hidden" name="livesuche" value="4" />
                    <input type="hidden" name="tab" value="mapping" />
                    <div class="panel panel-default settings">
                        <div class="panel-heading">
                            <h3 class="panel-title">{#mapping#}</h3>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <tr>
                                    <th class="th-1"></th>
                                    <th class="th-2">{#search#}</th>
                                    <th class="th-3">{#searchnew#}</th>
                                    <th class="th-4">{#searchcount#}</th>
                                </tr>
                                {foreach name=suchanfragenmapping from=$Suchanfragenmapping item=sfm}
                                    <tr class="tab_bg{$smarty.foreach.suchanfragenmapping.iteration%2}">
                                        <td class="TD1">
                                            <input name="kSuchanfrageMapping[]" type="checkbox" value="{$sfm->kSuchanfrageMapping}">
                                        </td>
                                        <td class="TD2">{$sfm->cSuche}</td>
                                        <td class="TD3">{$sfm->cSucheNeu}</td>
                                        <td class="TD4">{$sfm->nAnzahlGesuche}</td>
                                    </tr>
                                {/foreach}
                            </table>
                        </div>
                        <div class="panel-footer">
                            <button name="delete" type="submit" class="btn btn-danger"><i class="fa fa-trash"></i> {#mappingDelete#}</button>
                        </div>
                    </div>
                </form>
            {else}
                <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
            {/if}
        </div>
        <div id="blacklist" class="tab-pane fade {if isset($tab) && $tab === 'blacklist'} active in{/if}">
            <form name="login" method="post" action="livesuche.php">
                {$jtl_token}
                <input type="hidden" name="livesuche" value="3" />
                <input type="hidden" name="tab" value="blacklist" />

                <div class="panel panel-default settings">
                    <div class="panel-heading">
                        <h3 class="panel-title">{#blacklist#}</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table">
                            <tr>
                                <th class="th-1">{#blacklistDescription#}</th>
                            </tr>
                            <tr class="tab-1_bg">
                                <td class="TD2">
                                    <textarea class="form-control" name="suchanfrageblacklist" style="width:100%;min-height:400px;">{foreach name=suchanfragenblacklist from=$Suchanfragenblacklist item=Suchanfrageblacklist}{$Suchanfrageblacklist->cSuche};{/foreach}</textarea>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="panel-footer">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-refresh"></i> {#update#}</button>
                    </div>
                </div>
            </form>
        </div>
        <div id="einstellungen" class="tab-pane fade {if isset($tab) && $tab === 'einstellungen'} active in{/if}">
            {include file='tpl_inc/config_section.tpl' config=$oConfig_arr name='einstellen' a='saveSettings' action='livesuche.php' buttonCaption=#save# title='Einstellungen' tab='einstellungen'}
        </div>
    </div>
</div>
{include file='tpl_inc/footer.tpl'}