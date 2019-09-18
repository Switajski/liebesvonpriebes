<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

//mainword
$kKonfigPos            = verifyGPCDataInteger('ek');
$kKategorie            = verifyGPCDataInteger('k');
$kArtikel              = verifyGPCDataInteger('a');
$kVariKindArtikel      = verifyGPCDataInteger('a2');
$kSeite                = verifyGPCDataInteger('s');
$kLink                 = verifyGPCDataInteger('s');
$kHersteller           = verifyGPCDataInteger('h');
$kSuchanfrage          = verifyGPCDataInteger('l');
$kMerkmalWert          = verifyGPCDataInteger('m');
$kTag                  = verifyGPCDataInteger('t');
$kSuchspecial          = verifyGPCDataInteger('q');
$kNews                 = verifyGPCDataInteger('n');
$kNewsMonatsUebersicht = verifyGPCDataInteger('nm');
$kNewsKategorie        = verifyGPCDataInteger('nk');
$kUmfrage              = verifyGPCDataInteger('u');
//filter
$nBewertungSterneFilter = verifyGPCDataInteger('bf');
$cPreisspannenFilter    = verifyGPDataString('pf');
$kHerstellerFilter      = verifyGPCDataInteger('hf');
$kKategorieFilter       = verifyGPCDataInteger('kf');
$kSuchspecialFilter     = verifyGPCDataInteger('qf');
$kSuchFilter            = verifyGPCDataInteger('sf');
// Erweiterte Artikelübersicht Darstellung
$nDarstellung = verifyGPCDataInteger('ed');
$nSortierung  = verifyGPCDataInteger('sortierreihenfolge');
$nSort        = verifyGPCDataInteger('Sortierung');

$show            = verifyGPCDataInteger('show');
$vergleichsliste = verifyGPCDataInteger('vla');
$bFileNotFound   = false;
$cCanonicalURL   = '';
$is404           = false;
