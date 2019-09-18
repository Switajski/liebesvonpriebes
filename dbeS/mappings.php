<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

//mappings
$mKunde = [
    'cKundenNr',
    'cAnrede',
    'cTitel',
    'cVorname',
    'cNachname',
    'cFirma',
    'cStrasse',
    'cAdressZusatz',
    'cPLZ',
    'cOrt',
    'cLand',
    'cTel',
    'cMobil',
    'cFax',
    'cMail',
    'cUSTID',
    'cWWW',
    'cSperre',
    'dGeburtstag',
    'fRabatt',
    'cBundesland',
    'cZusatz'
];

$mKategorie = [
    'cName',
    'cSeo',
    'cBeschreibung',
    'nSort'
];

$mKategorieSprache = [
    'cName',
    'cSeo',
    'cBeschreibung',
    'cMetaDescription',
    'cMetaKeywords',
    'cTitleTag',
];

$mKategorieKundengruppe = [
    'fRabatt'
];

$mKategorieAttribut = [
    'cName',
    'cWert'
];

$mNormalKategorieAttribut = [
    'cName'                 => null,
    'cWert'                 => null,
    'nSort'                 => 0,
    'bIstFunktionsAttribut' => 0,
];

$mKategorieAttributSprache = [
    'cName',
    'cWert',
];

$mKategorieSichtbarkeit = [];

$mLieferadresse = [
    'cFirma',
    'cLand',
    'cNachname',
    'cOrt',
    'cPLZ',
    'cStrasse',
    'cTel',
    'cTitel',
    'cVorname',
    'cAdressZusatz',
    'cZusatz',
    'cAnrede'
];

$mFirma = [
    'cName',
    'cUnternehmer',
    'cStrasse',
    'cPLZ',
    'cOrt',
    'cLand',
    'cTel',
    'cFax',
    'cEMail',
    'cWWW',
    'cBLZ',
    'cKontoNr',
    'cBank',
    'cUSTID',
    'cSteuerNr',
    'cIBAN',
    'cBIC',
    'cKontoinhaber'
];

$mHersteller = [
    'cName',
    'cSeo',
    'cHomepage',
    'nSortNr'
];

$mHerstellerSprache = [
    'cMetaTitle',
    'cMetaKeywords',
    'cMetaDescription',
    'cBeschreibung'
];

//
$mHerstellerSpracheSeo = [
    'cSeo'
];

$mLieferstatus = [
    'cName'
];

$mXsellgruppe = [
    'cName',
    'cBeschreibung'
];

$mEinheit = [
    'cName'
];

$mKundengruppe = [
    'cName',
    'fRabatt',
    'cStandard',
    'cShopLogin',
    'nNettoPreise'
];

$mKundengruppensprache = [
    'cName'
];

$mKundengruppenattribut = [
    'cName',
    'cWert'
];

$mSprache = [
    'cNameEnglisch',
    'cNameDeutsch',
    'cISO',
    'cStandard',
    'cShopStandard',
    'cWawiStandard'
];

$mWaehrung = [
    'cName',
    'cNameHTML',
    'fFaktor',
    'cISO',
    'cStandard',
    'cVorBetrag',
    'cTrennzeichenCent',
    'cTrennzeichenTausend'
];

$mArtikel = [
    'cArtNr',
    'cName',
    'cHAN',
    'cSeo',
    'cAnmerkung',
    'cBeschreibung',
    'fLagerbestand',
    'fMwSt',
    'fMindestbestellmenge',
    'fLieferantenlagerbestand',
    'fLieferzeit',
    'fStandardpreisNetto',
    'cBarcode',
    'cTopArtikel',
    'fGewicht',
    'fArtikelgewicht',
    'cNeu',
    'cKurzBeschreibung',
    'fUVP',
    'cLagerBeachten',
    'cLagerKleinerNull',
    'cLagerVariation',
    'cTeilbar',
    'fAbnahmeintervall',
    'cVPE',
    'fVPEWert',
    'cVPEEinheit',
    'nSort',
    'cSuchbegriffe',
    'dErstellt',
    'dErscheinungsdatum',
    'cSerie',
    'cISBN',
    'cASIN',
    'cUNNummer',
    'cGefahrnr',
    'kVersandklasse',
    'nIstVater',
    'kVaterArtikel',
    'kEigenschaftKombi',
    'kVPEEinheit',
    'kStueckliste',
    'kWarengruppe',
    'cTaric',
    'cUPC',
    'cHerkunftsland',
    'cEPID',
    'fZulauf',
    'dZulaufDatum',
    'dMHD',
    'kMassEinheit',
    'kGrundPreisEinheit',
    'fMassMenge',
    'fGrundpreisMenge',
    'fBreite',
    'fHoehe',
    'fLaenge',
    'nLiefertageWennAusverkauft',
    'nAutomatischeLiefertageberechnung',
    'nBearbeitungszeit'
];

