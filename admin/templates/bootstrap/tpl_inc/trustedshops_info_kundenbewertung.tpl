<div id="page">
    <div id="content" class="container-fluid">
        <div id="welcome" class="post">
            <h2 class="title"><span>{#trustedshops#}</span></h2>
            <br />
            <strong>{#tsWhatIsRating#}</strong>
        </div>
        
        {if $hinweis}
            <br />
            <div class="userNotice">
                {$hinweis}
            </div>
        {/if}
        {if $fehler}
            <br />
            <div class="userError">
                {$fehler}
            </div>
        {/if}
        
        <div id="example" class="post" style="line-height: normal">
            <table  class="table">
                <tr>
                    <td valign="top">
                        <p><span class="left">Positive und nachpr&uuml;fbare Kundenbewertungen sind f&uuml;r Online-K&auml;ufer ein wichtiger Hinweis f&uuml;r die Vertrauensw&uuml;rdigkeit eines Online-Shops. Die notwendige Software ist bereits in Ihrer Shopl&ouml;sung vorhanden, sodass Sie mit wenigen Klicks auch in Ihrem Shop Kundenbewertungen einholen k&ouml;nnen.<br /><br />
                    Die Nutzer bewerten die Qualit&auml;t der Webseite, der Lieferung, der Ware und des Kundenservices. Zus&auml;tzlich k&ouml;nnen die K&auml;ufer ihrer Bewertung einen freien Kommentar hinzuf&uuml;gen.</span></p>
                    </td>
                    <td valign="top">
                        <img src="{$shopURL}/{$PFAD_GFX_TRUSTEDSHOPS}grafik-kundenbewertungen-kundenmeinungen.jpg" alt="Trusted Shops Certified" />
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <p><strong>Ihre Vorteile</strong></p>
                        <p><span class="left">Lernen Sie die W&uuml;nsche Ihrer Kunden besser kennen und verbessern Sie Ihren Service. Machen Sie so zufriedene Kunden zu Ihren besten Verk&auml;ufern!</span></p>
                        <br />
                        <ul class="default">
                            <li>Mehr Umsatz durch mehr Vertrauen</li>
                            <li>Orientierungshilfe f&uuml;r Ihre Kunden</li>
                            <li>Echte Kundenmeinungen im Shop</li>
                            <li>Besserer Service durch Feedback</li>
                            <li>Webbasierte L&ouml;sung ohne Aufwand</li>
                            <li>Basis f&uuml;r Shop-Optimierungen</li>
                            <li>Auswertungen und Benchmarks</li>
                        </ul>
                        <p><strong>So funktioniert es</strong></p>
                        <ol class="default">
                            <li>Sie melden sich auf dieser Seite f&uuml;r die Trusted Shops Kundenbewertungen an.</li>
                            <li>Sie erhalten Ihre Zugangsdaten und Ihre pers&ouml;nliche Trusted Shops ID per E-Mail, die Sie dann bitte in das Eingabefeld eingeben. Das Widget integriert sich dann automatisch in das Shop-Template und die Bewertungsauffoderung in die System E-Mails.</li>
                            <li>Ihre K&auml;ufer geben Bewertungen ab, die Sie pr&uuml;fen und best&auml;tigen k&ouml;nnen. Im gesch&uuml;tzten Bereich k&ouml;nnen Sie die Kundenbewertungen analysieren.</li>
                        </ol>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <p class="submit btn-group">
                            <a class="btn btn-default" href="trustedshops.php"><i class="fa fa-angle-double-left"></i> {#tsBack#}</a>
                            <a class="btn btn-primary" href="https://www.trustedshops.de/shopbetreiber/kundenbewertung_anmeldung.html?partnerPackage=JTL" target="_blank"><i class="fa fa-external-link"></i> Jetzt kostenlos registrieren!</a>
                        </p>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>