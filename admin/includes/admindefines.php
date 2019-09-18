<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
error_reporting(ADMIN_LOG_LEVEL);
date_default_timezone_set(SHOP_TIMEZONE);
// Captcha file
define('CAPTCHA_LOCKFILE', PFAD_ROOT . PFAD_ADMIN . 'templates_c/captcha.lock');
// AdminMenu
define('AM_EINSTELLUNGEN', 1);
define('AM_LINKS', 2);
define('AM_AGBWRB', 3);
define('AM_ZAHLUNGSARTEN', 4);
define('AM_VERSANDARTEN', 5);
define('AM_EMAILVORLAGEN', 6);
define('AM_KUPONS', 7);
define('AM_KEYWORDING', 8);
define('AM_EXPORTFORMATE', 9);
define('AM_SHOPTEMPLATE', 10);
define('AM_SHOPUPDATE', 11);
define('AM_RSS', 12);
define('AM_KUNDENIMPORT', 13);
define('AM_SHOPZURUECKSETZEN', 14);
define('AM_KONTAKTFORMULAR', 15);
define('AM_UMSAETZE', 16);
define('AM_HERKUNFT', 17);
define('AM_BESUCHER', 18);
define('AM_BESUCHTESEITEN', 19);
define('AM_PRODUKTANFRAGEN', 20);
define('AM_PASSWORTAENDERN', 21);
define('AM_PREISANZEIGE', 22);
define('AM_MONEYBOOKERS', 23);
define('AM_SITEMAP', 24);
define('AM_SHOPINFO', 25);
define('AM_LIVESUCHE', 26);
define('AM_PRODUKTTAGGING', 27);
define('AM_WUNSCHZETTEL', 28);
define('AM_BEWERTUNGSSYSTEM', 29);
define('AM_PREISVERLAUF', 30);
define('AM_VERGLEICHSLISTE', 31);
define('AM_NEWSLETTER', 32);
define('AM_SELBSTDEFINIERTEKUNDENFELDER', 33);
define('AM_NAVIGATIONSFILTER', 34);
define('AM_SUCHSPECIALS', 35);
define('AM_NEWSSYSTEM', 36);
define('AM_GESPEICHERTEWARENKOERBE', 37);
define('AM_ZUSATZVERPACKUNG', 38);
define('AM_EMAILBLACKLIST', 39);
define('AM_GLOBALEMETAANGABEN', 40);
define('AM_SHOPSITEMAP', 41);
define('AM_UMFRAGESYSTEM', 42);
define('AM_KUNDENWERBENKUNDEN', 43);
define('AM_WASSERZEICHENBRANDING', 44);
define('AM_FREISCHALTZENTRALE', 45);
define('AM_YATEGOEXPORT', 46);
define('AM_EXPORTFORMATWARTESCHLANGE', 47);
define('AM_SUCHSPECIALBILDOVERLAY', 48);
define('AM_STATUSEMAIL', 49);
define('AM_TRUSTEDSHOPS', 50);
define('AM_PLUGINVERWALTUNG', 51);

define('ADMINGROUP', 1);

define('KADMINMENU_EINSTELLUNGEN', 2);
