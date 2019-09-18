<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_DBES . 'xml_tools.php';

/**
 * Class UstID
 */
class UstID
{
    /**
     * @var string
     */
    public $cUstId_1;

    /**
     * @var string
     */
    public $cUstId_2;

    /**
     * @var string
     */
    public $cFirmenname;

    /**
     * @var string
     */
    public $cOrt;

    /**
     * @var string
     */
    public $cPLZ;

    /**
     * @var string
     */
    public $cStrasse;

    /**
     * @var string
     */
    public $cDruck;

    /**
     * @var string
     */
    public $cAntwort;

    /**
     * @var array
     */
    public $cAntwortInfo_arr;

    /**
     * @var string
     */
    public $cHausnummer;

    /**
     * @param string $cUstId_1
     * @param string $cUstId_2
     * @param string $cFirmenname
     * @param string $cOrt
     * @param string $cPLZ
     * @param string $cStrasse
     * @param string $cDruck
     * @param string $cHausnummer
     */
    public function __construct($cUstId_1, $cUstId_2, $cFirmenname, $cOrt, $cPLZ, $cStrasse, $cDruck = 'Nein', $cHausnummer = '')
    {
        $this->cUstId_1    = $cUstId_1;
        $this->cUstId_2    = $cUstId_2;
        $this->cFirmenname = $cFirmenname;
        $this->cOrt        = $cOrt;
        $this->cPLZ        = $cPLZ;
        $this->cStrasse    = $cStrasse;
        $this->cDruck      = $cDruck;
        $this->cHausnummer = $cHausnummer;
    }

    /**
     * @param bool $bBZStPruefung
     * @return int
     * 999 = Bundeszentralamt für Steuern ist nicht erreichbar
     * 0 = Die UstID Stringprüfung war ungültig.
     * 1 = Die UstID Stringprüfung war gültig jedoch kann durch die PHPini Einstellung keine Onlineabfrage abgeschickt werden.
     * 200-220 = Fehlercode vom Bundeszentralamt für Steuern
     */
    public function bearbeiteAnfrage($bBZStPruefung = false)
    {
        $oReturn = $this->pruefeUstIDString($this->cUstId_2);
        if (isset($oReturn->nRichtig) && $oReturn->nRichtig === 1) {
            if ($bBZStPruefung === true) {
                if ($this->pruefePHPEinstellung()) {
                    // Uhrzeit pruefen da die API Ruhezeit hat -.-
                    // Taeglich von 5 Uhr - 23 Uhr
                    if ((int)date('H') >= 5 && (int)date('H') < 23) {
                        $cURL = 'https://evatr.bff-online.de/evatrRPC?UstId_1=' . $this->urlencode_special($this->cUstId_1) .
                            '&UstId_2=' . $this->urlencode_special($this->cUstId_2) . 
                            '&Firmenname=' . $this->urlencode_special($this->cFirmenname) . 
                            '&Ort=' . $this->urlencode_special($this->cOrt) . 
                            '&PLZ=' . $this->urlencode_special($this->cPLZ) . 
                            '&Strasse=' . $this->urlencode_special($this->cStrasse . ' ' . $this->cHausnummer) . 
                            '&Druck=' . $this->urlencode_special($this->cDruck);
                        $xml = file_get_contents(str_replace(' ', '%20', $cURL));
                        $this->cAntwort = XML_unserialize($xml);
                        $paramCount     = count($this->cAntwort['params']['param']);
                        for ($i = 0; $i < $paramCount; $i++) {
                            $oInfo        = new stdClass();
                            $oInfo->cName = $this->cAntwort['params']['param'][$i]['value']['array']['data']['value'][0]['string'];
                            $oInfo->cWert = $this->cAntwort['params']['param'][$i]['value']['array']['data']['value'][1]['string'];
                            $this->cAntwortInfo_arr[$oInfo->cName] = $oInfo->cWert;
                        }

                        $nFehlerCode = (int)$this->cAntwortInfo_arr['ErrorCode'];
                        $this->mappeFehlerCode($nFehlerCode);
                        if ($nFehlerCode > 200) {
                            Jtllog::writeLog("Ust-ID-Pr&uuml;fung ErrorCode {$nFehlerCode} (" . $this->cAntwortInfo_arr['cFehlerNachricht'] . ")\n <br>Anfrage-URL: " . htmlentities($cURL), JTLLOG_LEVEL_DEBUG);
                        }
                        
                        return $nFehlerCode;
                    }
                    $this->mappeFehlerCode(999);

                    return 999;
                }

                return 1;
            }

            return 1;
        }

        return -1;
    }

