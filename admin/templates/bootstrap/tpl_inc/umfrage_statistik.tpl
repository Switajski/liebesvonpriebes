<div id="page">
    <div id="content" class="container-fluid">
        <h2 class="txtBlack">{$oUmfrageStats->cName}</h2>
        <div class="row">
            <div class="col-md-3">
                <strong>{#umfrageValidation#}:</strong><br/>
                {$oUmfrageStats->dGueltigVon_de}<br/>
                -{if $oUmfrageStats->dGueltigBis|truncate:10:"" === '0000-00-00'}{#umfrageInfinite#}{else}{$oUmfrageStats->dGueltigBis_de}{/if}
            </div>
            <div class="col-md-3">
                <strong>{#umfrageCustomerGrp#}:</strong><br/>
                {foreach name=kundengruppen from=$oUmfrageStats->cKundengruppe_arr item=cKundengruppe}
                    {$cKundengruppe}{if !$smarty.foreach.kundengruppen.last},{/if}
                {/foreach}
            </div>
            <div class="col-md-3">
                <strong>{#umfrageActive#}:</strong><br/>
                {$oUmfrageStats->nAktiv}
            </div>
            <div class="col-md-3">
                <strong>{#umfrageTryCount#}:</strong><br/>
                {$oUmfrageStats->nAnzahlDurchfuehrung}
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <strong>{#umfrageText#}:</strong><br/>
                {$oUmfrageStats->cBeschreibung}
            </div>
        </div>

        {if $oUmfrageStats->oUmfrageFrage_arr|@count > 0 && $oUmfrageStats->oUmfrageFrage_arr}
            <div>
                <h3>{#umfrageQ#}:</h3>
                {foreach name=umfragefrage from=$oUmfrageStats->oUmfrageFrage_arr item=oUmfrageFrage}

                    {if $oUmfrageFrage->oUmfrageFrageAntwort_arr|@count > 0 && $oUmfrageFrage->oUmfrageFrageAntwort_arr}
                        {if $oUmfrageFrage->cTyp === 'matrix_single' || $oUmfrageFrage->cTyp === 'matrix_multi'}
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <strong>{$oUmfrageFrage->cName}</strong> - {$oUmfrageFrage->cTypMapped}
                                </div>
                                <div class="panel-body">
                                    <div id="payment">
                                        <div id="tabellenLivesuche" class="table-responsive">
                                            <table class="table">
                                                <tr>
                                                    <th class="th-1" style="width: 5%;">{#umfrageQASing#}</th>
                                                    {foreach name=umfragematrixoption from=$oUmfrageFrage->oUmfrageMatrixOption_arr item=oUmfrageMatrixOption}
                                                        {assign var=maxbreite value=95}
                                                        {assign var=anzahloption value=$oUmfrageFrage->oUmfrageMatrixOption_arr|@count}
                                                        {math equation="x/y" x=$maxbreite y=$anzahloption assign=breite}
                                                        <th class="th-1" style="width: {$breite}%;">{$oUmfrageMatrixOption->cName}</th>
                                                    {/foreach}
                                                </tr>

                                                {foreach name=umfragefrageantwort from=$oUmfrageFrage->oUmfrageFrageAntwort_arr item=oUmfrageFrageAntwort}
                                                    {assign var=kUmfrageFrageAntwort value=$oUmfrageFrageAntwort->kUmfrageFrageAntwort}
                                                    <tr class="tab_bg{$smarty.foreach.umfragefrageantwort.iteration%2}">
                                                        <td class="TD1">{$oUmfrageFrageAntwort->cName}</td>
                                                        {foreach name=umfragematrixoption from=$oUmfrageFrage->oUmfrageMatrixOption_arr item=oUmfrageMatrixOption}
                                                            {assign var=kUmfrageMatrixOption value=$oUmfrageMatrixOption->kUmfrageMatrixOption}
                                                            <td align="center">
                                                                {if $oUmfrageFrage->oErgebnisMatrix_arr[$kUmfrageFrageAntwort][$kUmfrageMatrixOption]->nBold == 1}
                                                                <strong>{/if}
                                                                    {$oUmfrageFrage->oErgebnisMatrix_arr[$kUmfrageFrageAntwort][$kUmfrageMatrixOption]->fProzent}
                                                                    %
                                                                    ({$oUmfrageFrage->oErgebnisMatrix_arr[$kUmfrageFrageAntwort][$kUmfrageMatrixOption]->nAnzahl}
                                                                    )
                                                                    {if $oUmfrageFrage->oErgebnisMatrix_arr[$kUmfrageFrageAntwort][$kUmfrageMatrixOption]->nBold == 1}</strong>{/if}
                                                            </td>
                                                        {/foreach}
                                                    </tr>
                                                {/foreach}
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        {else}
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <strong>{$oUmfrageFrage->cName}</strong> - {$oUmfrageFrage->cTypMapped}
                                </div>
                                <div class="panel-body">
                                    <div id="payment">
                                        <div id="tabellenLivesuche">
                                            <table class="table table-responsive">
                                                <tr>
                                                    <th class="th-1" style="width: 20%;">{#umfrageQASing#}</th>
                                                    <th class="th-2" style="width: 60%;"></th>
                                                    <th class="th-3" style="width: 10%;">{#umfrageQResPercent#}</th>
                                                    <th class="th-4" style="width: 10%;">{#umfrageQResCount#}</th>
                                                </tr>
                                                {foreach name=umfragefrageantwort from=$oUmfrageFrage->oUmfrageFrageAntwort_arr item=oUmfrageFrageAntwort}
                                                    <tr class="tab_bg{$smarty.foreach.umfragefrageantwort.iteration%2}">
                                                        <td class="TD1" style="width: 20%;">{$oUmfrageFrageAntwort->cName}</td>
                                                        <td class="TD2" style="width: 60%;">
                                                            <div class="freqbar" style="width: {$oUmfrageFrageAntwort->fProzent}%; height: 10px;"></div>
                                                        </td>
                                                        <td class="TD3" style="width: 10%;">
                                                            {if $smarty.foreach.umfragefrageantwort.first}
                                                                <strong>{$oUmfrageFrageAntwort->fProzent} %</strong>
                                                            {elseif $oUmfrageFrageAntwort->nAnzahlAntwort == $oUmfrageFrage->oUmfrageFrageAntwort_arr[0]->nAnzahlAntwort}
                                                                <strong>{$oUmfrageFrageAntwort->fProzent} %</strong>
                                                            {else}
                                                                {$oUmfrageFrageAntwort->fProzent} %
                                                            {/if}
                                                        </td>
                                                        <td class="TD4" style="width: 10%;">{$oUmfrageFrageAntwort->nAnzahlAntwort}</td>
                                                    </tr>
                                                    {if $smarty.foreach.umfragefrageantwort.last}
                                                        <tr>
                                                            <td></td>
                                                            <td colspan="2" align="right">{#umfrageQMax#}</td>
                                                            <td align="center">{$oUmfrageFrage->nAnzahlAntworten}</td>
                                                        </tr>
                                                    {/if}
                                                {/foreach}
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        {/if}
                    {/if}
                {/foreach}
            </div>
        {/if}
    </div>
</div>