$mArtikelQuickSync = [
    'fLagerbestand',
    'fStandardpreisNetto'
];

$mPreise = [
    'fVKNetto' => 0,
    'nAnzahl1' => 0,
    'nAnzahl2' => 0,
    'nAnzahl3' => 0,
    'nAnzahl4' => 0,
    'nAnzahl5' => 0,
    'fPreis1'  => 0,
    'fPreis2'  => 0,
    'fPreis3'  => 0,
    'fPreis4'  => 0,
    'fPreis5'  => 0
];

$mPreis = [
    'tpreisdetail'
];

$mPreisDetail = [
    'nAnzahlAb',
    'fNettoPreis'
];

$mArtikelSonderpreis = [
    'cAktiv'     => 'Y',
    'dStart'     => null,
    'nAnzahl'    => 0,
    'nIstAnzahl' => 0,
    'nIstDatum'  => 0,
    'dEnde'      => '0000-00-00'
];

$mSonderpreise = [
    'fNettoPreis'
];

$mKategorieArtikel = [];

$mArtikelSprache = [
    'cName',
    'cSeo',
    'cBeschreibung',
    'cKurzBeschreibung'
];

$mArtikelAttribut = [
    'cName',
    'cWert'
];

$mAttribut = [
    'cName',
    'cStringWert',
    'cTextWert',
    'nSort'
];

$mAttributSprache = [
    'cName',
    'cStringWert',
    'cTextWert'
];

$mEigenschaftsichtbarkeit = [];

$mEigenschaft = [
    'cName',
    'cTyp',
    'cWaehlbar',
    'nSort'
];

$mEigenschaftSprache = [
    'cName'
];

$mEigenschaftWert = [
    'cName'          => null,
    'fAufpreisNetto' => 0,
    'fGewichtDiff'   => 0,
    'cArtNr'         => 0,
    'nSort'          => 0,
    'fLagerbestand'  => 0,
    'fPackeinheit'   => 0
];

$mArtikelSichtbarkeit = [];

$mEigenschaftWertSprache = [
    'cName'
];

$mEigenschaftWertAufpreis = [
    'fAufpreisNetto'
];

$mEigenschaftWertSichtbarkeit = [];

$mXSell = [];

$mArtikelPict = [
    'cPfad',
    'nNr'
];

$mtArtikelPict = [
    'kArtikel',
    'nNr'
];

$mKategoriePict = [
    'cPfad',
    'cType'
];

$mKonfiggruppePict = [
    'cPfad',
    'cType'
];

$mEigenschaftWertPict = [
    'cPfad',
    'cType'
];

$mDelEigenschaftWertPict = [
    'kEigenschaftWert'
];

$mBestellung = [
    'dVersandt',
    'cIdentCode',
    'cVersandInfo',
    'dBezahltDatum',
    'cSendeEMail',
    'nKomplettAusgeliefert',
    'cLogistik',
    'cLogistikURL',
    'fGuthaben',
    'fGesamtsumme',
    'cKommentar',
    'cBestellNr',
    'cZahlungsartName',
    'fWaehrungsFaktor',
    'cBezahlt',
    'cPUIZahlungsdaten'
];

$mGutschein = [
    'fWert',
    'cGrund'
];

$mSteuerzone = [
    'cName'
];

$mSteuerzoneland = [
    'cISO'
];

$mWarengruppe = [
    'cName'
];

$mWarenlager = [
    'cName',
    'cKuerzel',
    'cLagerTyp',
    'cBeschreibung',
    'cStrasse',
    'cPLZ',
    'cOrt',
    'cLand',
    'nFulfillment'
];

$mMasseinheit = [
    'cCode',
];

$mMasseinheitsprache = [
    'cName',
];

