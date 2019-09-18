{*
-------------------------------------------------------------------------------
    JTL-Shop 3
    File: trustedshops_info_kundenbewertung.tpl, smarty template inc file
    
    page for JTL-Shop 3 
    Admin
    
    Author: daniel.boehemr@jtl-software.de, JTL-Software
    http://www.jtl-software.de
    
    Copyright (c) 2010 JTL-Software

-------------------------------------------------------------------------------
*}

<div id="page">
    <div id="content">
        <div id="welcome" class="post">
            <h2 class="title"><span>{#trustedshops#}</span></h2>
            <br />
            <strong>{#tsWhatIsRating#}</strong>
        </div>
        
        {if $hinweis}
            <br>
            <div class="userNotice">
                {$hinweis}
            </div>
        {/if}
        {if $fehler}
            <br>
            <div class="userError">
                {$fehler}
            </div>
        {/if}
        
        <div id="example" class="post" style="line-height: normal">
            <table>
                <tr>
                    <td valign="top"><p><label class="left">Positive und nachpr&uuml;fbare Kundenbewertungen sind f&uuml;r Online-K&auml;ufer ein wichtiger Hinweis f&uuml;r die Vertrauensw&uuml;rdigkeit eines Online-Shops. Die notwendige Software ist bereits in Ihrer Shopl&ouml;sung vorhanden, sodass Sie mit wenigen Klicks auch in Ihrem Shop Kundenbewertungen einholen k&ouml;nnen.<br /><br />
                    Die Nutzer bewerten die Qualit&auml;t der Webseite, der Lieferung, der Ware und des Kundenservices. Zus&auml;tzlich k&ouml;nnen die K&auml;ufer ihrer Bewertung einen freien Kommentar hinzuf&uuml;gen.</label></p></td>
                    <td valign="top"><img src="{$URL_SHOP}/{$PFAD_GFX_TRUSTEDSHOPS}grafik-kundenbewertungen-kundenmeinungen.jpg" alt="Trusted Shops Certified"></td>
                </tr>
                <tr>
                    <td colspan="2">
                        <p><label>Ihre Vorteile</strong></label></p><br />
                        <p><label class="left">Lernen Sie die W&uuml;nsche Ihrer Kunden besser kennen und verbessern Sie Ihren Service. Machen Sie so zufriedene Kunden zu Ihren besten Verk&auml;ufern!</label></p>
                        <p><label class="left">
                            &bull;  Mehr Umsatz durch mehr Vertrauen<br />
                            &bull;  Orientierungshilfe f&uuml;r Ihre Kunden<br />
                            &bull;  Echte Kundenmeinungen im Shop<br />
                            &bull;  Besserer Service durch Feedback<br />
                            &bull;  Webbasierte L&ouml;sung ohne Aufwand<br />
                            &bull;  Basis f&uuml;r Shop-Optimierungen<br />
                            &bull;  Auswertungen und Benchmarks<br />
                        </p><br />
                        <p><label>So funktioniert es</strong></label></p>
                        <p><label class="left">
                        <table style="margin: 0px;"> 
                            <tr>                           
                                <td style="width: 10px;" valign="top">1.</td><td>Sie melden sich auf dieser Seite f&uuml;r die Trusted Shops Kundenbewertungen an.</td>
                            </tr>
                            <tr>                           
                                <td style="width: 10px;" valign="top">2.</td><td>Sie erhalten Ihre Zugangsdaten und Ihre pers&ouml;nliche Trusted Shops ID per E-Mail, die Sie dann bitte in das Eingabefeld eingeben. Das Widget integriert sich dann automatisch in das Shop-Template und die Bewertungsauffoderung in die System E-Mails.</td>
                            </tr>
                            <tr>                           
                                <td style="width: 10px;" valign="top">3.</td><td>Ihre K&auml;ufer geben Bewertungen ab, die Sie pr&uuml;fen und best&auml;tigen k&ouml;nnen. Im gesch&uuml;tzten Bereich k&ouml;nnen Sie die Kundenbewertungen analysieren.</td>
                            </tr>
                        </table>
                        </label></p>
                        
                        <p><strong><a href="https://www.trustedshops.de/shopbetreiber/kundenbewertung_anmeldung.html?partnerPackage=JTL" target="_blank">Jetzt kostenlos registrieren!</a></strong></p>
                    </td>
                </tr>
            </table>
        </div>
        
        <br />
        <strong><a href="trustedshops.php">{#tsBack#}</a></strong>
    </div>
</div>