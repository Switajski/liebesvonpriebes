# JTL-Shop Changelog

## [4.06.14]

Dieses Update enthält Bugfixes und ein kritisches Sicherheitsupdate.

Alle für diese Zielversion gelösten Vorgänge finden Sie unter diesem Link: https://jtl-url.de/cl40614

## [4.06.13] - 2019-07-25

Dieses Update enthält Bugfixes und Sicherheitsupdates.

Alle für diese Zielversion gelösten Vorgänge finden Sie unter diesem Link: https://jtl-url.de/cl40613

## [4.06.12] - 2019-04-02

Dieses Update enthält Bugfixes und Sicherheitsupdates.

Alle für diese Zielversion gelösten Vorgänge finden Sie unter diesem Link: https://jtl-url.de/cl40612

## [4.06.11] - 2018-11-30

Dieses Update enthält einen Bugfix zur Unterstützung von MySQL-Server 5.5 und kleiner. 

## [4.06.10] - 2018-11-30

Dieses Update enthält Bugfixes und Sicherheitsupdates.

Alle für diese Zielversion gelösten Vorgänge finden Sie unter diesem Link: https://jtl-url.de/cl40610

## [4.06.9] - 2018-09-19

Dieses Update enthält Fehlerbehebungen. 

Alle für diese Zielversion gelösten Vorgänge finden Sie unter diesem Link: https://jtl-url.de/cl4069

## [4.06.8] - 2018-09-14

Dieses Update enthält Fehlerbehebungen. 

Alle für diese Zielversion gelösten Vorgänge finden Sie unter diesem Link: https://jtl-url.de/cl4068

## [4.06.7] - 2018-09-14

Dieses Update enthält Fehlerbehebungen. 

Alle für diese Zielversion gelösten Vorgänge finden Sie unter diesem Link: https://jtl-url.de/cl4067

## [4.06.6] - 2018-06-28

Dieses Update enthält Fehlerbehebungen. 

Alle für diese Zielversion gelösten Vorgänge finden Sie unter diesem Link: https://jtl-url.de/cl4066

## [4.06.5] - 2018-06-12

Dieses Update enthält Fehlerbehebungen. 

Alle für diese Zielversion gelösten Vorgänge finden Sie unter diesem Link: https://jtl-url.de/cl4065

## [4.06.4] - 2018-04-25

Dieses Update enthält Bugfixes und Sicherheitsupdates.

Alle für diese Zielversion gelösten Vorgänge finden Sie unter diesem Link: https://jtl-url.de/cl4064

## [4.06.3] - 2018-01-29

Dieses Update enthält Bugfixes und Sicherheitsupdates.

Alle für diese Zielversion gelösten Vorgänge finden Sie unter diesem Link: https://jtl-url.de/cl4063

## [4.06.2] - 2017-11-28

* Sicherheitsupdate

## [4.06.1] - 2017-11-24

* [SHOP-1345] - Kupon-Statistikfilter liefert inkorrekte Ergebnisse
* [SHOP-1579] - VarKombi Artikeldetails des Kinds nicht erreichbar, wenn Kind nicht in Warenkorb legbar
* [SHOP-1610] - Upload geht nach Login verloren
* [SHOP-1784] - Lieferzeitraum-Angaben in Bestelldetails (Mein Konto) und Bestellbestätigung zu hoch bei > 14 Tagen
* [SHOP-1788] - Billpay Ratenzahlung Zahlung wird nicht vollständig gesetzt
* [SHOP-1789] - Bei 1 dimensionalen Varkombis fehlt die direkte Verfügbarkeitsinfo an den Variationen
* [SHOP-1803] - Falsche Berechnung von Lieferzeit bei Stücklisten
* [SHOP-1809] - Umlautproblem in der Suchvorschau
* [SHOP-1824] - Plugins, die den EventListener nutzen, lassen sich nicht aktualisieren
* [SHOP-1829] - Merkmalwertfilter prüfen auf falsche Bild-URL
* [SHOP-1836] - Die Formularvalidierung bei der Registrierung im Checkout leitet auf die falsche Seite
* [SHOP-1837] - Mailbenachrichtigung / Verfügbarkeitsanfrage bei nichtvorhandener Varkombi in Listenansicht nicht möglich
* [SHOP-1849] - Kupons nach "Zuletzt verwendet"-Sortieren nicht möglich
* [SHOP-1858] - Verfügbarkeitsanfrage bei ausverkauften Artikeln wird nicht in der DB gespeichert
* [SHOP-1860] - Fehlende Lagerbestands-Neuberechnung bei Stücklisten mit unsichtbaren Komponenten
* [SHOP-1760] - Label in Variationswahl nicht sichtbar


## [4.06] - 2017-10-17

### Wichtige Änderungen

#### Teilverschlüsselung wird nicht länger unterstützt
JTL-Shop 4.06 unterstützt nicht länger die Option Teilverschlüsselung, also den automatischen Wechsel von http auf https z.B. beim Wechsel in den Warenkorb. 
Sofern zum Update-Zeitpunkt noch Teilverschlüsselung in den Shop-Einstellungen aktiv war, wird permanentes SSL aktiviert. 
Tipps für einen reibungslosen Wechsel auf permanentes SSL finden Sie im JTL-Guide: http://jtl-url.de/iurf7

