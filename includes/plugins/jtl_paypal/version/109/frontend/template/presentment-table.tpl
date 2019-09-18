{$cf = $financingOption->getCreditFinancing()}
{$mp = $financingOption->getMonthlyPayment()}
{$tc = $financingOption->getTotalCost()}
{$ti = $financingOption->getTotalInterest()}
{$bcf = $bestFinancingOption->getCreditFinancing()}

<table class="table table-financing-option">
    <thead>
        <tr>
            <th colspan="2">
                <div class="rate-label">
                    <span class="badge">{$cf->getTerm()}</span> monatliche Raten
                    {if $bcf->getFinancingCode() == $cf->getFinancingCode()}
                        <i class="ppf-star"></i>
                    {/if}
                </div>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>in monatlicher H&ouml;he von je</td>
            <th class="value">{gibPreisStringLocalized($mp->getValue())}</th>
        </tr>
        <tr>
            <td>fester Sollzinssatz</td>
            <td class="value">{$cf->getNominalRate()|string_format:"%.2f"} %</td>
        </tr>
        <tr>
            <td>effektiver Jahreszins</td>
            <td class="value">{$cf->getApr()|string_format:"%.2f"} %</td>
        </tr>
        <tr>
            <td>Zinsbetrag</td>
            <td class="value">{gibPreisStringLocalized($ti->getValue())}</td>
        </tr>
    </tbody>
    <tfoot class="total">
        <tr>
            <th>Gesamtbetrag</th>
            <th class="value">{gibPreisStringLocalized($tc->getValue())}</th>
        </tr>
    </tfoot>
</table>