    /**
     * @return bool
     */
    public function pruefePHPEinstellung()
    {
        return ini_get('allow_url_fopen');
    }

    /**
     * @param int $nFehlerCode
     * @return $this
     */
    public function mappeFehlerCode($nFehlerCode)
    {
        switch ($nFehlerCode) {
            case 200:
                $this->cAntwortInfo_arr['cFehlerNachricht'] = 'Die angefragte USt-IdNr. ist gültig.';
                break;
            case 201:
                $this->cAntwortInfo_arr['cFehlerNachricht'] = 'Die angefragte USt-IdNr. ist ungültig.';
                break;
            case 202:
                $this->cAntwortInfo_arr['cFehlerNachricht'] = 'Die angefragte USt-IdNr. ist ungültig. Sie ist nicht in der Unternehmerdatei des betreffenden EU-Mitgliedstaates registriert.';
                break;
            case 203:
                $this->cAntwortInfo_arr['cFehlerNachricht'] = 'Die angefragte USt-IdNr. ist ungültig. Sie ist erst ab dem ... gültig (siehe Feld "Gueltig_ab"). ';
                break;
            case 204:
                $this->cAntwortInfo_arr['cFehlerNachricht'] = 'Die angefragte USt-IdNr. ist ungültig. Sie war im Zeitraum von ... bis ... gültig (siehe Feld "Gueltig_ab" und "Gueltig_bis").';
                break;
            case 205:
                $this->cAntwortInfo_arr['cFehlerNachricht'] = 'Ihre Anfrage kann derzeit durch den angefragten EU-Mitgliedstaat oder aus anderen Gründen nicht beantwortet werden. Bitte versuchen Sie es später noch einmal. Bei wiederholten Problemen wenden Sie sich bitte an das Bundeszentralamt für Steuern - Dienstsitz Saarlouis.';
                break;
            case 206:
                $this->cAntwortInfo_arr['cFehlerNachricht'] = 'Ihre deutsche USt-IdNr. ist ungültig. Eine Bestätigungsanfrage ist daher nicht möglich. Den Grund hierfür können Sie beim Bundeszentralamt für Steuern - Dienstsitz Saarlouis - erfragen.';
                break;
            case 207:
                $this->cAntwortInfo_arr['cFehlerNachricht'] = 'Ihnen wurde die deutsche USt-IdNr. ausschliesslich zu Zwecken der Besteuerung des innergemeinschaftlichen Erwerbs erteilt. Sie sind somit nicht berechtigt, Bestätigungsanfragen zu stellen.';
                break;
            case 208:
                $this->cAntwortInfo_arr['cFehlerNachricht'] = 'Für die von Ihnen angefragte USt-IdNr. läuft gerade eine Anfrage von einem anderen Nutzer. Eine Bearbeitung ist daher nicht möglich. Bitte versuchen Sie es später noch einmal.';
                break;
            case 209:
                $this->cAntwortInfo_arr['cFehlerNachricht'] = 'Die angefragte USt-IdNr. ist ungültig. Sie entspricht nicht dem Aufbau der für diesen EU-Mitgliedstaat gilt.';
                break;
            case 210:
                $this->cAntwortInfo_arr['cFehlerNachricht'] = 'Die angefragte USt-IdNr. ist ungültig. Sie entspricht nicht den Prüfziffernregeln die für diesen EU-Mitgliedstaat gelten.';
                break;
            case 211:
                $this->cAntwortInfo_arr['cFehlerNachricht'] = 'Die angefragte USt-IdNr. ist ungültig. Sie enthält unzulässige Zeichen.';
                break;
            case 212:
                $this->cAntwortInfo_arr['cFehlerNachricht'] = 'Die angefragte USt-IdNr. ist ungültig. Sie enthält ein unzulässiges Länderkennzeichen.';
                break;
            case 213:
                $this->cAntwortInfo_arr['cFehlerNachricht'] = 'Die Abfrage einer deutschen USt-IdNr. ist nicht möglich.';
                break;
            case 214:
                $this->cAntwortInfo_arr['cFehlerNachricht'] = 'Ihre deutsche USt-IdNr. ist fehlerhaft. Sie beginnt mit "DE" gefolgt von 9 Ziffern.';
                break;
            case 215:
                $this->cAntwortInfo_arr['cFehlerNachricht'] = 'Ihre Anfrage enthält nicht alle notwendigen Angaben für eine einfache Bestätigungsanfrage (Ihre deutsche USt-IdNr. und die ausl. USt-IdNr.). Ihre Anfrage kann deshalb nicht bearbeitet werden.';
                break;
            case 216:
                $this->cAntwortInfo_arr['cFehlerNachricht'] = 'Ihre Anfrage enthält nicht alle notwendigen Angaben für eine qualifizierte Bestätigungsanfrage (Ihre deutsche USt-IdNr., die ausl. USt-IdNr., Firmenname einschl. Rechtsform und Ort).';
                break;
            case 217:
                $this->cAntwortInfo_arr['cFehlerNachricht'] = 'Bei der Verarbeitung der Daten aus dem angefragten EU-Mitgliedstaat ist ein Fehler aufgetreten. Ihre Anfrage kann deshalb nicht bearbeitet werden.';
                break;
            case 218:
                $this->cAntwortInfo_arr['cFehlerNachricht'] = 'Eine qualifizierte Bestätigung ist zur Zeit nicht möglich. Es wurde eine einfache Bestätigungsanfrage mit folgendem Ergebnis durchgeführt: Die angefragte USt-IdNr. ist gültig.';
                break;
            case 219:
                $this->cAntwortInfo_arr['cFehlerNachricht'] = 'Bei der Durchführung der qualifizierten Bestätigungsanfrage ist ein Fehler aufgetreten. Es wurde eine einfache Bestätigungsanfrage mit folgendem Ergebnis durchgeführt: Die angefragte USt-IdNr. ist gültig.';
                break;
            case 220:
                $this->cAntwortInfo_arr['cFehlerNachricht'] = 'Bei der Anforderung der amtlichen Bestätigungsmitteilung ist ein Fehler aufgetreten. Sie werden kein Schreiben erhalten.';
                break;
            case 999:
                $this->cAntwortInfo_arr['cFehlerNachricht'] = 'Eine Bearbeitung Ihrer Anfrage ist zurzeit nicht möglich. Bitte versuchen Sie es später noch einmal.';
                break;
        }
        $this->cAntwortInfo_arr['cFehlerNachricht'] = utf8_decode($this->cAntwortInfo_arr['cFehlerNachricht']);

        return $this;
    }

