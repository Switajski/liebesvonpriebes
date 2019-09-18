
{include file='tpl_inc/seite_header.tpl' cTitel=#trustedshops# cBeschreibung=#tsWhatIs#}
<div id="content" class="container-fluid">
    {if !empty($hinweis)}
        <div class="alert alert-info">
            {$hinweis}
        </div>
    {/if}
    {if !empty($fehler)}
        <div class="alert alert-danger userError">
            {$fehler}
        </div>
    {/if}
    
    <div class="container-fluid">
        <table class="table">
            <tr>
                <td colspan="2"><p><label>Trusted Shops - Europas Internet-G&uuml;tesiegel Nr. 1</strong></label></p></td>
            </tr>
            <tr>
                <td><p><label class="left">Trusted Shops ist das bekannte Internet-G&uuml;tesiegel f&uuml;r Online-
                    Shops mit K&auml;uferschutz f&uuml;r Ihre Online-Kunden. Bei einer
                    Zertifizierung wird Ihr Shop umfassenden Sicherheits-Tests
                    unterzogen. Diese Pr&uuml;fung mit mehr als 100 Einzekriterien orientiert
                    sich an den Forderungen der Verbrauchersch&uuml;tzer sowie dem
                    nationalen und europ&auml;ischen Recht.</label></p></td>
                <td valign="top"><img src="{$shopURL}/{$PFAD_GFX_TRUSTEDSHOPS}TS_Certified-Software_180px_blue_RGB.gif" title="Trusted Shops Certified" alt="Trusted Shops Certified"></td>
            </tr>
            <tr>
                <td colspan="2"><p><label class="left">Da JTL-Software GmbH schon bei der Entwicklung dieser Shopsoftware mit Trusted Shops zusammen
                    gearbeitet hat, ist ein Gro&szlig;teil der Zertifizierungsanforderungen bereits jetzt erf&uuml;llt. Der Vorteil f&uuml;r Sie:
                    Sie k&ouml;nnen sich ohne gro&szlig;en Aufwand und zu erm&auml;&szlig;igten Konditionen zertifizieren lassen.</label></p><br />
                    <p><label>Trusted Shops Effekt</strong></label></p>
                    <p><label>G&uuml;tesiegel + K&auml;uferschutz + Service = Vertrauen</strong></label></p><br />
                    <p><label>Welche Leistungen bietet Ihnen Trusted Shops?</strong></label></p>
                    <p><label class="left">
                        1.  Trusted Shops Praxishandbuch mit Mustershop<br />
                        2.  Zertifizierung Ihres Online-Shops mit individuellem Pr&uuml;fungsprotokoll<br />
                        3.  Pers&ouml;nlicher Ansprechpartner f&uuml;r alle Anfragen<br />
                        4.  Updates zu rechtlichen Entwicklungen und relevanten Urteilen<br />
                        5.  K&auml;uferschutz und mehrsprachiges Service-Center f&uuml;r Ihre Kunden<br />
                        6.  Professionelle Streitschlichtung bei Problemfüllen<br />
                        7.  Integriertes Bewertungssystem f&uuml;r Kundenmeinungen<br />
                        8.  Nutzung der Trusted Shops Expertenforen<br />
                        9.  Exklusive Partnerangebote (Payment, Hosting, Marketing etc.)<br />
                        10. Shop-Profil mit Logo und Link (suchmaschinenoptimiert)<br />
                    </p><br />
                    <p><label>Ihre Vorteile durch Trusted Shops:</strong></label></p>
                    <p><label class="left">                            
                        &bull; Mehr Kunden - durch integriertes Marketing<br />
                        &bull; Mehr Umsatz und Vorauskasse - durch das h&ouml;here Verbrauchervertrauen<br />
                        &bull; Mehr Werbung - durch Portal und Newsletter<br />
                        &bull; Mehr Angebote - durch Partner und Dienstleister<br />
                    </label></p>
                    
                    <p>
                        Weitere Informationen und Erfahrungen von zertifizierten Online-Shops finden Sie auf der Trusted<br />
                        Shops Homepage unter <a href="http://www.trustedshops.de/shopbetreiber" target="_blank">www.trustedshops.de/shopbetreiber</a>.                            
                    </p>
                    <p><a href="http://www.trustedshops.de/shopbetreiber/mitgliedschaft_partner.html?shopsw=JTL" target="_blank">Nutzen Sie diese Chance und lassen Sie sich jetzt zum Sonderpreis zertifizieren.</a></p>
                </td>
            </tr>
        </table>
    </div>
    
    <br />
    <strong><a href="trustedshops.php">{#tsBack#}</a></strong>
</div>
