<?php

/**
 * Class ArrayToXML
 */
class ArrayToXML
{
    /**
     * @var string
     */
    public $text;
    public $arrays, $keys, $node_flag, $depth, $xml_parser;

    /**
     * The main function for converting to an XML document.
     * Pass in a multi dimensional array and this recrusively loops through and builds up an XML document.
     *
     * @param array            $data
     * @param SimpleXMLElement $xml - should only be used recursively
     * @return string XML
     */
    public function toXML1($data, $xml = null)
    {
        // turn off compatibility mode as simple xml throws a wobbly if you don't.
        if (ini_get('zend.ze1_compatibility_mode') == 1) {
            ini_set('zend.ze1_compatibility_mode', 0);
        }

        if ($xml === null) {
            $xml = simplexml_load_string("<?xml version='1.0' encoding='iso-8859-1'?><Document />");
        }

        // loop through the data passed in.
        foreach ($data as $key => $value) {
            // no numeric keys in our xml please!
            if (is_numeric($key)) {
                $key = 'Item';
            }

            // replace anything not alpha numeric
            $key = preg_replace('/[^a-z]/i', '', $key);

            // if there is another array found recursively call this function
            if (is_array($value)) {
                $node = $xml->addChild($key);
                // recrusive call.
                $this->toXML1($value, $node);
            } else {
                // add single node.
                $value = utf8_encode(StringHandler::htmlentitydecode($value));
                $xml->addChild($key, $value);
            }
        }

        // pass back as string. or simple xml object if you want!
        return $xml->asXML();
    }

    /**
     * Converts an array to an xml string
     *
     * @param array $array
     * @return string
     */
    public function toXML2($array)
    {
        $this->text = "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?><Document>";
        $this->arrayTransform($array);
        $this->text .= "</Document>";

        return $this->text;
    }

    /**
     * @param array $array
     */
    public function arrayTransform($array)
    {
        // key: element name; value: element value
        foreach ($array as $key => $value) {
            if (!is_array($value)) {
                $this->text .= "<$key>$value</$key>";
            } else {
                if (is_numeric($key)) {
                    $key = 'Item';
                }
                $this->text .= "<$key>";
                $this->arrayTransform($value);
                $this->text .= "</$key>";
            }
        }
    }
}

include __DIR__ . '/class/safetypayProxyAPI.php';

$proxySTP = new safetypayProxy();

$GetAmount = str_replace(',', '', (isset($_REQUEST['amount']) ? $_REQUEST['amount'] : $_GET['amount']));
$GetCurr   = (isset($_REQUEST['curr']) ? $_REQUEST['curr'] : $_GET['curr']);
$GetTOCurr = (isset($_REQUEST['tocurr']) ? $_REQUEST['tocurr'] : $_GET['tocurr']);

if (strlen($GetAmount) === 0) {
    $GetAmount = str_replace(',', '', (isset($_REQUEST['stp_totalamount']) ? $_REQUEST['stp_totalamount'] : $_GET['stp_totalamount']));
}
if (strlen($GetCurr) === 0) {
    $GetCurr = (isset($_REQUEST['stp_defaultcurrency']) ? $_REQUEST['stp_defaultcurrency'] : $_GET['stp_defaultcurrency']);
}
if (strlen($GetTOCurr) === 0) {
    $GetTOCurr = (isset($_REQUEST['stp_currencies']) ? $_REQUEST['stp_currencies'] : $_GET['stp_currencies']);
}

// SAFETYPAY_APIKEY, SAFETYPAY_SIGNTATURE_KEY und Umgebungseinstellung aus der DB laden
if (empty($GLOBALS['DB'])) {
    //einstellungen holen
    require_once __DIR__ . '/../../config.JTL-Shop.ini.php';

    //existiert Konfiguration?
    if (!defined('DB_HOST')) {
        die('Kein MySql-Datenbank Host angegeben. Bitte config.JTL-Shop.ini.php bearbeiten!');
    }
    if (!defined('DB_NAME')) {
        die('Kein MySql Datenbanknamen angegeben. Bitte config.JTL-Shop.ini.php bearbeiten!');
    }
    if (!defined('DB_USER')) {
        die('Kein MySql-Datenbank Benutzer angegeben. Bitte config.JTL-Shop.ini.php bearbeiten!');
    }
    if (!defined('DB_PASS')) {
        die('Kein MySql-Datenbank Passwort angegeben. Bitte config.JTL-Shop.ini.php bearbeiten!');
    }

    //datenbankverbindung aufbauen
    require_once __DIR__ . '/../../../classes/core/class.core.NiceDB.php';
    $DB = new NiceDB(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    $einstellungApiKey       = $DB->executeQuery("SELECT cWert FROM teinstellungen WHERE cName = 'zahlungsart_safetypay_apikey'", 1);
    $einstellungSignatureKey = $DB->executeQuery("SELECT cWert FROM teinstellungen WHERE cName = 'zahlungsart_safetypay_signaturekey'", 1);
    $einstellungUmgebung     = $DB->executeQuery("SELECT cWert FROM teinstellungen WHERE cName = 'zahlungsart_safetypay_testumgebung'", 1);
} else {
    $einstellungApiKey       = Shop::DB()->query("SELECT cWert FROM teinstellungen WHERE cName = 'zahlungsart_safetypay_apikey'", 1);
    $einstellungSignatureKey = Shop::DB()->query("SELECT cWert FROM teinstellungen WHERE cName = 'zahlungsart_safetypay_signaturekey'", 1);
    $einstellungUmgebung     = Shop::DB()->query("SELECT cWert FROM teinstellungen WHERE cName = 'zahlungsart_safetypay_testumgebung'", 1);
}

if (!empty($einstellungApiKey) && !empty($einstellungApiKey->cWert) && !empty($einstellungSignatureKey) && !empty($einstellungSignatureKey->cWert)) {
    $proxySTP->LetKeys($einstellungApiKey->cWert, $einstellungSignatureKey->cWert);
}

if (!empty($einstellungUmgebung)) {
    $proxySTP->SetEnvironment($einstellungUmgebung->cWert);
}

$ResultBanks  = $proxySTP->GetBanks((($GetTOCurr !== '') ? $GetTOCurr : DEFAULT_CURRENCY));
$ResultCQuote = $proxySTP->CalculationQuote((($GetCurr !== '') ? $GetCurr : DEFAULT_CURRENCY), $GetAmount, $GetTOCurr);
$Result       = $ResultBanks;
if (isset($ResultCQuote['FxCalculationQuote']['ToAmount'])) {
    $Result = array_merge(
        $ResultBanks,
        ['ReferenceNo' => $ResultCQuote['FxCalculationQuote']['ReferenceNo']],
        ['ToAmount'    => $ResultCQuote['FxCalculationQuote']['ToAmount']],
        ['Code'        => $ResultCQuote['FxCalculationQuote']['ToCurrency']['Code']]
    );
}

header('Content-Type: text/xml');

if (is_array($Result)) {
    if (PHP_VERSION >= '5.0.0') {
        echo ArrayToXML::toXML1($Result);
    } else {
        $objXML = new ArrayToXML;
        echo $objXML->toXML2($Result);
    }
}