    /**
     * @param string $cUstID
     * @return stdClass
     */
    public function pruefeUstIDString($cUstID)
    {
        $cUstID            = strtoupper($cUstID);
        $cIDNummer         = substr($cUstID, 2, strlen($cUstID));
        $oReturn           = new stdClass();
        $oReturn->nRichtig = 0;
        $oReturn->cError   = '';

        switch (substr($cUstID, 0, 2)) {
            case 'AT':
                if (strpos($cIDNummer, 'U') !== 0) {
                    $oReturn->cError = 'ATU99999999';
                } elseif (preg_match('/^[0-9A-Z]{9}$/', $cIDNummer) !== 1) {
                    $oReturn->cError = 'ATU99999999';
                } else {
                    $oReturn->nRichtig = 1;
                }
                break;
            case 'BE':
                if (preg_match('/^[0-9]{10}$/', $cIDNummer) !== 1) {
                    $oReturn->cError = 'BE0999999999 oder BE999999999';
                } else {
                    $oReturn->nRichtig = 1;
                }
                break;
            case 'BG':
                if (preg_match('/^[0-9]{9,10}$/', $cIDNummer) !== 1) {
                    $oReturn->cError = 'BG999999999 oder BG9999999999';
                } else {
                    $oReturn->nRichtig = 1;
                }
                break;
            case 'CY':
                if (preg_match('/^[0-9A-Z]{9}$/', $cIDNummer) !== 1) {
                    $oReturn->cError = 'CY99999999L';
                } else {
                    $oReturn->nRichtig = 1;
                }
                break;
            case 'CZ':
                if (preg_match('/^[0-9]{8,10}$/', $cIDNummer) !== 1) {
                    $oReturn->cError = 'CZ99999999 oder CZ999999999 oder CZ9999999999';
                } else {
                    $oReturn->nRichtig = 1;
                }
                break;
            case 'DE':
                if (preg_match('/^[0-9]{9}$/', $cIDNummer) !== 1) {
                    $oReturn->cError = 'DE999999999';
                } else {
                    $oReturn->nRichtig = 1;
                }
                break;
            case 'DK':
                if (
                    (preg_match('/^[0-9]{8}$/', $cIDNummer) === 1) ||
                    (preg_match('/^[0-9]{2}[ ]{1}[0-9]{2}[ ]{1}[0-9]{2}[ ]{1}[0-9]{2}$/', $cIDNummer) === 1)
                ) {
                    $oReturn->nRichtig = 1;
                } else {
                    $oReturn->cError = 'DK99 99 99 99';
                }
                break;
            case 'EE':
                if (preg_match('/^[0-9]{9}$/', $cIDNummer) !== 1) {
                    $oReturn->cError = 'EE999999999';
                } else {
                    $oReturn->nRichtig = 1;
                }
                break;
            case 'EL':
                if (preg_match('/^[0-9]{9}$/', $cIDNummer) !== 1) {
                    $oReturn->cError = 'EL999999999';
                } else {
                    $oReturn->nRichtig = 1;
                }
                break;
            case 'ES':
                if (preg_match('/^[0-9A-Z]{9}$/', $cIDNummer) !== 1) {
                    $oReturn->cError = 'ESX9999999X';
                } else {
                    $oReturn->nRichtig = 1;
                }
                break;
            case 'FI':
                if (preg_match('/^[0-9]{8}$/', $cIDNummer) !== 1) {
                    $oReturn->cError = 'FI99999999';
                } else {
                    $oReturn->nRichtig = 1;
                }
                break;
            case 'FR':
                if (preg_match('/^[0-9]{11}$/', $cIDNummer) !== 1) {
                    $oReturn->cError = 'FR99999999999';
                } else {
                    $oReturn->nRichtig = 1;
                }
                break;
            case 'GB':
                if (
                    (preg_match('/^[0-9]{9}$/', $cIDNummer) === 1) ||
                    (preg_match('/^[0-9]{12}$/', $cIDNummer) === 1) ||
                    (preg_match('/^[0-9]{3}[ ]{1}[0-9]{4}[ ]{1}[0-9]{2}$/', $cIDNummer) === 1) ||
                    (preg_match('/^[0-9]{3}[ ]{1}[0-9]{4}[ ]{1}[0-9]{2}[0-9]{3}$/', $cIDNummer) === 1) ||
                    (preg_match('/^[0-9A-Z]{5}$/', $cIDNummer) === 1)
                ) {
                    $oReturn->nRichtig = 1;
                } else {
                    $oReturn->cError = 'GB999999999 oder GB999999999999 GB999 9999 99 oder GB999 9999 99 999 oder GBGD999 oder GBHA999';
                }
                break;
            case 'HU':
                if (preg_match('/^[0-9]{8}$/', $cIDNummer) !== 1) {
                    $oReturn->cError = 'HU99999999';
                } else {
                    $oReturn->nRichtig = 1;
                }
                break;
            case 'IE':
                if (preg_match('/^[0-9A-Z]{8}$/', $cIDNummer) !== 1) {
                    $oReturn->cError = 'IE9S99999L';
                } else {
                    $oReturn->nRichtig = 1;
                }
                break;
            case 'IT':
                if (preg_match('/^[0-9]{11}$/', $cIDNummer) !== 1) {
                    $oReturn->cError = 'IT99999999999';
                } else {
                    $oReturn->nRichtig = 1;
                }
                break;
            case 'LT':
                if (preg_match('/^[0-9]{9,12}$/', $cIDNummer) !== 1) {
                    $oReturn->cError = 'LT999999999 oder LT999999999999';
                } else {
                    $oReturn->nRichtig = 1;
                }
                break;
            case 'LU':
                if (preg_match('/^[0-9]{8}$/', $cIDNummer) !== 1) {
                    $oReturn->cError = 'LU99999999';
                } else {
                    $oReturn->nRichtig = 1;
                }
                break;
            case 'LV':
                if (preg_match('/^[0-9]{11}$/', $cIDNummer) !== 1) {
                    $oReturn->cError = 'LV99999999999';
                } else {
                    $oReturn->nRichtig = 1;
                }
                break;
            case 'MT':
                if (preg_match('/^[0-9]{8}$/', $cIDNummer) !== 1) {
                    $oReturn->cError = 'MT99999999';
                } else {
                    $oReturn->nRichtig = 1;
                }
                break;
            case 'NL':
                if (preg_match('/^[0-9A-Z]{12}$/', $cIDNummer) !== 1) {
                    $oReturn->cError = 'NL999999999B99';
                } else {
                    $oReturn->nRichtig = 1;
                }
                break;
            case 'PL':
                if (preg_match('/^[0-9]{10}$/', $cIDNummer) !== 1) {
                    $oReturn->cError = 'PL9999999999';
                } else {
                    $oReturn->nRichtig = 1;
                }
                break;
            case 'PT':
                if (preg_match('/^[0-9]{9}$/', $cIDNummer) !== 1) {
                    $oReturn->cError = 'PT999999999';
                } else {
                    $oReturn->nRichtig = 1;
                }
                break;
            case 'RO':
                if (preg_match('/^[0-9]{2,10}$/', $cIDNummer) !== 1) {
                    $oReturn->cError = 'RO999999999';
                } else {
                    $oReturn->nRichtig = 1;
                }
                break;
            case 'SE':
                if (preg_match('/^[0-9]{12}$/', $cIDNummer) !== 1) {
                    $oReturn->cError = 'SE999999999999';
                } else {
                    $oReturn->nRichtig = 1;
                }
                break;
            case 'SI':
                if (preg_match('/^[0-9]{8}$/', $cIDNummer) !== 1) {
                    $oReturn->cError = 'SI99999999';
                } else {
                    $oReturn->nRichtig = 1;
                }
                break;
            case 'SK':
                if (preg_match('/^[0-9]{10}$/', $cIDNummer) !== 1) {
                    $oReturn->cError = 'SK9999999999';
                } else {
                    $oReturn->nRichtig = 1;
                }
                break;
            default:
                $oReturn->nRichtig = 1;
                break;
        }

        return $oReturn;
    }
    
    /**
     * Trims and encodes parameter for use with XML-RPC-API. 
     * Requirements for special chars: http://evatr.bff-online.de/eVatR/xmlrpc/faq_xmlrpc#f11
     * 
     * @param the $param to encode
     * @return urlencoded param with some specials
     */
    function urlencode_special($param) {
        $param = str_replace('+', '%2B', trim($param));
        $param = urlencode($param);
        $param = str_replace('.', '%2E', $param);
        $param = str_replace('-', '%2D', $param);
        return $param;
    }
}