#### Verkürzter Checkout mit 3 Schritten
Der Bestellvorgang wurde von 5 auf nur noch 3 Schritte reduziert und benutzerfreundlicher umgestaltet. 
Bei angepassten Templates oder individuellen Plugins sind unter Umständen Änderungen notwendig. 
Nachfolgend sind die wichtigsten Änderungen aufgeführt: 
 * `checkout/step1_proceed_as_guest.tpl` und `checkout/step2_delivery_address.tpl` sind als `@deprecated` markiert und werden nicht mehr verwendet
 * `checkout/step4_payment_options.tpl` wurde grundlegend geändert
 * `checkout/step1_edit_customer_address.tpl` ist neu und muss in Drittanbieter-Templates integriert werden, da sie vom Shop-Core verwendet wird
 * Hinweise zu Template-Änderungen finden Sie [hier](http://docs.jtl-shop.de/de/latest/shop_templates/short_checkout.html)
 * Hinweise für Plugin-Anpassungen finden Sie [hier](http://docs.jtl-shop.de/de/latest/shop_plugins/short_checkout.html)

#### Template-Kompatibilität zu JTL-Shop3-Tiny standardmäßig inaktiv
Der Kompatibilitätsmodus für die Nutzung eines Tiny-basierten Templates in JTL-Shop 4 ist ab Version 4.06 standardmäßig inaktiv.
Shops mit Evo oder kompatiblen Shop4-Templates benötigen diesen Modus nicht und profitieren mit einem leichten Performancegewinn von der Deaktivierung.   
Wer den Kompatibilitätsmodus zum Betrieb seines Templates benötigt, kann diesen mit einer zusätzlichen Zeile in der config.JTL-Shop.ini.php 
aktivieren: define('TEMPLATE_COMPATIBILITY', true);

#### Variationsauswahl und Detailvorschau in Artikelübersichten
In den Artikelübersichten ist es jetzt möglich direkt Variationsauswahlen vorzunehmen und eine verkürzte Detailansicht als Popup in der Liste
anzeigen zu lassen. Die Anzahl der möglichen Variationen und die Detailansicht lassen sich in der Templatekonfiguration einstellen.

#### Evo-Templateänderungen
Ein Template-Diff mit allen Änderungen zwischen v4.05 und v4.06 finden Sie [hier](https://gitlab.jtl-software.de/jtlshop/shop4/snippets/39). 

#### Weitere Anpassungen
Das PayPal-Plugin wird in Version 1.08 ausgeliefert und beinhaltet nun auch die Zahlart Ratenzahlung powered by PayPal. 
Bitte beachten Sie auch das aktualisierte Handbuch zum PayPal-Plugin: http://jtl-url.de/paypaldocs

### Changelog 

#### Allgemeine Änderungen
* [SHOP-701] - Löschung des Kundenaccounts bei offenen Bestellungen neu lösen
* [SHOP-835] - Bei JTL-Search-Suchergebnissen müssen freigeschaltete Livesuchbegriffe ignoriert werden
* [SHOP-899] - Warenkorbmatrix soll in Listenansicht nach Anzahl Varkombis begrenzt werden (nicht nach #Variationen)
* [SHOP-939] - Sitemapexport: Seiten mit "noindex"-Attribut vom Export ausschließen
* [SHOP-958] - Auswahlassistent-Template an Evo-Optik anpassen (Selectboxes durch Dropdowns ersetzen)
* [SHOP-964] - Im meta-Tag "robots" der Spezialseiten den "content" auf "nofollow, noindex" setzen
* [SHOP-969] - Kupon anlegen: Netto/Bruttopreis-Info bei prozentualem Kuponwert verstecken
* [SHOP-1194] - Gratisgeschenk ab "Mindestwarenwert" Umbenennung
* [SHOP-1223] - Merkmalfilter: Anzeigetyp Selectbox überarbeiten (Name bislang doppelt in Panel-Title und Selectbox-Title)
* [SHOP-1230] - Max. Bestellnummernlänge von derzeit 20 Zeichen erhöhen
* [SHOP-1330] - Überbleibsel vom Commerz Finanz-Modul entfernen
* [SHOP-1341] - ID "article-tabs" wieder hinzufügen im Artikeldetails Tabcontent
* [SHOP-1377] - Breadcrumb sollte entweder absolute oder relative URLs beinhalten
* [SHOP-1633] - Templatekompatibilität (zu JTL-Shop3-Tiny) standardmäßig inaktiv
* [SHOP-1659] - Evo-Child-Example nicht mehr im Installationspaket ausliefern
* [SHOP-1663] - TrustedShops-Plugin Repository umgezogen

#### Bugfixes
* [SHOP-178] - Lieferzeit bei Stücklisten falsch, wenn Komponenten unsichtbar sind
* [SHOP-190] - Bei PayPal Plus wird Trusted Shops Excellence nicht angezeigt.
* [SHOP-216] - Neukundenkupon verfällt, wenn der Inhalt des Warenkorbes geändert wird.
* [SHOP-242] - Kupon wird nach Login aus dem Warenkorb entfernt
* [SHOP-243] - Checkout: Ein-Klick-Methode überspringt Versandartschritt, auch wenn keine Versandarten angeboten werden
* [SHOP-251] - Bestellsummen nach Anpassung des Währungsfaktors falsch
* [SHOP-326] - Extra Beschreibungstexte bei Zahlungs- und Versandarten für Mailvorlagen
* [SHOP-349] - Merkmale in Artikeldetails haben keinen vertikalen Abstand
* [SHOP-374] - Warenkorb-Fehlermeldung wegen fehlendem Pflicht-Upload wird bei anschließendem Datei-Upload weiter angezeigt
* [SHOP-384] - Bildergenerierung im Admin: Fehler-Logs, wenn Artikelbilder noch in alter Ordnerstruktur sind
* [SHOP-426] - Artikelsuche findet keine Ergebnisse, wenn Suchwörter in verschiedenen Feldern z.B. Bezeichnung + Suchbegriffe stehen
* [SHOP-465] - Sprachverwaltung: Nicht gefundene Variablen - kontextbezogene Aktionen ergänzen
* [SHOP-473] - Artikelsortierung in Warenkorbmatrix-Liste inkorrekt (kArtikel)
* [SHOP-474] - Merkmale ohne Merkmalwert-Übersetzung werden in Fremdsprache nicht angezeigt
* [SHOP-483] - Artikel- und Versandgewicht bei Variationen immer gleich
* [SHOP-527] - Backend: Header zeigt falsches Benutzerbild wenn über Plugin ein Bild hochgeladen wurde
* [SHOP-562] - Boxenverwaltung: Container-Platzierung/Darstellung fehlerhaft  
* [SHOP-603] - Gelöschte Bezahlung in der Wawi setzt das Bezahldatum im Shop nicht zurück
* [SHOP-627] - Trusted Shops - Zertifikatsprüfung fehlerhaft
* [SHOP-645] - Konfigurationen mit virtuellen Artikel werden nach Login aus WK gelöscht
* [SHOP-653] - Herstellermenü wird nicht angezeigt, wenn die Einstellung "Artikel/Kategorien erst nach Login sichtbar" aktiv ist
* [SHOP-658] - Sofortüberweisung Zahlungsbenachrichtigungen können mehrfach eingehen
* [SHOP-667] - HTTP-Statuscode im Wartungsmodus auf 503 setzen
* [SHOP-672] - Shop 4.04: Wenn Einstellung 334 auf Ja gesetzt ist verhält sich der PayPal Express Button falsch
* [SHOP-703] - Update Problem Shop3-zu-Shop4: Menüstruktur zerstört
* [SHOP-705] - PaymentMethod: $nAgainCheckout Parameter nicht gesetzt
* [SHOP-710] - Bei UVP Anzeige in den Artikeldetails wird bei (umsatzsteuerbefreiten) Auslandskunden ein Rabatt zwischen Netto-Artikelpreis und UVP ausgewiesen
* [SHOP-711] - Sitemap exportiert falsche Bilderlinks
* [SHOP-718] - Bestellungen mit Betrag 0 haben keine Zahlungsart
* [SHOP-719] - Extrem Lange Ladezeiten bei mehrdimensionalen Variationskombinationen und > 400 Optionen je Variation
* [SHOP-747] - PayPal PLUS und Express: Zahlungseinstellungen Anzahl Bestellungen nötig, Mindestbestellwert, Maximaler Bestellwert greifen nicht
* [SHOP-771] - Einstellung " Meta Title überall anhängen" auf CMS-Seiten wirkungslos
* [SHOP-785] - Zulauf wird in Listenansicht trotz Deaktivierung immer angezeigt, wenn Artikel keine Überverkäufe ermöglicht.
* [SHOP-794] - Breadcrumb ändert sich nicht bei Varkombiauswahl
* [SHOP-795] - Lagerbestandsanzeige bei Varkombis nicht sichtbar
* [SHOP-802] - Guthaben im Warenkorb aktualisiert sich nicht bei Hinzufügen eines weiteren Artikels
* [SHOP-823] - Suche: "Maximale Treffer pro Suche" inkludiert Artikel, die nicht angezeigt werden
* [SHOP-847] - Persistenter Warenkorb zeigt Menge falsch an nach dem Login
* [SHOP-853] - Persistenter Warenkorb fasst Konfiguratorkomponenten von Konfiguratoren in Variationen falsch zusammen
* [SHOP-856] - PayPal 1.06: Fehler im Express-Checkout
* [SHOP-858] - Variationen und VarKombis mit Freitext im persistenten Warenkorb beachten
* [SHOP-859] - Sitemap ignoriert Artikelanzeigefilter
* [SHOP-863] - externe Bildschnittstelle zeigt kein Bild wenn es nicht zuvor generiert wurde
* [SHOP-879] - Falsche Kürzung bei Meta-Title
* [SHOP-880] - Überverkäufe durch Schnellkauf-Funktion möglich
* [SHOP-885] - Vorschau der Blog/Newsbeiträge: Links und Bildpfade inkorrekt
* [SHOP-886] - Variation mit LB=0 in Variationswerten noch in der Produktübersicht
* [SHOP-889] - Login während Verwendung der Wunschliste nicht möglich
* [SHOP-890] - Kategorieattribut "darstellung" funktioniert nicht kategoriebezogen
* [SHOP-895] - Konfigurator->postcheckBasket() löscht unter Umständen falschen Artikel aus dem Warenkorb
* [SHOP-915] - Pagination mit sehr großen Eingabe-Arrays inperformant
* [SHOP-917] - PayPal PLUS - Bestellnummer auf der Bestellabschlussseite stimmt nicht mit tatsächlicher Bestellnummer überein
* [SHOP-931] - Falsches Verhalten beim Konfigurator bezüglich Verfügbarkeit und Lieferzeit
* [SHOP-932] - Zusammenfassen vom persistenten Warenkorb leitet auf Kundendetail-Seite
* [SHOP-941] - Bannerzonen ohne Artikellink leiten auf Startseite
* [SHOP-950] - dbeS/seo.php: Ineffiziente checkSeo-Funktion zur Generierung eindeutiger URLs
* [SHOP-953] - Rechtschreibung Backend E-Mail-Vorlage "Warenrücksendung abegeschickt"
* [SHOP-955] - Overlay "Neu" beachtet Checkbox "Neu im Sortiment" aus der Wawi nicht
* [SHOP-965] - Checkout: Neukundenkupon Einlösung vor Bestellabschluss (z.B. für PayPal PLUS)
* [SHOP-966] - Bug JS-Event "popstate" in Safari und Chrome
* [SHOP-972] - Emailvorlage Bonus nach Bewertung mischt Sprachen
* [SHOP-973] - Überschriften in Vergleichsliste-Modalfenstern in verschiedenen Themes nicht lesbar
* [SHOP-977] - Zuschlagliste zeigt bei Fehler falsche Meldung
* [SHOP-979] - Weiterleitungen funktionieren nicht wenn in der Quellurl Parameter übergeben werden
* [SHOP-981] - Admin-Dashboard-Widget "Webcrawler (Top10/Monat)" stylen
* [SHOP-982] - Artikel-Objekt im Warenkorb ist leer bei "unsichtbaren" Konfigurationskomponenten
* [SHOP-990] - Weiterleitungsschleife bei SSL-Teilverschlüsselung z.B. bei Mein-Konto oder Kontakt
* [SHOP-1017] - Boxenverwaltung: vordefinierte Boxen bilden nicht alle Seitentypen ab
* [SHOP-1025] - PHP Memory Limit wird nicht korrekt ausgelesen / geprüft
* [SHOP-1031] - Falsche Währungsumrechnung bei Sofortüberweisung
* [SHOP-1034] - Einschränkung auf Artikel(Artikelauswahl) fehlende js Validation
* [SHOP-1035] - Kuponexport exportiert keine Übersetzungen (nur interne Name)
* [SHOP-1036] - Fehlende Prüfung beim Kupon-CSV-Import auf bereits vorhandenen Kupon mit gleichem Code
* [SHOP-1041] - Wawi-Shop-Sync: Schlechte Performance bei sehr vielen Preisanpassungen und aktivem Preisverlauf
* [SHOP-1043] - Aktivieren von Bewertungen invalidiert Cache nicht
* [SHOP-1046] - Plugin-E-Mail-Vorlagen kaputt bei Updates
* [SHOP-1051] - Variationswertbilder werden nicht korrekt geladen bei mehrdimensionalen (einfachen) Variationen
* [SHOP-1053] - Admin: Im Aufgabenplaner fehlt der Button "Cron manuell anstoßen", wenn keine Exportformate geplant sind
* [SHOP-1062] - Bei Sonderpreisen greift die Versandfreigrenze obwohl diese oberhalb des Sonderpreises liegt.
* [SHOP-1067] - UStID-Prüfung für Griechenland falsch / IGL-Lieferungen nach Griechenland nicht möglich
* [SHOP-1072] - Hintergundfarbe des Wasserzeichen liegt im Vordergrund des Kategoriebildes
* [SHOP-1076] - Race Condition Jobqueue Lock-File
* [SHOP-1091] - Kupon CSV Import nicht ausführbar bei aktiviertem Errorlogging
* [SHOP-1102] - Typ TEXT reicht für die Erstellung eines Newsletter nicht (mehr) aus!
* [SHOP-1104] - Warenkorbmatrix Quer- und Hochformat Backend Hinweis auf Variationsanzahl
* [SHOP-1108] - Falsche Berechnung von Stücklisten-Lagerbestand beim Kauf von Komponenten
* [SHOP-1109] - Unnötige SQL-Statements durch getCheckBoxForLocation() in functions.php des Templates
* [SHOP-1110] - Lange Wartezeiten beim Bestellabschluss von Stücklisten-Artikel
* [SHOP-1113] - Artikelabstand in Listenansicht ist verschwunden
* [SHOP-1119] - Slider-Filter "Hersteller" fehlerhaftes Verhalten
* [SHOP-1120] - Wunschliste wird immer unter Kundenkonto angezeigt
* [SHOP-1125] - Smarty-Variable showLoginCaptcha nur auf Loginseite definiert
* [SHOP-1134] - Upload-Modul funktioniert nicht in Verbindung mit PayPal Plugin
* [SHOP-1138] - Bewertungserinnerung Mail wird an Kunden versendet, welche den Artikel bereits bewertet haben
* [SHOP-1144] - Weitlereitung CSV Import funktioniert nicht für Datensätze, deren Quell-URL schon vorhanden ist
* [SHOP-1155] - navbar-default-link-active-bg nicht über LiveStyler anpassbar
* [SHOP-1158] - unsichtbare Footerboxen erzeugen Leerraum
* [SHOP-1160] - PLZ-Ort-Prüfung stellt zu wenig Ergebnisse dar
* [SHOP-1161] - PHP7: Non well formed numeric value in rss_inc.php
* [SHOP-1164] - Regression: HOOK_SMARTY_OUTPUTFILTER wird in tauscheVariationKombi() nicht mehr ausgeführt
* [SHOP-1173] - In der Artikelübersicht kein Wechsel von Listenansicht auf Gallerieansicht möglich
* [SHOP-1183] - Konfigurator: Falsche Komponentenmengen Anzeige
* [SHOP-1185] - Meta-Tags Paginierungs-Informationen inkonsistent ("Doppelte Metabeschreibungen" und "Doppelte Meta-Titel")
* [SHOP-1191] - Filtern-Nach-Dropdown: Versetzte Ansicht der Ergebnisanzahl
* [SHOP-1203] - Kundendaten: Falsche Fehlermeldung bei nicht ausgefüllter Mailadresse
* [SHOP-1206] - Herstellerbilder werden ausgeblendet sobald eine Seo-URL hinterlegt wird
* [SHOP-1209] - Verfügbarkeitsbenachrichtigung wird in der DB falsch angelegt
* [SHOP-1210] - Newsbeiträge - Falsche Zuordnung der Zeitzonen
* [SHOP-1216] - Fehlerhafte Plugins werden in der Pluginverwaltung nicht gelistet
* [SHOP-1263] - Neukunden Kupons sind nicht einlösbar wegen falscher Methoden-Rückgabe
* [SHOP-1265] - Fragmentbezeichner in Ankerlinks: Regex aus Smooth Scroll ohne Funktion
* [SHOP-1270] - Stücklistenbestand ändert sich unter Umständen bei Kauf auf 999999 - falsche Lieferzeit
* [SHOP-1271] - Fehlerhafte Grundpreisanzeige in Variationsauswahl, nachdem Grundpreis entfernt wurde
* [SHOP-1275] - Warenkorbmatrix blendet Artikel mit LB=0 nicht aus
* [SHOP-1278] - Umfragemodul speichert Ergebnisse nur, wenn Besucher angemeldet ist
* [SHOP-1283] - Backend-Hauptmenü in mobiler Ansicht fehlerhaft
* [SHOP-1284] - Weiterleitungen - Pagination berücksichtige nicht die momentane Filterung
* [SHOP-1291] - Exportformate mit smarty-condition erzeugen Leerzeilen
* [SHOP-1298] - Kuponeingabefeld wird nur für den ersten eingeschränkten Artikel angezeigt
* [SHOP-1300] - Nach Ausführung von HOOK_ARTIKEL_CLASS_FUELLEARTIKEL werden Artikelfelder erneut überschrieben
* [SHOP-1303] - Englischsprachiger Shop wechselt auf Deutsch, wenn Banner-Zone geklickt wird
* [SHOP-1304] - Warenkorb zeigt PHP-Notices, wenn Artikel keinen englischen Namen besitzt
* [SHOP-1309] - Newskategorien zeigen keine Beschreibung an
* [SHOP-1313] - Bestell-Kommentar mit Umbrüchen wird inkorrekt gespeichert
* [SHOP-1317] - SQL-Abfrage für Zahlart-Plugineinstellungen im Admin fehlerhaft
* [SHOP-1332] - WK Konfigurator konfigurieren: Komponenten vergessen ihre eigene Menge
* [SHOP-1338] - X-Selling (Kunden, die X gekauft haben, haben auch Y gekauft) erzeugt Slow-Querys bei vielen Datensätzen
* [SHOP-1364] - Fatal Error beim Abgleich von Kundendaten unter PHP7.1
* [SHOP-1365] - Sprachwechsel auf news.php leitet zur Startseite weiter
* [SHOP-1438] - Tooltips in Kupon bearbeiten funktionieren nicht
* [SHOP-1445] - Trying to get property on non-object in dbeS/Bestellungen_xml.php
* [SHOP-1465] - Kupon-Artikelpicker: Artikelnummern mit Sonderzeichen führen zu fehlerhaften Element-IDs
* [SHOP-1489] - Variationswahl in Artikelliste lädt Inhalt falsch
* [SHOP-1524] - Versandklassen-Kombination "Alle" kann nicht gespeichert werden
* [SHOP-1527] - Registrieren: Keine Fehlermeldung bei bereits existierendem Kundenkonto 
* [SHOP-1557] - Shop Backend über Iphone nicht steuerbar
* [SHOP-1575] - Variationsauswahl Radiobutton-Labels werden in Großschrift ausgegeben
* [SHOP-1588] - Lazy-loading in Artikellisten funktioniert nicht
* [SHOP-1597] - Versandart ist nicht wählbar wenn "alle Kombinationen" erlaubt
* [SHOP-1598] - Merkmalfilter im Contentbereich zeigt die Anzahl versetzt zu den Werten an (Firefox)
* [SHOP-1614] - 2FA QR-Code wird falsch berechnet und ist nicht scanbar
* [SHOP-1615] - Checkout: Mobile Ansicht verbessern
* [SHOP-1621] - doppelte Rahmen in Bestellzusammenfassung, simplex theme
* [SHOP-1622] - tartikel.fstandardpreisnetto wird bei QuickSync nicht geupdated
* [SHOP-1623] - Accessibility-Verbesserungen
* [SHOP-1629] - Tabelle in "Ihre Konfigration" überschreitet bei langen Wörter den Boxrand
* [SHOP-1653] - Die News-Anzeige auf der Startseite arbeitet nicht sauber mit dem "jtl_backenduser_extension"-Plugin zusammen
* [SHOP-1666] - Backend: Kupon-Tabelle zusammenfassen
* [SHOP-1754] - große Kategoriebilder ragen über die Seite hinaus


#### Story / Feature
* [SHOP-3] - Hook in admin/shopzuruecksetzen.php
* [SHOP-180] - Schnittstellenerweiterung: Wawi-Sync von Bestellattributen
* [SHOP-204] - Verbesserung: Variationsauswahl für Varianten + Varkombis direkt in Artikelliste
* [SHOP-336] - Neue Option, um Versandarten für Lieferzeitberechnung und günstigste Versandberechnung auszuschließen
* [SHOP-355] - Backend-Feature: OpenGeoDB-Import
* [SHOP-370] - Beschränkung auf max 5 Versandklassen aufheben, Darstellungsform ändern
* [SHOP-380] - Refactoring des Auswahlassistenten
* [SHOP-401] - Variationen per AJAX in WK legbar machen
* [SHOP-410] - Neue Youtube Shortlinks (youtu.be) unterstützen
* [SHOP-439] - FR: Kupons auf Hersteller beschränken
* [SHOP-466] - Sprachverwaltung Benutzerfreundlichkeit verbessern
* [SHOP-778] - Suchfeld in mobiler Ansicht überarbeiten
* [SHOP-827] - Strukturierte Daten für WebPage, ItemPage, CheckoutPage etc. bereitstellen
* [SHOP-882] - Warenkorb: Versandgewicht je Position anzeigen (Artikeldetails-Einstellung für Artikelgewichte abfragen)
* [SHOP-951] - Automatische Anpassung der Grundpreismengenheit bei Staffelung und Gesamt-Füllmengen > 250 g/ml
* [SHOP-956] - Unterstützung für APCu als Cache-Methode (APC) hinzufügen
* [SHOP-1032] - Antwort-Funktion für Produktbewertungen
* [SHOP-1048] - Formularvalidierung bei fehlenden Pflichteingaben benutzerfreundlicher gestalten
* [SHOP-1129] - Neuer Smarty Block - Productdetails -> details.tpl => Bewertungen
* [SHOP-1131] - Neuer Smarty Block - Productdetails -> variation.tpl => Variationen
* [SHOP-1132] - Neuer Smarty Block - Productdetails -> details.tpl => stock.tpl
* [SHOP-1133] - Neuer Smarty Block - Productdetails -> details.tpl => price.tpl
* [SHOP-1140] - Neuer Smarty Block - Layout -> header_category_nav.tpl
* [SHOP-1142] - Neuer Smarty Block - Productdetails -> attributes.tpl
* [SHOP-1156] - Beitragsanzahl auf der News-Seite konfigurierbar machen
* [SHOP-1172] - FR: Bilder zu News im Newsobject halten
* [SHOP-1178] - Evo: Bei Nachladen von Content Events triggern, die im Custom-JS-Code abgefangen werden können
* [SHOP-1184] - Versionsangabe in Child-Template template.xml nicht mehr verpflichtend
* [SHOP-1187] - Lieferzeitberechnung: Versandarten mit niedriger Sortiernr priorisieren
* [SHOP-1220] - Neuer Smarty Block: Layout header.tpl #main-wrapper
* [SHOP-1221] - Neuer Smarty Block: Layout footer.tpl
* [SHOP-1235] - Neue Smarty Blocks im Footer
* [SHOP-1294] - Bilder, die im Backend hinzugefügt werden, müssen mit relativer Pfadangabe zur Shop-Base gespeichert werden
* [SHOP-1340] - Klick auf Stern-Bewertung sollte Bewertungstab öffnen
* [SHOP-1439] - Nach Artikelnummern suchen bei "Kupon bearbeiten"
* [SHOP-1545] - Varkombi Artikeldetails: Variationsabhängigkeiten werden initial nicht geladen 
* [SHOP-1592] - .php_cs an PHP-CS-Fixer Version 2.0 anpassen
* [SHOP-1634] - Template-Namen sollten zum Debugging optional im Frontend ausgegeben werden
* [SHOP-1651] - Einstellung "Merkmale anzeigen als" ohne Funktion
* [SHOP-1654] - Fehlende Fehlermeldung bei Gast Bestellung mit Download Artikel
* [SHOP-1658] - Header/Content/Footer-Blocks für alle Haupt-Templates
* [SHOP-1660] - Template-Vererbung (Smarty-Blocks) über Plugins unterstützen
* [SHOP-1667] - Backend Weiterleitungen nach Aufrufen filtern
* [SHOP-1674] - Neue Smarty Blocks in snippets/shipping_tax_info.tpl und productdetails/price.tpl
* [SHOP-1694] - Backstretch-Hintergrundbild verursacht Fehler bei geringer Netzgeschwindigkeit

## [4.05.6] - 2018-01-29

* Sicherheitsupdate

## [4.05.5] - 2017-11-25

* Sicherheitsupdate

## [4.05.4] - 2017-11-24

* Schutzfunktion im Uploadmodul verbessert


## [4.05.3] - 2017-04-27

### Bugfixes

* Beim Export von Exportformaten wird der neue Dateiinhalt an alte Datei angehängt (#1217)
* Bei News-Kategoriebeschreibung kann kein HTML verwendet werden (#1197)

## [4.05.2] - 2017-04-04

### Bugfixes 

* Varkombivorschaubilder werden nicht mehr gruppiert angezeigt (#985)
* Detailinformationen von Seiteninhalten gehen beim Speichern von eigenen Seiten verloren (#998)
* Fehlende Vorschaubilder in der Newsübersicht (#1011)
* Admin Sitemapexport speicherhungrig (zeitlich unbegrenzte Statistikauswertung) (#986)
* E-Mail-Bestellbestätigung: Lieferzeit an Bestellpositionen fehlt (#1004)
* Bewertungen werden nicht vollständig angezeigt (#1000)
* Falsche Meldung bei 2-Faktor-Authentifizierung (#968)
* Neue SEO-URLs beachten Kundengruppen nicht (#1003)
* Prozentuale Neukundenkupons werden nicht eingelöst bei Ganzer WK "Nein" (#1007)
* Konfigurationsartikelbeschreibung wird nicht angezeigt, wenn Komponente mit Artikel verknüpft ist, aber individuelle Beschreibung enthält (#980)
* E-Mails bringen Dateianhang-Namen durcheinander (#1016)
* Abfrage auf spezielle Kundengruppe in cKundengruppe funktioniert nur bei einstelligen Kundengruppen-IDs (#997)
* Hinweistext für Versandarten lässt kein HTML zu (#1039)
* Kupon: "Beschränken auf Kunden" funktioniert bei aktiviertem Errorlogging nicht (#1092)
* Beim Editieren von Artikel-Bewertungs-Texten wird die Überschrift im Bewertungstext gespeichert (#1001)
* Beitragsanzahl auf der News Seite (#1118)
* Warenkorbmatrix arbeitet fehlerhaft (#1078)
* Admin Boxenverwaltung: Footer wird deaktiviert, sobald man den Aktualisieren-Button drückt (#1128)
* Fehlerhafte URLs in Newsübersicht (#1159)
* Bestellbestätigung-E-Mail Lieferzeit fehlerhaft, wenn letzter verfügbarer Artikel gekauft wurde (#1097)
* Fehler beim Versenden von Newslettern wenn mindestens zwei Artikel hinzugefügt werden (#1014)
* Aufgabenplaner löscht bei Start vorhandene Datei (steht während des Exports nicht zum Download zur Verfügung) (#1190)

## [4.05.1] - 2017-02-17

### Bugfixes

* Weiterleitungslink kann bei Teilverschlüsselung nicht gespeichert werden, wenn Admin über https aufgerufen wird (#991)
* Weiterleitungsschleife bei SSL-Teilverschlüsselung z.B. bei Mein-Konto oder Kontakt (#990)
* Serverfehler 500, wenn Konfigurationsartikel im Warenkorb bearbeitet wird (#995)
* Mehrere im Backend-CMS hinterlegte Startseiten (Typ Spezialseite Startseite) erzeugen Dauerschleife (#987)


## [4.05] - 2017-02-14

Die nachfolgende Auflistung informiert Sie über die wichtigsten Änderungen in JTL-Shop Version 4.05. <br>
Eine vollständige Liste aller in dieser Version gelöster Issues finden Sie [hier](https://gitlab.jtl-software.de/jtlshop/shop4/issues?scope=all&utf8=%E2%9C%93&state=closed&milestone_title=4.05).

Änderungen innerhalb des PayPal-Plugins werden in einer separaten [CHANGELOG.md](https://gitlab.jtl-software.de/jtlshop/shop4/blob/release/4.05/includes/plugins/jtl_paypal/CHANGELOG.md) aufgeführt.    

### Neue Features
* SEO-URLs ersetzen warenkorb.php, bestellvorgang.php, bestellabschluss.php, pass.php, newsletter.php, wunschliste.php und jtl.php (#31)
* Unterstützung kundenindividueller Preise. Setzt Wawi v1.2 oder größer voraus (#344)
* Unterstützung mehrsprachiger Kategorieattribute (#191)
* Optionale 2-Faktor-Authentifizierung im Backend (#276)
* Banner und Slider und Box-Anzeige können nun konkreten CMS-Seiten zugewiesen werden (#107) 
* Weitere Felder für Backend-Benutzer (neues Plugin) (#21)
* Support für alt-Attribute für Mediendatei-Bilder per Attribut "img_alt" (#179)
* Eigene Datasources für Plugin-Optionen (#92)
* Optionale Einstellung für "Kunden kauften auch": Vaterartikel statt wie bisher Kindartikel anzeigen (#171) 
* Email-Vorlagen: Option "Muster-Widerrufsformular anhängen" implementieren (#422)
* sitemap.xml nach Export automatisch an Google und Bing übermitteln (einstellbar in Sitemap-Optionen) (#470)
* Optionaler vCard-Upload im Rechnungsadresse-Formular (#307)
* Datenbank-Management-Tool im Backend (admin/dbmanager.php)
* Child-Templates erlauben Überschreiben von Parent-Themes mit override-Attribut (#200)
* Neuer Hook HOOK_GET_NEWS in gibNews()
* Neuer Hook HOOK_STOCK_FILTER in gibLagerFilter()
* Neue Hooks HOOK_FILTER_INC_GIBARTIKELKEYS_SQL und HOOK_FILTER_INC_BAUFILTERSQL (#310)
* Neuer Hook HOOK_BOXEN_HOME bei Erstellung der Startseiten-Boxen (#371)
* Neuer Hook HOOK_QUICKSYNC_XML_BEARBEITEINSERT in dbeS/QuickSync_xml.php (#496)

### Weitere Verbesserungen und Änderungen
* Update: Smarty auf Version 3.1.30 aktualisiert
* Update: FontAwesome auf Version 4.6.3 und jQuery auf Version 1.12.4 aktualisiert (#593)
* Benutzerfreundliches Layout in Mein-Konto
* Kuponverwaltung hinsichtlich Massenerstellung überarbeitet, Validierung verbessert (#275, #277)
* Backend: Überarbeitetes Layout. Warnungen und Informationen werden nun bei Klick auf ein Benachrichtigungs-Icon im Header gezeigt (#38)
* Objektcache Speicheroptimierungen
* SQL-Performanceoptimierungen für Prüfung von Varkombi-Aufpreisen (#87)
* Exportformate Speicheroptimierungen (#327, #165)
* Checkboxverwaltung: Verfügbarkeitsanfragen und Frage zum Produkt unterstützen (#256)
* Bilder für Newskategorien können im Backend angegeben werden 
* Links mit Typ Spezialseite können dupliziert werden und in mehreren Linkgruppen gleichzeitig vorhanden sein (#159)
* Prioritäten für Plugin-Hooks (#45)
* Anzeige von Boxen in nicht-sichtbaren Positionen im Backend mitsamt Warnhinweis(#252)
* NiceDB::selectAll() zur Abfrage mehrere Spalten als Prepared Statement implementiert (#334)
* Artikeldetails Bildergalerie nun auch in XS-Ansicht blätterbar 
* Variationsauswahl bei aktiver Warenkorbmatrix am Vaterartikel ausblenden
* Evo Druckansicht verbessert (#319)
* Bestellabschluss: Lieferzeitangabe nach oben in die Versandart-Box verschieben
* Smooth Scrolling bei relativen Ankerlinks (#147)
* Einstellungen zur Anzeige der Artikel-Kurzbeschreibungen in Listen und Artikeldetails (#479)
* HOOK_INDEX_SEO_404 wird nun auch ausgeführt, wenn cSeo nicht leer ist
* HOOK_WARENKORB_PAGE_KUPONANNEHMEN_PLAUSI wird wieder früher ausgeführt und enthält Parameter  (#234)

### Bugfixes
* Bearbeiten von Herstellern invalidiert Objektcache für Artikel nicht
* Newsbeiträge aus deaktivierten Kategorien werden auf der Startseite angezeigt
* Globale Variable $AktuelleSeite zeigt bei Newsdetails falschen Typ an
* Newskategorien-Box verschwindet bei aktiviertem Objektcache
* URL_SHOP in Exportformaten nicht definiert
* Plugin-Sprachvariablen lassen sich nicht aktualisieren, wenn zwischenzeitlich neue Sprache im Webshop aktiviert wurde
* Speichern von Newsbeiträgen in deaktivierter Newskategorie schlägt fehl bzw. erzeugt Duplikat des Beitrags
* Varkombis können in Fremdsprachen bei fehlender Übersetzung Sprachvariablen nicht nachgeladen werden
* TopArtikel werden bei aktiviertem Objektcache nicht aktualisiert 
* Ändern von Bildeinstellungen invalidiert Objektcache nicht 
* Varkombi Dropdown-Auswahl wird bei Auswahl Nachladen der Kombination zurückgesetzt
* Fehlerhafte Kategorie-/Merkmalfilter-URLs erzeugen keinen 404-Statuscode
* Bei Klick auf Sortierung in Freischaltzentrale unter Livesuche erfolgt Weiterleitung zum Reiter "Bewertungen" (#100)
* Aufgabenplaner: Bei Klick auf "Zuletzt fertiggestellt" verschwinden Buttons (#98)
* Mailversand erfolgt immer in zum Registrierungszeitpunkt eingestellter Sprache (#63)
* "<tab>" wird aus Exportformaten gelöscht, wenn beim Speichern Angaben fehlen (#136)
* Kupon::generateCode() erzeugt Endlosrekursion
* Internal Server Error wenn mod_deflate nicht aktiviert ist (#235)
* Abnahmeintervall wird bei Konfigurationsartikel nicht beachtet, Hinweis auf Mindestbestellmenge/Abnahmeintervall fehlt (#259)
* apple-touch-icon.png in header.tpl verlinket, obwohl Datei nicht vorhanden ist (#278)
* Fehlerhafte URLs in $Suchergebnisse->Kategorieauswahl bei hierarchischer Filterung (#273)
* Mehrdeutige Verwendung von GET-Parameter "n" (#321)
* Weiterleitung zum Login bei Artikel mit Slash in SEO-URL fehlerhaft (#322)
* Fehlerhafter Kategoriefilter, wenn Einstellung 1321 auf "Hierarchische Filterung" gestellt ist (#185)
* Prozent-Kupon-Preis in Standardwährung ändert sich abhängig von der Währung beim Einlösen (#366)
* Bestellkommentar wird nicht zur Wawi übertragen, wenn "Zahlung vor Bestellabschluss" auf "Ja" steht (#356)
* Im Wartungsmodus, wird man in kürzester Zeit nach dem Einloggen im Frontend wieder ausgeloggt (#314)
* Zahlungsart > Einstellung maximaler Bestellwert ohne Funktion (#346)
* Exportformate: Zeichenmaskierung fehlerhaft (#481)
* Neukundenkupon verfällt immer nach der Erstanmeldung (#215, #407)
* Quicksync invalidiert Objektcache nicht korrekt, wenn Varkombi-Preis geändert wird (#447)
* Preisalarmbox Darstellungsfehler (#451)
* Bewertungs-Pagination springt nicht in den korrekten Tab und nutzt die SEO-URL des Artikels nicht (#472)
* Box Bestseller Startseite: Es werden alle gekauften Produkte angezeigt (#199)
* Backend: Weiterleitungen Sortierung nach Aufrufen funktioniert nicht (#368)
* Konfiguratorgruppenkomponentenbeschreibung wird nicht angezeigt (#391)
* Varkombis ohne Variationswert Übersetzung sind nicht in den Warenkorb legbar (#389)
* Mehrere Tracking IDs in Versandbestätigungs-E-Mail darstellen (#389)
* Sonderpreise können durch Wawi-Abgleich gelöscht werden (#305)
* Anrede in "Mein Konto" nicht an Sprache angepasst (#514)
* In Zukunft startende Sonderpreise werden bei aktiviertem Objektcache nicht berücksichtigt (#123)
* Versandkostenfreigrenze ignoriert Steuerberechnung-Einstellung (#231)

## [4.04.1] - 2016-07-19
* Neues Premium-Plugin: Login und Bezahlen mit Amazon (von Solution360)
* Neues Premium-Plugin: TrustedShops Trustbade (von AG-Websolutions)
* Update: Google Shopping Plugin v1.05 (Bugfix: Unter Umständen doppelte IDs bei Varkombi-Kindartikeln)
* Bugfix: Testmails werden nur noch auf Deutsch versendet (#241)
* Verbesserung: Vermeiden mehrfacher Cache-Einträge mit demselben Inhalt in gibKategorieFilterOptionen() (#244)
* Bugfix: Mixed-Content-Warnungen (Megamenü-Kategoriebilder via http) bei Teilverschlüsselung und Wechsel auf https (#211)
* Bugfix: Frontendlinks verschwinden aus tseo bei Plugin-Updates in mehrsprachiger Umgebung (#258)
* Bugfix: jtl_token wird sporadisch in der Session überschrieben (#306)
* Bugfix: Boxenverwaltung: Footer für alle Seiten aktivieren geht nicht
* Bugfix: Produktbilder-Encoding bei Dateityp "PNG" fehlerhaft

## [4.04] - 2016-06-22
* Bugfix: robots.txt fehlendes "Sitemap: " vor der Sitemap-URL (#83)
* Bugfix: Billpay Zahlungseingang wird nicht gesetzt (#96)
* Bugfix: Newsletter Abmelden unsichtbar für nicht-angemeldete Besucher (#77)
* Verbesserung: Alte Bildpfade müssen bei Änderung von /bilder/ auf /media/ via 301 weitergeleitet werden (#189)
* Bugfix: Kundenimport Fehler bei unbekannten Spalten (#214)

## [4.03.1] - 2016-05-17
* Bugfix: Sprachwechsel in einigen Linkgruppen unvollständig
* Bugfix: HTTP 500, wenn Object-Cache aktiv ist und Preise erst nach Login sichtbar
* Bugfix: DB-Update läuft in Endlosschleife, wenn das Update ohne Umweg über admin/index.php direkt im Backend angestoßen wird
* Bugfix: reCaptcha-Validierung schlägt bei eingeloggten Kunden fehl
* Bugfix: Konfigurator Initialisierung dauert bei größeren Konfi-Artikeln sehr lange
* Bugfix: Banner werden nicht dargestellt, wenn Aktiv-Von/Bis-Datum fehlt
* Bugfix: Ändern von Kundengruppen-Rabatten invalidiert Objektcache für Artikel und Kategorien nicht
* Bugfix: Thumbnail-Cache-Ordner media/images/product wurde u.U. geleert, obwohl nicht nötig
* Bugfix: Leere Kategorien werden trotz gesetzer Einstellung nicht immer ausgeblendet
* Bugfix: Fehlerhafte Sortierung von Kategorien
* Bugfix: PayPal Basic Transaction ID wird nicht gesetzt
* Bugfix: Artikeldetails "weiter einkaufen" führt zur Startseite
* Bugfix: Fehlerhaftes Routing: /gibtEsNicht/index.php liefert 200 OK statt 404
* Unterstützung: Konfiguratorkomponenten Bildwechsel nutzt Gruppenbild, wenn Komponente kein Bild hat
* Bugfix: Angepasste robots.txt wird falsch sortiert durch robots.php

## [4.03] - 2016-05-09

Dieses Update enthält folgende Verbesserungen und Bugfixes: 

* Bugfix: PayPal PLUS (jtl_paypal 1.04): success_url und cancel_url enthalten html-maskierte &-Zeichen in der URL. Führt bei bestimmten Servereinstellungen zu Fehlern bei Rückleitung zum Shop
* Bugfix: PayPal PLUS (jtl_paypal 1.04): Bei einem gemischten Warenkorb mit verschiedenen Versandklassen wird die Payment Wall nicht geladen
* Bugfix: PayPal PLUS (jtl_paypal 1.04): Shopeigene Zahlungsarten Lastschrift und Kreditkarte werden bei den weiteren PLUS-Zahlungsarten nicht angeboten 
* Verbesserung: PayPal PLUS (jtl_paypal 1.04): Verbesserte interne Prozessbehandlung für Zahlungsarten, die weitere Interaktion mit Kunden voraussetzen (Zahlungszusatzschritt)  
* Verbesserung: PayPal PLUS (jtl_paypal 1.04): Unterstützung für Loading-Indicator eingebaut (Lade-Grafik, während PayPal PLUS Wall lädt)
* Bugfix: PayPal Express (jtl_paypal 1.04): Invoice-ID wird nicht übermittelt (bestellung.cBestellNr)
* Bugfix: PayPal Express (jtl_paypal 1.04): Sporadische Zahlungseingänge ohne tatsächliche PayPal-Zahlung. Zahlung darf nicht gesetzt werden, wenn Paymentstatus != COMPLETED (Sonderfall eCheck, wenn keine Zahlmethode im PayPal-Konto vorhanden ist)
* Verbesserung: PayPal Basic (jtl_paypal 1.04): Weiterleitung zu PayPal erfolgt jetzt erst mit dem Klick auf "Zahlungspflichtig bestellen" (Einstellbar in der Zahlungsart über die Option "Zahlung vor Bestellabschluss") 
* Bugfix: PayPal (jtl_paypal 1.04): State-Parameter für USA, CA, NL und IT werden nicht als ISO-Code an PayPal übermittelt
* Bugfix: PayPal (jtl_paypal 1.04): Negative Variationsaufpreise werden nicht unterstützt
* Bugfix: Billpay: Rechnungsaktivierung aus JTL-Wawi schlägt fehl. Umlaute werden als HTML-Entities dargestellt.
* Bugfix: Anführungszeichen in Plugin-Optionen werden nicht escaped
* Bugfix: Fehlerhafte Plugins (Returncode 90 - doppelte Plugin-ID) tauchen nicht in Liste fehlerhafter Plugins auf
* Bugfix: Variationsaufpreise Live-Berechnung liefert manchmal 1 EUR Gesamtpreis zurück, obwohl ein anderer Preis berechnet werden müsste 
* Bugfix: ReCaptcha wird im Bestellschritt "Registrieren" angezeigt, obwohl Backend-Einstellung "Spamschutz-Methode" auf "Keine" gesetzt ist
* Bugfix: Sprachwechsel-Problem beim Ajax-Nachladen von Varkombis, wenn Variationswerte den gleichen Namen auf englisch und deutsch haben
* Bugfix: Warengruppen werden über Webshopabgleich nicht aktualisiert (Globals-Abgleich)
* Bugfix: Schnellkauf funktioniert unter Umständen nicht
* Bugfix: HOOK_BESTELLUNGEN_XML_BEARBEITESTORNO enthält leere Kunden- und Bestellungsobjekte, wenn über Zahlungsplugin gezahlt wurde
* Bugfix: Duplicate Key bei Einfügen von Lieferscheinpositionen in DB
* Bugfix: Bei Wawi-Kundenänderung darf sich die Login-E-Mail von registrierten Kunden nicht ändern
* Bugfix: Boxen werden nie angezeigt, wenn Filter auf "Eigene Seiten" aktiv ist
* Bugfix: Sitemap-Einträge werden mit SSL-URLs erstellt, wenn automatischer Wechsel zwischen http und https aktiv ist und Export manuell gestart wird
* Bugfix: Änderungen von JS-/CSS-Dateien werden bei Plugin-Update nicht übernommen
* Bugfix: Lokalisierte Plugin-Daten werden bei Sprachwechsel nicht aktualisiert
* Bugfix: Plugin-Boxen bleiben im Frontend aktiv, nachdem Plugin deaktiviert wurde
* Bugfix: Yatego-Export erzeugt Serverfehler
* Bugfix: Statistiken zeigen in bestimmten Zeitabschnitten keine Daten an
* Bugfix: Freischalten von Tags invalidiert Objektcache nicht
* Bugfix: Änderung an Bildoverlays invalidiert Objektcache nicht
* Bugfix: Newsletterempfänger-Import führt zu Serverfehler, wenn CSV-Zeile mit ";" endet
* Bugfix: Spamschutz "Sicherheitscode" funktioniert in alten Tiny-Templates nicht mehr
* Bugfix: Option "Position der Vergleichsliste" ohne Funktion
* Bugfix: Fatal error, wenn Template als "mobil" aktiviert wurde und kein weiteres Desktop-Template vorhanden ist
* Bugfix: Funktionsattribute mit Umlauten verhindern Kauf von Artikeln in Kategorieansicht
* Bugfix: Bei aktiven Sonderpreisen wird bei Vaterartikeln Preis "ab" angezeigt, auch wenn alle Kindartikel den gleichen Preis haben
* Bugfix: Bei vorhandenem Kundengruppen- und Kundenrabatt wird u.U. der niedrigere Rabatt genutzt
* Bugfix: Keine Meta-Keywords in Artikeldetails vorhanden, wenn nicht explizit gesetzt
* Bugfix: U.U. falscher Lagerbestand bei Stücklistenartikeln mit Überverkäufen
* Bugfix: Versandkostenfreigrenze beachtet keine Rabattkupons
* Bugfix: Kupons, die nicht den gesamten Warenkorb rabattieren, erscheinen nicht in Statistik
* Bugfix: 404-Fehler bei Aufruf nicht vollständig lokalisierter Kategorien
* Bugfix: Artikel, die mit Lagerbestand arbeiten und Überverkäufe ermöglichen, werden als "Produkt vergriffen" angezeigt
* Bugfix: Modale Popups im Shop zeigen teilweise "Attention" im Titel an
* Bugfix: Falsches Encoding von Fehlermeldungen bei Wawi-Abgleich
* Bugfix: Links zu Unterkategorien in Kategorieansicht sind nicht lokalisiert
* Bugfix: Ändern von Optionen/Erstellung von bestimmten Inhalten invalidiert nicht alle nötigen Objektcaches
* Bugfix: Wawi-Abgleich invalidiert nicht alle nötigen Objektcaches
* Bugfix: Slider-Slides lassen sich nicht korrekt sortieren
* Bugfix: Auf der Gratisgeschenke-Seite fehlt die Kundengruppensichtbarkeitsprüfung und Prüfung auf Artikelanzeigefilter
* Bugfix: Lagerbestand von Gratisgeschenken wird nicht aktualisiert
* Bugfix: Wunschzettel übernimmt Artikelanzahl nicht
* Bugfix: Staffelpreise werden falsch berechnet, wenn Sonderpreis aktiv ist
* Bugfix: Passwort-Zurücksetzen-Funktion schlägt u.U. fehl, wenn mehrere Kunden mit derselben Email-Adresse existieren
* Bugfix: Hersteller-Links sind nach Sprachwechsel fehlerhaft
* Bugfix: NiceDB::updateRow() gibt im Erfolgsfall stets 1 statt row count zurück
* Bugfix: Newsbeiträge ignorieren Kundengruppensichtbarkeit bei Direktaufruf
* Bugfix: Auf Gratisgeschenke-Seite fehlt Kundengruppensichtbarkeitsprüfung und Prüfung auf Artikelanzeigefilter
* Bugfix: Admin-Logins mit mehr als 20 Zeichen werden gekürzt in DB gespeichert
* Bugfix: Antwortmöglichkeiten im Umfragesystem werden tlw. nicht gespeichert
* Bugfix: Shop-Zurücksetzen-Funktion löscht keine Newsbilder
* Bugfix: Mögliche Kollision unterschiedlicher Funktionen mit Name baueFormularVorgaben
* Verbesserung: Funktion Upload-Löschen für Templates implementiert
* Verbesserung: Backend-Bilder-Einstellung "Containergröße verwenden" wird wieder beachtet (Hintergrundfarbe nur bei JPG, PNG behält transparenten Hintergrund)
* Verbesserung: Profiler unterstützt zusätzlich zu XHProf auch Tideways
* Verbesserung: Möglichkeit, den Template-Cache ohne Plugin direkt im Backend unter "Cache" zu löschen
* Verbesserung: neuer Hook HOOK_KUNDE_DB_INSERT (215), bevor neuer Kunde in DB gespeichert wird
* Verbesserung: Speicheroptimierungen bei Sitemapexport
* Verbesserung: Summenerhaltendes Runden für Warenkorbpositionen (gilt nur für die Einzelpreisanzeige im Warenkorb, Checkout und Bestellbestätigung. Keine Änderung an der Gesamtpreisberechnung oder an Preisen für Wawi-Sync)
* Verbesserung: FlushTags-Attribut in Plugin-XML für automatisches Löschen von Cache-Tags bei Plugin-Installation
* Verbesserung: DokuURL-Attribut in Plugin-XML für Definition eigener Dokumentations-URLs
* Verbesserung: Übersichtlicheres Anlegen von Banner-Zonen
* Verbesserung: E-Mailvorlage Bestellbestätigung: {$Position->cArtNr} ersetzt durch {$Position->Artikel->cArtNr}
* Verbesserung: Einheitliche und erweiterte Filtermöglichkeiten für Banner, Boxen und Slider
* Verbesserung: Performance-Optimierungen bei umfangreichen Kategoriestrukturen und Nutzung des Megamenüs bzw. der Kategoriebox
* Verbesserung: Option für UTF-8 ohne BOM bei Exporten
* Verbesserung: reCaptcha wird bei erfolgreicher Eingabe zukünftig nicht erneut angezeigt
* Verbesserung: Hartkodierte Admin-Pfade entfernt
* Verbesserung: Diverse Konstanten einfacher überschreibbar gemacht
* Verbesserung: .htaccess-Regeln optimiert. robots.txt wird nun dynamisch um die Sitemap-URL ergänzt. Änderungen als Diff: http://jtl-url.de/diffhtaccess402403
* Diverses: Artikel-Weiterempfehlen-Funktion entfernt 
* Diverses: Zahlungsarten Click & Buy und veraltete Saferpay-Integration entfernt. Saferpay empfiehlt https://www.jtl-software.de/Marktplatz/Customweb-Saferpay-292 als Plugin-Lösung für JTL-Shop.  
* Diverses: NiceDB::update() und NiceDB::delete() geben im Fehlerfall nun -1 statt 0 zurück

## [4.02] - 2015-12-18
* Shop-Zahlungsmodul: veraltetes Commerz Finanz-Modul entfernt. Bitte alternativ das von Commerz Finanz empfohlene Plugin für JTL-Shop nutzen.
* Shop-Zahlungsmodul: Komplette Überarbeitung des Billpay-Moduls und Aufteilung in 4 verschiedene Zahlungsarten. WICHTIG: Bitte passen Sie die entsprechenden E-Mail-Vorlagen zu Ihren Billpay-Zahlungsarten bitte an.
* Umbenennung: templates/Evo-Child in templates/Evo-Child-Example umbenannt. Das alte Verzeichnis templates/Evo-Child kann gelöscht werden. 
* Shop-Backend: Newsvorschaubild wird bei erneutem Speichern des Beitrags gelöscht
* Shop-Backend: Kampagnenwert nicht sofort auswählbar und fehlende Bootstrap-Klasse
* Shop-Backend: Newsletter-Smarty-Code wird durch CKEditor zerstört
* Shop-Backend: Newsletter-Smarty-Code wird nicht korrekt geprüft
* Shop-Backend: Linkgruppen mit bestimmten Sonderzeichen sind nicht aufklappbar
* Shop-Backend: Aktionsbuttons bei Emailvorlagen von Plugins wirkungslos
* Shop-Backend: Eingabefelder für Bildgrößen zu klein
* Shop-Backend: Buttonbeschriftung in Bestellungsübersicht falsch
* Shop-Backend: Ändern von Einstellungen invalidiert Objekt-Cache nicht mehr
* Shop-Backend: Mapping von erfolglosen Suchbegriffen nicht mehr möglich
* Shop-Backend: Postleitzahlen in Versandzuschlägen können nur numerisch sein
* Shop-Backend: automatische Generierung der SEO-URLs von CMS-Seiten u.U. fehlerhaft
* Shop-Backend: Freischaltung von Bewertungen aktualisiert u.U. den Bewertungsdurchschnitt von falschen Artikeln und invalidiert den Objektcache nicht
* Shop-Backend: Favicon-Uploader prüft keine Schreibrechte und erwartet Datei immer als favicon.ico
* Shop-Backend: OpCache-Statistik hinzugefügt
* Shop-Backend: Speichern/Anzeigen von PDF-Anhängen in Email-Vorlagen fehlerhaft
* Bibliotheken: PHPMailer auf 5.2.14 aktualisiert
* Shop: Versandart-Staffeln wurden nicht beachtet
* Shop: Deinstallation von Plugins invalidiert wichtige Objektcaches nicht
* Shop: Performance-Optimierung für XSell-Artikel
* Shop: Performance-Optimierung für Kategorielisten
* Shop: Falsche Boxensprache bei Sprachwechsel und aktiviertem Objektcache 
* Shop: Max. Bestellmenge bei Änderungen im Warenkorb wird falsch berechnet
* Shop: Kupons werden doppelt berechnet
* Shop: ChildTemplates ohne eigene Einstellungen sind nicht konfigurierbar
* Shop: Unterkategorien wurden alle als Aktiv gekennzeichnet, wenn Hauptkategorie aktiv war
* Shop: Boxen in Containern werden nicht korrekt dargestellt
* Shop: Hinzufügen/Entfernen von Bildern invalidiert Objekt-Cache nicht
* Shop: Konflikt zwischen Plugin-Boxen und Plugin-Zahlungsarten
* Shop: Probleme mit sehr langen Zeilen in Mails bei manchen MTAs
* Shop: Newsletter-Registrierung auch ohne reCaptcha möglich
* Shop: Fehlerhafte Versandkostenberechnung bei Exporten
* Shop: Kategorien werden bei den Exportformaten nicht exportiert bei weiteren Sprachen
* Shop: Suchweiterleitung bei nur einem Treffer funktioniert nicht
* Shop: Mindestbestellwert von Kupons kann umgangen werden
* Shop: Zuletzt angesehene Artikel werden nur gespeichert, wenn die entsprechende Box auf Produktdetailseiten aktiv ist
* Shop: Breadcrumb-Eintrag bei Vergleichsliste fehlt
* Shop-Update: Sicherstellen, dass Bildeinstellungen für Mini-Produktbilder einen Wert größer 0 gesetzt haben.
* Evo-Editor/LiveStyler: CSS wird bei aktiviertem LiveStyler für Besucher nicht geladen, wenn Minifizierung deaktiviert ist
* JTL-Search: Session geht bei Klick auf Suchvorschlag verloren
* PayPal-Plugin 1.02: Bugfixes: 
    * Kupon wird jetzt nicht mehr als LineItem übertragen sondern (kürzlich erst von PayPal implementiert) als Sonderposition
    * Umlaute bei Zahlungsarten in der Payment-Wall verursachte nicht sichtbaren Fehler, es erfolgte keine Weiterleitung zu PayPal
    * PayPal Lib Update
* PayPal-Plugin 1.03: 
    * Unterstützung von Kauf auf Rechnung in PayPal PLUS! 
    * PayPal Basic ist wieder im Plugin integriert und bildet PayPal Basis-Zahlungen über die API von PayPal ab. 
    * Rundungs-Fix fuer nicht-ganzzahlige Bestellmengen
* Shop: tartikelabnahme.fIntervall in double geaendert (behebt Rundungsfehler bei kundengruppenspezifischen Abnahmeintervallen)
* Shop: Artikel-SEO-URLs, die aus Artikelnamen generiert werden: Querstriche im Namen durch Bindestriche ersetzen (gleiches Verhalten wie im Shop 3)
* Shop: Artikelattributnamen dürfen nun max. 255 Zeichen lang sein (zuvor max 45 Zeichen)
* Canonical auf jeder Seite implementiert, auch wenn es eine Referenz auf die eigene Seite ist - Begruendung siehe https://yoast.com/rel-canonical/
* Geänderte E-Mail-Vorlagen: 
** Bestellung aktualisiert (Satz komplett entfernt: "Die Bestellung wird direkt nach Zahlungseingang versandt.")
** Bestellbestaetigung (Billpay-Anpassungen)



## [4.01] - 2015-10-29
* Shop: Lizenzprüfung vor Update kann u.U. fehlschlagen
* Shop: PHP7-Kompatibilität in NiceDB verbessert
* Shop: pdo bei Installation immer prüfen
* Shop: Umlautprobleme bei CLI-Installer
* Shop: falsche Parameter in HOOK_LETZTERINCLUDE_CSS_JS
* Shop: falsche Smarty-Initialisierung für Seitencache
* Shop: Varkombi-Tausch in auf Tiny basierenden Templates nicht mehr möglich
* Shop: Performance-Verbesserung bei häufigem Ausführen von Hooks
* Shop: Leere Sprachvariablen werden wie nicht-existierende behandelt
* Shop: Bei Lieferadresse=Rechnungsadresse wird stets neue Lieferadresse erstellt
* Shop: Produktanfrage bemängelt fehlende Eingaben
* Shop: Preisgrafik fehlerhaft bei Ziffer 1
* Shop: Zahlung mit Sofortüberweisung nicht möglich
* Shop: Template-Pfade für Plugin-Boxen fehlerhaft
* JTL Search: Access Control-Fehler bei nicht-permanentem SSL
* Shop-Backend: Darstellung von Bildern im CMS-Editor fehlerhaft
* Shop-Backend: Fehlende Grafiken
* Shop-Backend: Umlautfehler in Beschreibungen
* Shop-Backend: SQL-Fehler beim Speichern von Slidern/Slides und Neukundenkupons
* Shop-Backend: SQL-Fehler beim Installieren von Plugins mit Exportformaten
* Shop-Backend: Banner mit Umlauten im Namen erlauben kein Hinzufügen/Bearbeiten von Zonen
* Shop-Backend: ionCube-verschlüsselte Plugins erzeugen unnötige Ausgabe, wenn Extension nicht geladen ist
* Mails: Widerrufsbelehrung ist leer
* ChildTemplate: Theme-Support für ChildTemplates hinzugefügt
* Evo-Editor: ChildTheme-Support hinzugefügt
* Shop: PayPal Plugin Version 1.01: Behoben in dieser Version: PayPal Express taucht als normale Zahlungsart im Checkout auf. Bugfix fuer Zeilenumbrueche in Zahlungsartnamen/Beschreibungen.  
* Shop: PayPal-Standard-Zahlungsart aus JTL-Shop3 reaktiviert. 

## [4.00] - 2015-10-14
### [4.00 Open Beta] - 2015-10-12  Verbesserungen und Bugfixes innerhalb der Open Beta
* Shop: Zahlungsmodul PayPal entfernt. (Als Plugin-Lösung verfügbar)
* Shop: neue PHPMailer Version 5.2.13
* Shop: PHP7-Kompatibilität verbessert
* Shop: Verbesserungen im Zusammenhang mit SQL Strict Mode

### [4.00 Open Beta] - 2015-10-07  Verbesserungen und Bugfixes innerhalb der Open Beta

* Shop: Diverse Fehlermeldungen in dbeS behoben
* Shop: Füge Plugin-Links standardmäßig in Linkgruppe "hidden" ein
* Shop: Explizite Angabe der gewünschten Linkgruppe für Plugins möglich (vgl. Beispiel-Plugin)
* Shop: Darstellung von News-Kommentaren wenn Freischaltung deaktiviert ist
* Shop: Sortierung von Newsbeiträgen die in derselben Minute erstellt wurden korrigiert
* Shop: Hook 99 vor Erstellung der Boxen ausführen, um Modifikationen zu erlauben
* Admin: diverse Verbesserungen in der Sliderverwaltung
* Admin: Aktualisierte Wiki-Links
* Admin: Markup-Fixes in Boxenverwaltung
* Admin: Darstellung langer Log-Einträge verbessert
* Admin: Umlautfehler korrigiert
* Admin: Icons hinzugefügt
* Admin: Falsche Optionen bei Zahlungsart-Plugins mit mehreren Methoden entfernt
* Wawi-Sync Globals: Tabelle twarenlager vor dem Einfuegen neuer Warenlager komplett leeren
* Evo-Editor: Backup von less-Dateien, Zurücksetzen auf Standard behoben
* JTL-Search: Diverse Shop4-Anpassungen
* Bilderschnittstelle: GIF Support, transparentes Watermark (GDLib)

### [4.00 Open Beta] - 2015-10-02 Verbesserungen und Bugfixes innerhalb der Open Beta

* Bugfix: Sonderpreisanzeige in Listen und Boxen fehlerhaft, wenn Endddatum für Sonderpreis auf den aktuellen Tag fällt 
* Objektcache-Methode "mysql" entfernt
* Socket-Support für Installer (behebt Probleme bei Installation auf 1und1-Servern)
* neue Minify-Version
* Billpay Zahlungsabgleich-Informationen: Kontonr und BLZ durch IBAN und BIC ersetzt

### [4.00 Open Beta] - 2015-09-30 - Start der Open Beta (Community Free)

Alle Highlights zum Shoprelease Version 4.00: https://www.jtl-software.de/Onlineshop-Software-JTL-Shop
