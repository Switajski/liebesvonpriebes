<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

// Version
define('JTL_VERSION', 406);
define('JTL_MIN_WAWI_VERSION', 99713);
define('JTL_MINOR_VERSION', '14');
// Einstellungssektionen
define('CONF_GLOBAL', 1);
define('CONF_STARTSEITE', 2);
define('CONF_EMAILS', 3);
define('CONF_ARTIKELUEBERSICHT', 4);
define('CONF_ARTIKELDETAILS', 5);
define('CONF_KUNDEN', 6);
define('CONF_KAUFABWICKLUNG', 7);
define('CONF_BOXEN', 8);
define('CONF_BILDER', 9);
define('CONF_SONSTIGES', 10);
define('CONF_TEMPLATE', 11);
//
define('CONF_ZAHLUNGSARTEN', 100);
define('CONF_EXPORTFORMATE', 101);
define('CONF_KONTAKTFORMULAR', 102);
define('CONF_SHOPINFO', 103);
define('CONF_RSS', 104);
define('CONF_PREISVERLAUF', 105);
define('CONF_VERGLEICHSLISTE', 106);
define('CONF_BEWERTUNG', 107);
define('CONF_NEWSLETTER', 108);
define('CONF_KUNDENFELD', 109);
define('CONF_NAVIGATIONSFILTER', 110);
define('CONF_EMAILBLACKLIST', 111);
define('CONF_METAANGABEN', 112);
define('CONF_NEWS', 113);
define('CONF_SITEMAP', 114);
define('CONF_UMFRAGE', 115);
define('CONF_KUNDENWERBENKUNDEN', 116);
define('CONF_TRUSTEDSHOPS', 117);
define('CONF_PREISANZEIGE', 118);
define('CONF_SUCHSPECIAL', 119);
define('CONF_CHECKBOX', 120);
define('CONF_AUSWAHLASSISTENT', 121);
define('CONF_RMA', 122);
define('CONF_OBJECTCACHING', 123);
define('CONF_CACHING', 124);
define('CONF_LOGO', 125);
define('CONF_PLUGINZAHLUNGSARTEN', 126);
//
define('C_WARENKORBPOS_TYP_ARTIKEL', '1');
define('C_WARENKORBPOS_TYP_VERSANDPOS', '2');
define('C_WARENKORBPOS_TYP_KUPON', '3');
define('C_WARENKORBPOS_TYP_GUTSCHEIN', '4');
define('C_WARENKORBPOS_TYP_ZAHLUNGSART', '5');
define('C_WARENKORBPOS_TYP_VERSANDZUSCHLAG', '6');
define('C_WARENKORBPOS_TYP_NEUKUNDENKUPON', '7');
define('C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR', '8');
define('C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG', '9');
define('C_WARENKORBPOS_TYP_VERPACKUNG', '10');
define('C_WARENKORBPOS_TYP_GRATISGESCHENK', '11');
define('C_WARENKORBPOS_TYP_TRUSTEDSHOPS', '12');
//
define('C_WARENKORBPOS_TYP_ZINSAUFSCHLAG', '13');
define('C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR', '14');
//
define('KONFIG_ITEM_TYP_ARTIKEL', 0);
define('KONFIG_ITEM_TYP_SPEZIAL', 1);
//
define('KONFIG_ANZEIGE_TYP_CHECKBOX', 0);
define('KONFIG_ANZEIGE_TYP_RADIO', 1);
define('KONFIG_ANZEIGE_TYP_DROPDOWN', 2);
define('KONFIG_ANZEIGE_TYP_DROPDOWN_MULTI', 3);
//
define('KONFIG_AUSWAHL_TYP_BELIEBIG', -1);
define('KONFIG_AUSWAHL_TYP_MIN1', 0);
// KONFIG_AUSWAHL_TYP_EXAKT > 0
//
define('URLART_ARTIKEL', 1);
define('URLART_KATEGORIE', 2);
define('URLART_SEITE', 3);
define('URLART_HERSTELLER', 4);
define('URLART_LIVESUCHE', 5);
define('URLART_TAG', 6);
define('URLART_MERKMAL', 7);
define('URLART_NEWS', 8);
define('URLART_NEWSMONAT', 9);
define('URLART_NEWSKATEGORIE', 10);
define('URLART_UMFRAGE', 11);
define('URLART_SEARCHSPECIALS', 12);
// bestellstati
define('BESTELLUNG_STATUS_STORNO', -1);
define('BESTELLUNG_STATUS_OFFEN', 1);
define('BESTELLUNG_STATUS_IN_BEARBEITUNG', 2);
define('BESTELLUNG_STATUS_BEZAHLT', 3);
define('BESTELLUNG_STATUS_VERSANDT', 4);
define('BESTELLUNG_STATUS_TEILVERSANDT', 5);
// zahlungsart mails
define('ZAHLUNGSART_MAIL_EINGANG', 0x0001);
define('ZAHLUNGSART_MAIL_STORNO', 0x0010);
define('ZAHLUNGSART_MAIL_RESTORNO', 0x0100);
// mailtemplates
define('MAILTEMPLATE_GUTSCHEIN', 'core_jtl_gutschein');
define('MAILTEMPLATE_BESTELLBESTAETIGUNG', 'core_jtl_bestellbestaetigung');
define('MAILTEMPLATE_PASSWORT_VERGESSEN', 'core_jtl_passwort_vergessen');
define('MAILTEMPLATE_ADMINLOGIN_PASSWORT_VERGESSEN', 'core_jtl_admin_passwort_vergessen');
define('MAILTEMPLATE_NEUKUNDENREGISTRIERUNG', 'core_jtl_neukundenregistrierung');
define('MAILTEMPLATE_ACCOUNTERSTELLUNG_DURCH_BETREIBER', 'core_jtl_accounterstellung_durch_betreiber');
define('MAILTEMPLATE_BESTELLUNG_BEZAHLT', 'core_jtl_bestellung_bezahlt');
define('MAILTEMPLATE_BESTELLUNG_VERSANDT', 'core_jtl_bestellung_versandt');
define('MAILTEMPLATE_BESTELLUNG_AKTUALISIERT', 'core_jtl_bestellung_aktualisiert');
define('MAILTEMPLATE_BESTELLUNG_STORNO', 'core_jtl_bestellung_storno');
define('MAILTEMPLATE_BESTELLUNG_RESTORNO', 'core_jtl_bestellung_restorno');
define('MAILTEMPLATE_KUNDENACCOUNT_GELOESCHT', 'core_jtl_account_geloescht');
define('MAILTEMPLATE_KUPON', 'core_jtl_kupon');
define('MAILTEMPLATE_KUNDENGRUPPE_ZUWEISEN', 'core_jtl_kdgrp_zuweisung');
define('MAILTEMPLATE_KONTAKTFORMULAR', 'core_jtl_kontaktformular');
define('MAILTEMPLATE_PRODUKTANFRAGE', 'core_jtl_produktanfrage');
define('MAILTEMPLATE_PRODUKT_WIEDER_VERFUEGBAR', 'core_jtl_verfuegbarkeitsbenachrichtigung');
define('MAILTEMPLATE_WUNSCHLISTE', 'core_jtl_wunschliste');
define('MAILTEMPLATE_BEWERTUNGERINNERUNG', 'core_jtl_bewertungerinnerung');
define('MAILTEMPLATE_NEWSLETTERANMELDEN', 'core_jtl_newsletteranmelden');
define('MAILTEMPLATE_KUNDENWERBENKUNDEN', 'core_jtl_kundenwerbenkunden');
define('MAILTEMPLATE_KUNDENWERBENKUNDENBONI', 'core_jtl_kundenwerbenkundenboni');
define('MAILTEMPLATE_STATUSEMAIL', 'core_jtl_statusemail');
define('MAILTEMPLATE_CHECKBOX_SHOPBETREIBER', 'core_jtl_checkbox_shopbetreiber');
define('MAILTEMPLATE_RMA_ABGESENDET', 'core_jtl_rma_submitted');
define('MAILTEMPLATE_BEWERTUNG_GUTHABEN', 'core_jtl_bewertung_guthaben');
define('MAILTEMPLATE_BESTELLUNG_TEILVERSANDT', 'core_jtl_bestellung_teilversandt');
define('MAILTEMPLATE_ANBIETERKENNZEICHNUNG', 'core_jtl_anbieterkennzeichnung');
// Suche
define('SEARCH_SORT_STANDARD', 100);
define('SEARCH_SORT_NAME_ASC', 1);
define('SEARCH_SORT_NAME_DESC', 2);
define('SEARCH_SORT_PRICE_ASC', 3);
define('SEARCH_SORT_PRICE_DESC', 4);
define('SEARCH_SORT_EAN', 5);
define('SEARCH_SORT_NEWEST_FIRST', 6);
define('SEARCH_SORT_PRODUCTNO', 7);
define('SEARCH_SORT_AVAILABILITY', 8);
define('SEARCH_SORT_WEIGHT', 9);
define('SEARCH_SORT_DATEOFISSUE', 10);
define('SEARCH_SORT_BESTSELLER', 11);
define('SEARCH_SORT_RATING', 12);
//
define('SEARCH_SORT_CRITERION_NAME', 'artikelname');
define('SEARCH_SORT_CRITERION_NAME_ASC', 'artikelname aufsteigend');
define('SEARCH_SORT_CRITERION_NAME_DESC', 'artikelname absteigend');
define('SEARCH_SORT_CRITERION_PRODUCTNO', 'artikelnummer');
define('SEARCH_SORT_CRITERION_AVAILABILITY', 'lagerbestand');
define('SEARCH_SORT_CRITERION_WEIGHT', 'gewicht');
define('SEARCH_SORT_CRITERION_PRICE', 'preis');
define('SEARCH_SORT_CRITERION_PRICE_ASC', 'preis aufsteigend');
define('SEARCH_SORT_CRITERION_PRICE_DESC', 'preis absteigend');
define('SEARCH_SORT_CRITERION_EAN', 'ean');
define('SEARCH_SORT_CRITERION_NEWEST_FIRST', 'neuste zuerst');
define('SEARCH_SORT_CRITERION_DATEOFISSUE', 'erscheinungsdatum');
define('SEARCH_SORT_CRITERION_BESTSELLER', 'bestseller');
define('SEARCH_SORT_CRITERION_RATING', 'bewertungen');
// Einstellungen
define('EINSTELLUNGEN_ARTIKELANZEIGEFILTER_ALLE', 1);
define('EINSTELLUNGEN_ARTIKELANZEIGEFILTER_LAGER', 2);
define('EINSTELLUNGEN_ARTIKELANZEIGEFILTER_LAGERNULL', 3);
define('EINSTELLUNGEN_KATEGORIEANZEIGEFILTER_ALLE', 1);
define('EINSTELLUNGEN_KATEGORIEANZEIGEFILTER_NICHTLEERE', 2);
// Linktypen
define('LINKTYP_EIGENER_CONTENT', 1);
define('LINKTYP_EXTERNE_URL', 2);
define('LINKTYP_STARTSEITE', 5);
define('LINKTYP_VERSAND', 6);
define('LINKTYP_LOGIN', 7);
define('LINKTYP_REGISTRIEREN', 8);
define('LINKTYP_WARENKORB', 9);
define('LINKTYP_PASSWORD_VERGESSEN', 10);
define('LINKTYP_AGB', 11);
define('LINKTYP_DATENSCHUTZ', 12);
define('LINKTYP_KONTAKT', 13);
define('LINKTYP_TAGGING', 14);
define('LINKTYP_LIVESUCHE', 15);
define('LINKTYP_HERSTELLER', 16);
define('LINKTYP_NEWSLETTER', 17);
define('LINKTYP_NEWSLETTERARCHIV', 18);
define('LINKTYP_NEWS', 19);
define('LINKTYP_NEWSARCHIV', 20);
define('LINKTYP_SITEMAP', 21);
define('LINKTYP_UMFRAGE', 22);
define('LINKTYP_GRATISGESCHENK', 23);
define('LINKTYP_WRB', 24);
define('LINKTYP_PLUGIN', 25);
define('LINKTYP_AUSWAHLASSISTENT', 26);
define('LINKTYP_IMPRESSUM', 27);
define('LINKTYP_RMA', 28);
define('LINKTYP_404', 29);
define('LINKTYP_BATTERIEGESETZ_HINWEISE', 30);
define('LINKTYP_WRB_FORMULAR', 31);
define('LINKTYP_BESTELLVORGANG', 32);
define('LINKTYP_BESTELLABSCHLUSS', 33);
define('LINKTYP_WUNSCHLISTE', 34);
define('LINKTYP_VERGLEICHSLISTE', 35);
// Artikel
define('INWKNICHTLEGBAR_LAGER', -1);
define('INWKNICHTLEGBAR_LAGERVAR', -2);
define('INWKNICHTLEGBAR_NICHTVORBESTELLBAR', -3);
define('INWKNICHTLEGBAR_PREISAUFANFRAGE', -4);
define('INWKNICHTLEGBAR_UNVERKAEUFLICH', -5);
// Attribute
define('KAT_ATTRIBUT_KATEGORIEBOX', 'kategoriebox');
define('KAT_ATTRIBUT_ARTIKELSORTIERUNG', 'artikelsortierung');
define('KAT_ATTRIBUT_METATITLE', 'meta_title');
define('KAT_ATTRIBUT_METADESCRIPTION', 'meta_description');
define('KAT_ATTRIBUT_METAKEYWORDS', 'meta_keywords');
define('KAT_ATTRIBUT_BILDNAME', 'bildname');
define('KAT_ATTRIBUT_DARSTELLUNG', 'darstellung');
define('KAT_ATTRIBUT_CSSKLASSE', 'css_klasse');
define('KAT_ATTRIBUT_MERKMALFILTER', 'merkmalfilter');
define('ART_ATTRIBUT_STEUERTEXT', 'steuertext');
define('ART_ATTRIBUT_METATITLE', 'meta_title');
define('ART_ATTRIBUT_METADESCRIPTION', 'meta_description');
define('ART_ATTRIBUT_METAKEYWORDS', 'meta_keywords');
define('ART_ATTRIBUT_BILDLINK', 'artikelbildlink');
define('ART_ATTRIBUT_GRATISGESCHENKAB', 'gratisgeschenk ab');
define('ART_ATTRIBUT_AMPELTEXT_GRUEN', 'ampel_text_gruen');
define('ART_ATTRIBUT_AMPELTEXT_GELB', 'ampel_text_gelb');
define('ART_ATTRIBUT_AMPELTEXT_ROT', 'ampel_text_rot');
define('ART_ATTRIBUT_SHORTNAME', 'shortname');
define('KNDGRP_ATTRIBUT_MINDESTBESTELLWERT', 'mindestbestellwert');
// Fkt Attribute
define('FKT_ATTRIBUT_KEINE_PREISSUCHMASCHINEN', 'keine preissuchmaschinen');
define('FKT_ATTRIBUT_BILDNAME', 'bildname');
define('FKT_ATTRIBUT_UNVERKAEUFLICH', 'unverkaeuflich');
define('FKT_ATTRIBUT_VERSANDKOSTEN', 'versandkosten');
define('FKT_ATTRIBUT_VERSANDKOSTEN_GESTAFFELT', 'versandkosten gestaffelt');
define('FKT_ATTRIBUT_MAXBESTELLMENGE', 'max bestellmenge');
define('FKT_ATTRIBUT_GRATISGESCHENK', 'gratisgeschenk ab');
define('FKT_ATTRIBUT_GRUNDPREISGENAUIGKEIT', 'grundpreis genauigkeit');
define('FKT_ATTRIBUT_WARENKORBMATRIX', 'warenkorbmatrix');
define('FKT_ATTRIBUT_MEDIENDATEIEN', 'mediendateien');
define('FKT_ATTRIBUT_ATTRIBUTEANHAENGEN', 'attribute anhaengen');
define('FKT_ATTRIBUT_STUECKLISTENKOMPONENTEN', 'stuecklistenkomponenten');
define('FKT_ATTRIBUT_INHALT', 'inhalt');
define('FKT_ATTRIBUT_CANONICALURL_VARKOMBI', 'varkombi_canonicalurl');
define('FKT_ATTRIBUT_KONFIG_MAX_ITEMS', 'konfig_max_items'); // deprecated? not in use anywhere
define('FKT_ATTRIBUT_ARTIKELDETAILS_TPL', 'tpl_artikeldetails');
define('FKT_ATTRIBUT_ARTIKELKONFIG_TPL', 'tpl_artikelkonfig');
define('FKT_ATTRIBUT_ARTIKELKONFIG_TPL_JS', 'tpl_js_artikelkonfig');
// Special Content
define('SC_KONTAKTFORMULAR', '1');
// Suchspecials
define('SEARCHSPECIALS_BESTSELLER', 1);
define('SEARCHSPECIALS_SPECIALOFFERS', 2);
define('SEARCHSPECIALS_NEWPRODUCTS', 3);
define('SEARCHSPECIALS_TOPOFFERS', 4);
define('SEARCHSPECIALS_UPCOMINGPRODUCTS', 5);
define('SEARCHSPECIALS_TOPREVIEWS', 6);
define('SEARCHSPECIALS_OUTOFSTOCK', 7);
define('SEARCHSPECIALS_ONSTOCK', 8);
define('SEARCHSPECIALS_PREORDER', 9);
// Adminmenu (Backend)
define('LINKTYP_BACKEND_PLUGINS', 5);
define('LINKTYP_BACKEND_MODULE', 7);
// Plugin
define('PFAD_PLUGIN_VERSION', 'version/');
define('PFAD_PLUGIN_SQL', 'sql/');
define('PFAD_PLUGIN_FRONTEND', 'frontend/');
define('PFAD_PLUGIN_ADMINMENU', 'adminmenu/');
define('PFAD_PLUGIN_LICENCE', 'licence/');
define('PFAD_PLUGIN_PAYMENTMETHOD', 'paymentmethod/');
define('PFAD_PLUGIN_TEMPLATE', 'template/');
define('PFAD_PLUGIN_BOXEN', 'boxen/');
define('PFAD_PLUGIN_WIDGET', 'widget/');
define('PFAD_PLUGIN_EXPORTFORMAT', 'exportformat/');
define('PFAD_PLUGIN_UNINSTALL', 'uninstall/');
define('PLUGIN_INFO_FILE', 'info.xml');
define('PLUGIN_LICENCE_METHODE', 'checkLicence');
define('PLUGIN_LICENCE_CLASS', 'PluginLicence');
define('PLUGIN_EXPORTFORMAT_CONTENTFILE', 'PluginContentFile_');
define('PLUGIN_SEITENHANDLER', 'seite_plugin.php');
define('PLUGIN_BOOTSTRAPPER', 'bootstrap.php');
// Red. Param
define('R_MINDESTMENGE', 1);
define('R_LAGER', 2);
define('R_LOGIN', 3);
define('R_VORBESTELLUNG', 4);
define('R_VARWAEHLEN', 5);
define('R_LAGERVAR', 6);
define('R_LOGIN_WUNSCHLISTE', 7);
define('R_MAXBESTELLMENGE', 8);
define('R_LOGIN_BEWERTUNG', 9);
define('R_LOGIN_TAG', 10);
define('R_LOGIN_NEWSCOMMENT', 11);
define('R_LOGIN_UMFRAGE', 12);
define('R_ARTIKELABNAHMEINTERVALL', 14);
define('R_UNVERKAEUFLICH', 15);
define('R_AUFANFRAGE', 16);
define('R_EMPTY_TAG', 17);
define('R_EMPTY_VARIBOX', 18);
define('R_LOGIN_RMA', 19);
// Kategorietiefe
// 0 = Aus
// 1 = Tiefe 0 (Hauptkategorien)
// 2 = Tiefe 1
// 3 = Tiefe 2
define('K_KATEGORIE_TIEFE', 3);
// url sep
define('SEP_SEITE', '_s');
define('SEP_KAT', ':');
define('SEP_HST', '::');
define('SEP_MERKMAL', '__');
define('SEP_MM_MMW', '--');
// extract params seperator
define('EXT_PARAMS_SEPERATORS_REGEX', '\&\?');
// Updater
define('U_FILE_PREFIX', 'jtl_update_');
// JobQueue
defined('JOBQUEUE_LIMIT_JOBS') || define('JOBQUEUE_LIMIT_JOBS', 5);
defined('JOBQUEUE_LIMIT_M_EXPORTE') || define('JOBQUEUE_LIMIT_M_EXPORTE', 500);
define('JOBQUEUE_LIMIT_M_NEWSLETTER', 100);
define('JOBQUEUE_LIMIT_M_STATUSEMAIL', 1);
// Exportformate
defined('EXPORTFORMAT_LIMIT_M') || define('EXPORTFORMAT_LIMIT_M', 2000);
defined('EXPORTFORMAT_ASYNC_LIMIT_M') || define('EXPORTFORMAT_ASYNC_LIMIT_M', 15);
// Special Exportformate
define('SPECIAL_EXPORTFORMAT_YATEGO', 1);
// Shop Template Logo Name
define('SHOPLOGO_NAME', 'jtlshoplogo');
// Erweiterte Artikelübersicht Darstellung
define('ERWDARSTELLUNG_ANSICHT_LISTE', 1); // Standard
define('ERWDARSTELLUNG_ANSICHT_GALERIE', 2);
define('ERWDARSTELLUNG_ANSICHT_MOSAIK', 3);
define('ERWDARSTELLUNG_ANSICHT_ANZAHL_STD', 25); // Standard
// LastJobs
define('LASTJOBS_INTERVALL', 12); // Intervall in Stunden
define('LASTJOBS_BEWERTUNGSERINNNERUNG', 1); // Bewertungserinnerungskey
define('LASTJOBS_SITEMAP', 2); // Sitemapkey
define('LASTJOBS_RSS', 3); // RSSkey
define('LASTJOBS_GARBAGECOLLECTOR', 4); // GarbageCollector
define('LASTJOBS_KATEGORIEUPDATE', 5); // Kategorielevel update, nested set build
// Seitentypen
define('PAGE_UNBEKANNT', 0);
define('PAGE_ARTIKEL', 1); // Artikeldetails
define('PAGE_ARTIKELLISTE', 2); // Artikelliste
define('PAGE_WARENKORB', 3); // Warenkorb
define('PAGE_MEINKONTO', 4); // Mein Konto
define('PAGE_KONTAKT', 5); // Kontakt
define('PAGE_UMFRAGE', 6); // Umfrage
define('PAGE_NEWS', 7); // News
define('PAGE_NEWSLETTER', 8); // Newsletter
define('PAGE_LOGIN', 9); // Login
define('PAGE_REGISTRIERUNG', 10); // Registrierung
define('PAGE_BESTELLVORGANG', 11); // Bestellvorgang
define('PAGE_BEWERTUNG', 12); // Bewertung [NEIN]
define('PAGE_DRUCKANSICHT', 13); // Druckansicht
define('PAGE_PASSWORTVERGESSEN', 14); // Passwort vergessen
define('PAGE_WARTUNG', 15); // Wartung
define('PAGE_WUNSCHLISTE', 16); // Wunschliste
define('PAGE_VERGLEICHSLISTE', 17); // Vergleichsliste
define('PAGE_STARTSEITE', 18); // Startseite
define('PAGE_VERSAND', 19); // Versand
define('PAGE_AGB', 20); // AGB
define('PAGE_DATENSCHUTZ', 21); // Datenschutz
define('PAGE_TAGGING', 22); // Tagging
define('PAGE_LIVESUCHE', 23); // Livesuche
define('PAGE_HERSTELLER', 24); // Hersteller
define('PAGE_SITEMAP', 25); // Sitemap
define('PAGE_GRATISGESCHENK', 26); // Gratis Geschenk
define('PAGE_WRB', 27); // WRB
define('PAGE_PLUGIN', 28); // Plugin
define('PAGE_NEWSLETTERARCHIV', 29); // Newsletterarchiv
define('PAGE_NEWSARCHIV', 30); // Newsarchiv
define('PAGE_EIGENE', 31); // Eigene Seite
define('PAGE_AUSWAHLASSISTENT', 32); // Auswahlassistent
define('PAGE_BESTELLABSCHLUSS', 33); // Bestellabschluss
define('PAGE_RMA', 34); // Warenruecksendung
define('PAGE_404', 36);
define('PAGE_IO', 37);
define('PAGE_BESTELLSTATUS', 38);
define('PAGE_MEDIA', 39);
// Boxen
define('BOX_BESTSELLER', 1);
define('BOX_KATEGORIEN', 2);
define('BOX_VERGLEICHSLISTE', 3);
define('BOX_WUNSCHLISTE', 4);
define('BOX_LOGIN', 5);
define('BOX_FINANZIERUNG', 6);
define('BOX_ZULETZT_ANGESEHEN', 7);
define('BOX_HERSTELLER', 8);
define('BOX_NEUE_IM_SORTIMENT', 9);
define('BOX_NEWS_KATEGORIEN', 10);
define('BOX_NEWS_AKTUELLER_MONAT', 11);
define('BOX_SCHNELLKAUF', 12);
define('BOX_SUCHWOLKE', 13);
define('BOX_SONDERANGEBOT', 14);
define('BOX_TOP_ANGEBOT', 15);
define('BOX_TOP_BEWERTET', 16);
define('BOX_TRUSTEDSHOPS_KUNDENBEWERTUNGEN', 17);
define('BOX_TRUSTEDSHOPS_GUETESIEGEL', 18);
define('BOX_IN_KUERZE_VERFUEGBAR', 19);
define('BOX_GLOBALE_MERKMALE', 20);
define('BOX_WARENKORB', 21);
define('BOX_UMFRAGE', 22);
define('BOX_LINKGRUPPE', 23);
define('BOX_FILTER_TAG', 24);
define('BOX_FILTER_PREISSPANNE', 25);
define('BOX_FILTER_BEWERTUNG', 26);
define('BOX_FILTER_MERKMALE', 27);
define('BOX_FILTER_SUCHE', 28);
define('BOX_FILTER_SUCHSPECIAL', 29);
define('BOX_EIGENE_BOX_OHNE_RAHMEN', 30);
define('BOX_EIGENE_BOX_MIT_RAHMEN', 31);
define('BOX_TAGWOLKE', 32);
define('BOX_KONFIGURATOR', 33);
define('BOX_PREISRADAR', 100);
// Kampagnentypen
define('KAMPAGNE_DEF_HIT', 1);
define('KAMPAGNE_DEF_VERKAUF', 2);
define('KAMPAGNE_DEF_ANMELDUNG', 3);
define('KAMPAGNE_DEF_VERKAUFSSUMME', 4);
define('KAMPAGNE_DEF_FRAGEZUMPRODUKT', 5);
define('KAMPAGNE_DEF_VERFUEGBARKEITSANFRAGE', 6);
define('KAMPAGNE_DEF_LOGIN', 7);
define('KAMPAGNE_DEF_WUNSCHLISTE', 8);
define('KAMPAGNE_DEF_WARENKORB', 9);
define('KAMPAGNE_DEF_NEWSLETTER', 10);
// Interne Kampagnen
define('KAMPAGNE_INTERN_VERFUEGBARKEIT', 1);
define('KAMPAGNE_INTERN_OEFFENTL_WUNSCHZETTEL', 2);
define('KAMPAGNE_INTERN_GOOGLE', 3);
// Backend Statistiktypen
define('STATS_ADMIN_TYPE_BESUCHER', 1);
define('STATS_ADMIN_TYPE_KUNDENHERKUNFT', 2);
define('STATS_ADMIN_TYPE_SUCHMASCHINE', 3);
define('STATS_ADMIN_TYPE_UMSATZ', 4);
define('STATS_ADMIN_TYPE_EINSTIEGSSEITEN', 5);
// Newsletter URL_SHOP Parsevariable für Bilder in der Standardvorlage
define('NEWSLETTER_STD_VORLAGE_URLSHOP', '$#URL_SHOP#$');
// Trusted Shops Käuferschutz Varianten
define('TS_BUYERPROT_CLASSIC', 'CLASSIC');
define('TS_BUYERPROT_EXCELLENCE', 'EXCELLENCE');
// CheckBox
define('CHECKBOX_ORT_REGISTRIERUNG', 1);
define('CHECKBOX_ORT_BESTELLABSCHLUSS', 2);
define('CHECKBOX_ORT_NEWSLETTERANMELDUNG', 3);
define('CHECKBOX_ORT_KUNDENDATENEDITIEREN', 4);
define('CHECKBOX_ORT_KONTAKT', 5);
define('CHECKBOX_ORT_FRAGE_ZUM_PRODUKT', 6);
define('CHECKBOX_ORT_FRAGE_VERFUEGBARKEIT', 7);
// JTLLOG Levels
define('JTLLOG_LEVEL_ERROR', 1); // 0001
define('JTLLOG_LEVEL_NOTICE', 2); // 0010
define('JTLLOG_LEVEL_DEBUG', 4); // 0100
// JTL Trennzeichen
define('JTLSEPARATER_WEIGHT', 1);
define('JTLSEPARATER_LENGTH', 2);
define('JTLSEPARATER_AMOUNT', 3);
// Globale Arten von generierte Nummern (z.b. Bestellnummer)
define('JTL_GENNUMBER_ORDERNUMBER', 1);
define('JTL_GENNUMBER_RMANUMBER', 2);
// JTL Support Email
define('JTLSUPPORT_EMAIL', 'support@jtl-software.de');
// JTL URLS
define('JTLURL_BASE', 'https://ext.jtl-software.de/');
define('JTLURL_HP', 'https://www.jtl-software.de/');
define('JTLURL_GET_DUK', JTLURL_BASE . 'json_duk.php');
define('JTLURL_GET_SHOPNEWS', JTLURL_HP . 'news_json.php?notimeline=1&limit=5');
define('JTLURL_GET_SHOPPATCH', JTLURL_BASE . 'json_patch.php');
define('JTLURL_GET_SHOPMARKETPLACE', JTLURL_BASE . 'json_marketplace.php');
define('JTLURL_GET_SHOPHELP', JTLURL_BASE . 'jtlhelp.php');
define('JTLURL_GET_SHOPVERSION', JTLURL_BASE . 'json_version.php');
// Log-Levels
define('LOGLEVEL_ERROR', 1);
define('LOGLEVEL_NOTICE', 2);
define('LOGLEVEL_DEBUG', 3);
// Auswahlassistent
define('AUSWAHLASSISTENT_ORT_STARTSEITE', 'kStartseite');
define('AUSWAHLASSISTENT_ORT_KATEGORIE', 'kKategorie');
define('AUSWAHLASSISTENT_ORT_LINK', 'kLink');
// Upload
define('UPLOAD_TYP_KUNDE', 1);
define('UPLOAD_TYP_BESTELLUNG', 2);
define('UPLOAD_TYP_WARENKORBPOS', 3);
define('UPLOAD_ERROR_NEED_UPLOAD', 12);
// Template
define('TEMPLATE_XML', 'template.xml');
// Seo
define('SHOP_SEO', true);
// Sessionspeicherung 1 => DB, sonst => Dateien
define('ES_SESSIONS', 0);
//Max Anzahl an Variationswerten für Warenkorbmatrix
define('ART_MATRIX_MAX', 250);