$mArtikelWarenlager = [
    'fBestand',
    'fZulauf',
    'dZulaufDatum'
];

$mArtikelAbnahme = [
    'fMindestabnahme',
    'fIntervall'
];

$mSteuerklasse = [
    'cName',
    'cStandard'
];

$mSteuersatz = [
    'fSteuersatz',
    'nPrio'
];

$mEigenschaftWertAbhaengigkeit = [];

$mVersandklasse = [
    'cName'
];

$mMerkmal = [
    'nSort',
    'cName',
    'nGlobal',
    'cTyp'
];

$mMerkmalSprache = [
    'cName'
];

$mMerkmalWert = [
    'nSort'
];

$mMerkmalWertSprache = [
    'cWert',
    'cSeo',
    'cMetaTitle',
    'cMetaKeywords',
    'cMetaDescription',
    'cBeschreibung'
];

$mMediendatei = [
    'cPfad',
    'cURL',
    'cTyp',
    'nSort'
];

$mStueckliste = [
    'fAnzahl'
];

$mMediendateisprache = [
    'cName',
    'cBeschreibung'
];

$mMediendateiattribut = [
    'cName',
    'cWert'
];

$mArtikelUpload = [
    'nTyp',
    'cName',
    'cBeschreibung',
    'cDateiTyp',
    'nPflicht'
];

$mArtikelUploadSprache = [
    'cName',
    'cBeschreibung'
];

$mArtikelkonfiggruppe = [
    'nSort'
];

$mHerstellerBild = [
    'cPfad',
    'cType'
];

$mMerkmalWertBild = [
    'cPfad',
    'cType'
];

$mMerkmalBild = [
    'cPfad',
    'cType'
];

$mEigenschaftKombiWert = [];

$mRechnungsadresse = [
    'cAnrede',
    'cTitel',
    'cVorname',
    'cNachname',
    'cFirma',
    'cStrasse',
    'cAdressZusatz',
    'cPLZ',
    'cOrt',
    'cBundesland',
    'cLand',
    'cTel',
    'cMobil',
    'cFax',
    'cUSTID',
    'cWWW',
    'cMail',
    'cZusatz'
];

$mWarenkorbpos = [
    'cUnique',
    'cName',
    'cLieferstatus',
    'cArtNr',
    'cEinheit',
    'fPreisEinzelNetto',
    'fPreis',
    'fMwSt',
    'nAnzahl',
    'nPosTyp',
    'cHinweis'
];

$mWarenkorbposeigenschaft = [
    'cEigenschaftName',
    'cEigenschaftWertName',
    'cFreifeldWert',
    'fAufpreis'
];

$mDownload = [
    'cID',
    'cPfad',
    'cPfadVorschau',
    'nAnzahl',
    'nTage',
    'dErstellt',
    'nSort',
];

$mDownloadSprache = [
    'cName',
    'cBeschreibung'
];

$mKonfigSprache = [
    'cName',
    'cBeschreibung'
];

$mKonfigGruppe = [
    'nMin',
    'nMax',
    'nTyp',
    'nSort',
    'cKommentar'
];

$mKonfigItem = [
    'nPosTyp',
    'bSelektiert',
    'bEmpfohlen',
    'bName',
    'bPreis',
    'bRabatt',
    'bZuschlag',
    'fMin',
    'fMax',
    'fInitial',
    'bIgnoreMultiplier',
    'nSort'
];

$mKonfigItemPreis = [
    'kKundengruppe',
    'kSteuerklasse',
    'fPreis',
    'nTyp'
];

$mLieferschein = [
    'kLieferschein',
    'kInetBestellung',
    'cLieferscheinNr',
    'cHinweis',
    'nFulfillment',
    'nStatus',
    'dErstellt',
    'bEmailVerschickt'
];

$mLieferscheinpos = [
    'kLieferscheinPos',
    'kLieferschein',
    'kBestellPos',
    'kWarenlager',
    'fAnzahl'
];

$mLieferscheinposinfo = [
    'kLieferscheinPos',
    'dMHD',
    'cChargeNr',
    'cSeriennummer'
];

$mVersand = [
    'kVersand',
    'kLieferschein',
    'cLogistik',
    'cLogistikURL',
    'cIdentCode',
    'cHinweis',
    'dErstellt'
];
