# JTL-Shop Evo-Template Changelog

## [4.03]
* Verbesserung: Über 100 neue Smarty-Blocks zum Erweitern über Child-Templates ergänzt
* Verbesserung: Kleinere Verbesserungen an der klassischen Warenkorbmatrix. Neue, optionale Matrix-Einstellung "Liste"
* Bugfix: Megamenü verdeckt in allen Themes außer Evo bei min-width 768px die Megamenü-Navbar
* Bugfix: Einstellungen Artikeldetails "Stücklistenkomponenten anzeigen" und "Produktbundle-Empfehlungen nutzen" werden nicht beachtet
* Bugfix: Mediendatei-Tabs können bei Leerzeichen im Tabname nicht gewählt werden
* Bugfix: Slick-Slider-Buttons werden im Theme "bootstrap" nicht korrekt dargestellt
* Bugfix: Im Warenkorb und in Bestellpositionen fehlen die eindeutige Artikelmerkmale (Buttonloesung) 
* Entfernt: Artikel-Weiterempfehlen-Funktion wurde komplett aus dem Evo-Template entfernt, da nicht rechtssicher
* Bugfix: Banner-Popover u.U. ungünstig am Rand positioniert
* Bugfix: Änderung von Artikelanzahl in Wunschliste nicht möglich
* Bugfix: Bildauswahl springt nach Nutzung des Bilderzooms zurück auf das erste Bild
* Bugfix: Einheit für Gewichtsangaben im Template nicht dynamisch
* Bugfix: Warenkorb-Button wird in der Konfigurationsbox angezeigt, obwohl kein Lagerbestand vorhanden ist
* Bugfix: Preisgrafikanzeige in Produktlistings fehlerhaft
* Entfernt: Ungenutzte JS-Bibliothek imgView entfernt
* Diverses: Abweichende Lieferadresse im Checkout - Vorbelegung mit Rechnungsadresse entfernen und stattdessen Sessiondaten nutzen, sofern abweichende Lieferadressdaten bereits übermittelt wurden. 
* Diverses: Boxen aus der Boxenverwaltung erhalten wieder eindeutige IDs im Frontend am jew. section-Element
* Diverses: Templateeinstellung "Statischer Header" umbenannt in "Mitlaufendes Header-Megamenü". Megamenü-Navigation bleibt außerdem nun unabhängig von dieser Einstellung im Container (nicht mehr 100% Bildschirmbreite)

## [4.02]
* Varkombi-Auswahl via AJAX komplett refaktorisiert, inkl. URL-History-Änderung, inkl. sofortiger ausverkauft/nicht-möglich-Anzeige
* Einfache Variationen: Aufpreise / Rabatte werden via AJAX-Aufruf berechnet und aktualisieren den Artikelpreis. 
* Neue Evo-Template-Einstellung: Full-Width Slider. Zeigt Slider zwischen Header und Content auf voller Fensterbreite an
* Produkt-Slider durch Slick Slider (http://kenwheeler.github.io/slick/) ersetzt
* Zur-Kasse-Button in Mini-Warenkorb und "Produkt hinzugefügt"-Popup entfernt, weil dort anfallende Versandkosten noch nicht ausgewiesen werden können
* Preisverlauf ohne Flash realisiert
* Bugfix: Konfigurator berechnet Gesamtpreis nicht korrekt bei Anzeigetyp = Mehrfachauswahl
* Bugfix: Konfiguratorbox Breite ändert sich bei Änderung der Fensterbreite (Breakpoints) 
* Bugfix: Bei Varkombikonfiartikeln wird keine Konfiguration in den Artikeldetails angezeigt(Konfigurator ist leer)
* Konfigurator-Validierung verbessert, Berücksichtigung der min/max-Limits
* Konfi Multiselectbox ohne Funktion im Evo Template 
* Box Kategorien: Oberkategorien wurden beim Aufklappen je nach Einstellung nochmal aufgeführt
* Box Kategorien: Aktiv-Markierung muss bei Filterung oder in Artikeldetails erhalten bleiben
* "active"-Classes im Megamenü für Subkategorien ergänzt
* Megamenü CMS-Seiten: Nur Templatename 'megamenu' zur Identifikation der Megamenu-CMS-Seiten heranziehen
* Megamenü Bugfix: Beim scrollenden Header ist die Verlinkung des Shoplogos nicht klickbar
* Bugfix: Lieferstatus wurde nicht angezeigt (war templateseitig auskommentiert)
* Bugfix: Z-Index bei Slidercontrols fehlerhaft
* Link zur Sendungsverfolgung war teilweise schlecht sichtbar
* UVP wird nicht mehr durchgestrichen dargestellt
* Offcanvas-Navigation: Schließen-Button wurde in manchen Themes als Viereck-Symbol dargestellt
* Bugfix: Einkauf ueber Warenkorbmatrix scheitert an required Attributen an Variationswahl
* Statischer Header aktualisiert Warensumme nun sofort per Ajax bei Artikelkauf in Kategorieansicht
* Zusätzlichen Löschen-Button am Ende von Warenkorbpositionen eingefügt
* Slider Captions überarbeitet: Anzeige von Titel + Text möglich
* Footer-Boxes werden bei xs-Ansicht auf 50% Breite angezeigt
* Hersteller fehlen in Offcanvas-Menü
* Bugfix: OS X / Firebug: Fullscreen-Image-Slider lädt zweites Bild nicht auf Anhieb
* Schwarzen Hintergrund beim Laden von Artikelbildern in Zoom-Ansicht entfernt
* Social-Icon-Grafiken im Footer durch FontAwesome-Icons ersetzt
* Mikrodaten für itemprop="price" in Produktdetails validiert
* Bugfix: Manche Filter-Links setzen gesamte Merkmalauswahl zurück
* Bugfix: Fehlende Markierung für Pflichtfelder im Kontaktformular
* Meta content charset wieder identisch zum HTTP Header Content-Type charset (iso-8859-1)
* www-Eingabefeld in Registrierung ist wieder ein einfaches Textfeld (ohne URL-Validierung mit http...)
* Bugfix: Nicht verfügbare Varkombis sind anwählbar nach einer Auswahl von einer verfügbaren Varkombi
* In der Box "Globale Merkmale" entsteht ein Zeilenumbruch nach dem Bild
* Bugfix: Javascript von Recaptcha wird doppelt geladen 
* Bestseller-Gruppierung in Listenansicht: Bestseller werden nun im Slick-Slider angezeigt
* Box Top-Angebote / Neu-im-Sortiment usw: "Zeige alle..."-Link ergaenzt

## [4.01]

* Konfigurator überdeckt einzelne Boxen
* Hersteller-Bilder werden trotz anderslautender Option 188 stets angezeigt
* Breite der JTL-Search-Ergebnisse zu groß
* Funktion get_cms_content existiert nicht mehr
* Megamenü in manchen Themes bei aktiviertem statischen Header nicht lesbar
* Ähnliche Artikel übernehmen Preis von Konfigurator-Komponente
* Lieferscheine können im Kundenkonto nicht angezeigt werden
* Templateeinstellung "Hauptkategorie Infobereich" ohne Funktion
* Hausnummer fehlt in Lieferadresse im Bestellvorgang
* Versandkostenseite öffnet sich in neuem Fenster statt Popup
* Sprung zum Bewertungstab funktioniert nicht
* Auswahl ausverkaufter Komponenten im Konfigurator erzeugt wenig informative Fehlermeldung und setzt Auswahl zurück
* AGB/WRB-Links im Bestellabschluss werden nicht als modale Popups geöffnet
* Slider zeigen permanent Lade-Icon bei transparenten PNG
* Sliderdarstellung abhängig von Boxed Layout
* Performanceprobleme bei vielen Kategorien im Megamenü oder in Kategoriebox
* Artikelmerkmale werden nicht angezeigt, wenn Beschreibung leer ist und keine weiteren Tabs vorhanden sind
* Neue Templateeinstellung: Warenkorb-Mengen-Optionen in Dropdown anzeigen?
* reCaptcha kann in modalen Popups nicht gelöst werden
* Nicht-quadratische Bilder werden fehlerhaft angezeigt

## [4.00]

### 10.10.2015 Verbesserungen und Bugfixes innerhalb der Open Beta

* MegaMenu: Weitere Einstellungen zur Individualisierung hinzugefügt
* Artikel-in-den-Warenkorb-gelegt Alert: Schließen-Button und "Weiter einkaufen" Link ergaenzt
* Bestellabschluss: Hinweis zum Korrigieren von Angaben eingefügt (SQL-Updates beachten!)
* Formular-Pflichtfelder werden mit einer Hintergrundgrafik (themes/base/images/asterisk.png) markiert
* Custom-Hintergrundbilder können in das Theme gelegt und in den Template-Einstellungen aktiviert werden

### 07.10.2015  Verbesserungen und Bugfixes innerhalb der Open Beta

* Automatisches Einfügen des Protokolls bei Socialmedia-Links
* Einstellung 159 (Unterkategorie-Anzeige) beachten
* Megamenü unterstützt optional eine Linkgruppe mit dem Namen "megamenu"
* Required-Attribut für alle Pflichteingabefelder gesetzt
* Kategoriebox Quelltext neu aufgebaut, Kategoriebaum bleibt nun aufgeklappt
* Sticky-Header initialer Darstellungsfehler behoben
* Klick auf Artikeldetail-Tabs wird nicht mehr im Browserverlauf hinterlegt

### 02.10.2015 Verbesserungen und Bugfixes innerhalb der Open Beta 

* Neue Artikelbild-Platzhalter (keinBild) eingefügt
* Sticky Header: bei Scrolldown verstecken
* Reihenfolge von JS/CSS im Header angepasst
* Nicht weiter benötigte Dateien gelöscht
* custom.css pro Theme hinzugefügt
* Klick-Events in Kategoriebox gefixt
* Bugfix: Lieferadresseingabe Label bei Anrede fehlte, Sichtbarkeitsproblem bei neuer Adresseingabe
* Lieferwagen-Icon als Lagerampel-Ersatz eingeführt
* JTL-Search-Integration: class ac_input ergaenzt
