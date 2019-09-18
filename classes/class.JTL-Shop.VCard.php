<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class VCard
 * @property stdClass ADR
 * @property vCard AGENT
 * @property DateTime BDAY
 * @property string|stdClass EMAIL
 * @property stdClass FN
 * @property string GENDER
 * @property string|stdClass LABEL
 * @property stdClass N
 * @property string NAME
 * @property stdClass ORG
 * @property string|stdClass TEL
 * @property string TITLE
 * @property string URL
 * @property string VERSION

 * @property int mode
 */
class VCard
{
    const NL             = "\n";
    const OPT_ERR_IGNORE = 0x01;
    const OPT_ERR_RAISE  = 0x02;

    const MODE_UNKNOWN   = 0x00;
    const MODE_SINGLE    = 0x01;
    const MODE_MULTIPLE  = 0x02;

    const ERR_INVALID    = 0x01;

    /**
     * @var string
     */
    private $rawVCard;

    /**
     * @var array
     */
    private $data     = [];

    /**
     * @var int
     */
    private $mode     = self::MODE_UNKNOWN;

    /**
     * @var int
     */
    private $iVCard   = 0;

    /**
     * @var array
     */
    private $options = [
        'handling' => self::OPT_ERR_IGNORE,
    ];

    /**
     * @var array
     */
    private static $elementsStructured = [
        'n'   => ['LastName', 'FirstName', 'AdditionalNames', 'Prefixes', 'Suffixes'],
        'adr' => ['POBox', 'ExtendedAddress', 'StreetAddress', 'Locality', 'Region', 'PostalCode', 'Country'],
        'geo' => ['Latitude', 'Longitude'],
        'org' => ['Name', 'Unit1', 'Unit2'],
    ];

    /**
     * @var array
     */
    private static $elementsMultiple = [
        'nickname',
        'categories'
    ];

    /**
     * @var array
     */
    private static $elementsType = [
        'email' => ['internet', 'x400', 'pref', 'work', 'home'],
        'adr'   => ['dom', 'intl', 'postal', 'parcel', 'home', 'work', 'pref'],
        'label' => ['dom', 'intl', 'postal', 'parcel', 'home', 'work', 'pref'],
        'tel'   => ['home', 'msg', 'work', 'pref', 'voice', 'fax', 'cell', 'video', 'pager', 'bbs', 'modem', 'car', 'isdn', 'pcs'],
        'impp'  => ['personal', 'business', 'home', 'work', 'mobile', 'pref']
    ];
    /**
     * @var array
     */
    private static $elementsFile = [
        'photo',
        'logo',
        'sound'
    ];

    /**
     * VCard constructor.
     * @param string $vcardData
     * @param array|null $options
     */
    public function __construct($vcardData, array $options = null)
    {
        $this->rawVCard = $vcardData;

        if ($options !== null) {
            $this->options = array_merge($this->options, $options);
        }

        $this->parseVCard();
    }

    /**
     * creates new instance from self
     * @param string $vcardData
     * @return VCard
     */
    protected function createInstance($vcardData)
    {
        $className = get_called_class();

        return new $className($vcardData, $this->options);
    }

    /**
     * @param string $encodedStr
     * @return string
     */
    protected static function correctEncoding($encodedStr)
    {
        // das erste BASE64 final = sign muss maskiert werden, damit es vom nachfolgenden replace nicht ersetzt wird
        $encodedStr = preg_replace('{(\n\s.+)=(\n)}', '$1-base64=-$2', $encodedStr);

        // verketten von Zeilen, die mit einem hard wrap getrennt sind (quoted-printable-encoded values in v2.1 vCards)
        $encodedStr = str_replace("=\n", '', $encodedStr);

        // verketten von Zeilen, die mit einem soft wrap getrennt sind (space oder tab am Anfang der nächsten Zeile
        $encodedStr = str_replace(["\n ", "\n\t"], '-wrap-', $encodedStr);

        // das erste BASE64 final = sign aus der Maskierung wiederherstellen
        $encodedStr = str_replace("-base64=-\n", "=\n", $encodedStr);

        return $encodedStr;
    }

    /**
     * removes escaping slashes
     * @param string $str
     * @return string
     */
    protected static function unescape($str)
    {
        return str_replace(
            ['\:', '\;', '\,', self::NL],
            [':', ';', ',', ''],
            $str
        );
    }

    /**
     * parses parameters
     * @param string $key
     * @param array|null $rawParams
     * @return array
     */
    protected static function parseParameters($key, array $rawParams = null)
    {
        $result = [];
        $type   = [];

        if ($rawParams === null) {
            return $result;
        }

        // Parameter in (key, value) pairs aufteilen
        $params = [];
        foreach ($rawParams as $item) {
            $params[] = explode('=', strtolower($item));
        }

        foreach ($params as $index => $param) {
            // leere Element überspringen
            if (empty($param)) {
                continue;
            }

            if (count($param) === 1) {
                // prüfen ob der typ für den Parameter erlaubt ist (Ausnahme: email)
                if (($key === 'email' && is_scalar($param[0])) ||
                    (isset(self::$elementsType[$key]) && in_array($param[0], self::$elementsType[$key]))
                ) {
                    $type[] = $param[0];
                }
            } elseif (count($param) > 2) {
                $tmpTypeParams = self::parseParameters($key, explode(',', $rawParams[$index]));

                if ($tmpTypeParams['type']) {
                    $type = array_merge($type, $tmpTypeParams['type']);
                }
            } else {
                switch ($param[0]) {
                    case 'encoding':
                        if (in_array($param[1], ['quoted-printable', 'b', 'base64'], true)) {
                            $result['encoding'] = $param[1] === 'base64' ? 'b' : $param[1];
                        }
                        break;
                    case 'charset':
                        $result['charset'] = $param[1];
                        break;
                    case 'type':
                        $type = array_merge($type, explode(',', $param[1]));
                        break;
                    case 'value':
                        if (strtolower($param[1]) === 'url') {
                            $result['encoding'] = 'uri';
                        }
                        break;
                }
            }
        }

        $result['type'] = $type;

        return $result;
    }

    /**
     * separates  various parts of a structured value
     * @param string $key
     * @param string $rawValue
     * @return array
     */
    protected static function parseStructuredValue($key, $rawValue)
    {
        $result = [];
        $txtArr = array_map('trim', explode(';', $rawValue));

        foreach (self::$elementsStructured[$key] as $index => $structurePart) {
            $result[$structurePart] = isset($txtArr[$index]) ? $txtArr[$index] : null;
        }

        return $result;
    }

    /**
     * separates multiple text values
     * @param string $rawValue
     * @return array
     */
    protected static function parseMultipleTextValue($rawValue)
    {
        return explode(',', $rawValue);
    }

    /**
     * parses a vcard line
     * @param string $line
     */
    protected function parseVCardLine($line)
    {
        // Jede Zeile in zwei Teile trennen. key enthält den Elementnamen und ggfs. weitere Parameter, value ist der Wert
        list($key, $rawValue) = explode(':', $line, 2);

        $key = strtolower(trim(self::unescape($key)));
        if (in_array($key, ['begin', 'end'], true)) {
            // begin und end müssen nicht weiter geparst werden
            return;
        }

        if ((strpos($key, 'agent') === 0) && (stripos($rawValue, 'begin:vcard') !== false)) {
            $vCard = $this->createInstance(str_replace('-wrap-', "\n", $rawValue));

            if (!isset($this->data[$key])) {
                $this->data[$key] = [];
            }
            $this->data[$key][] = $vCard;

            return;
        }

        $rawValue = trim(self::unescape(str_replace('-wrap-', '', $rawValue)));
        $keyParts = explode(';', $key);
        $key      = $keyParts[0];
        $encoding = false;
        $type     = false;
        //$value    = null;

        if (strpos($key, 'item') === 0) {
            $tmpKey  = explode('.', $key, 2);
            $key     = $tmpKey[1];
        }

        if (count($keyParts) > 1) {
            $params = self::parseParameters($key, array_slice($keyParts, 1));

            foreach ($params as $paramKey => $paramValue) {
                switch ($paramKey) {
                    case 'encoding':
                        $encoding = $paramValue;

                        if (in_array($paramValue, ['b', 'base64'], true)) {
                            //$rawValue = base64_decode($Value);
                        } elseif ($paramValue === 'quoted-printable') { // v2.1
                            $rawValue = quoted_printable_decode($rawValue);
                        }
                        break;
                    case 'charset': // v2.1
                        if ($paramValue !== 'utf-8' && $paramValue !== 'utf8') {
                            $rawValue = mb_convert_encoding($rawValue, 'UTF-8', $paramValue);
                        }
                        break;
                    case 'type':
                        $type = $paramValue;
                        break;
                }
            }
        }

        // prüfe auf zusätzliche Doppelpunk getrennte parameter (z.B. Apples "X-ABCROP-RECTANGLE" für photos)
        if (isset($params['encoding']) &&
            in_array($key, self::$elementsFile, true) &&
            in_array($params['encoding'], ['b', 'base64'], true)
        ) {
            // wenn ein Doppelpunkt vorhanden ist, dann gibt es zusätzliche Address Book paremeter,
            // da ein ":" kein gültiges Zeichen in Base64 ist
            if (strpos($rawValue, ':') !== false) {
                $rawValue = array_pop(explode(':', $rawValue));
            }
        }

        if (isset(self::$elementsStructured[$key])) {
            $value = self::parseStructuredValue($key, $rawValue);
            if ($type) {
                $value['Type'] = $type;
            }
        } else {
            $value = in_array($key, self::$elementsMultiple, true)
                ? self::parseMultipleTextValue($rawValue)
                : $rawValue;

            if ($type && isset($value)) {
                $value = [
                    'Value' => $value,
                    'Type'  => $type
                ];
            }
        }

        if (isset($value) && is_array($value) && $encoding) {
            $value['Encoding'] = $encoding;
        }

        if (!isset($this->data[$key])) {
            $this->data[$key] = [];
        }

        $this->data[$key][] = isset($value) ? $value : $rawValue;
    }

    /**
     * parses vcard
     * @throws Exception
     */
    protected function parseVCard()
    {
        $beginCount = preg_match_all('{^BEGIN\:VCARD}miS', $this->rawVCard);
        $endCount   = preg_match_all('{^END\:VCARD}miS', $this->rawVCard);

        if (($beginCount !== $endCount) || !$beginCount) {
            if ($this->options['handling'] == self::OPT_ERR_RAISE) {
                throw new Exception('vCard: invalid vCard', self::ERR_INVALID);
            }

            $this->mode = self::MODE_UNKNOWN;
        } else {
            $this->mode = $beginCount === 1 ? self::MODE_SINGLE : self::MODE_MULTIPLE;
        }

        if ($this->mode !== self::MODE_UNKNOWN) {
            // newlines vereinheitlichen, mehrfache entfernen
            $this->rawVCard = str_replace("\r", self::NL, $this->rawVCard);
            $this->rawVCard = preg_replace('{(\n+)}', self::NL, $this->rawVCard);

            if ($this->mode == self::MODE_MULTIPLE) {
                $this->rawVCard = explode('BEGIN:VCARD', $this->rawVCard);
                // leere Einträge entfernen
                $this->rawVCard = array_filter($this->rawVCard);

                foreach ($this->rawVCard as $singleRawVCard) {
                    // BEGIN:VCARD für jede Sub-VCard wiedervoranstellen, da dies als Trenner verwendet wurde
                    $singleRawVCard = 'BEGIN:VCARD' . self::NL . $singleRawVCard;
                    $this->data[]   = $this->createInstance($singleRawVCard);
                }
            } else {
                $lines = explode(self::NL, self::correctEncoding($this->rawVCard));

                foreach ($lines as $line) {
                    // Zeilen ohne Doppelpunkt überspringen
                    if (strpos($line, ':') === false) {
                        continue;
                    }

                    $this->parseVCardLine($line);
                }
            }
        }
    }

    /**
     * Magic method to get the various vCard values as object members
     * @param string $property
     * @return mixed
     */
    public function __get($property)
    {
        $property = strtolower($property);

        if ($property === 'mode') {
            return $this->mode;
        }

        switch ($this->mode) {
            case self::MODE_MULTIPLE:
                $propData = $this->data[$this->iVCard];
                break;
            case self::MODE_SINGLE:
                $propData = $this->data;
                break;
            case self::MODE_UNKNOWN:
            default:
                return null;
        }

        if (isset($propData[$property])) {
            if ($property === 'agent') {
                return $propData[$property];
            } elseif ($property === 'bday') {
                $bDay = is_array($propData[$property]) && count($propData[$property]) > 0 ? $propData[$property][0] : $propData[$property];
                if (is_numeric($bDay)) {
                    return DateTime::createFromFormat('YmdHis', (string)$bDay . '000000');
                }

                return DateTime::createFromFormat('Y-m-d H:i:s', $bDay . '00:00:00');
            } elseif (in_array($property, self::$elementsFile)) {
                $result = $propData[$property];

                foreach ($result as $key => $value) {
                    if (stripos($value['Value'], 'uri:') === 0) {
                        $result[$key]['Value']    = substr($value, 4);
                        $result[$key]['Encoding'] = 'uri';
                    }
                }

                return $result;
            }

            if (is_array($propData[$property]) && (count($propData[$property]) > 0)) {
                $result = new stdClass();

                foreach ($propData[$property] as $data) {
                    if (isset($data['Type']) && count($data['Type']) > 0) {
                        $key          = implode('_', $data['Type']);
                        $result->$key = (object)$data;

                        foreach ($data['Type'] as $key) {
                            $result->$key = (object)$data;
                        }
                    } else {
                        $result = is_array($data)
                            ? (object)$data
                            : $data;
                    }
                }

                return $result;
            }

            return $propData[$property];
        }

        return [];
    }

    /**
     * Magic method to test if vCard value is set
     * @param string $property
     * @return bool
     */
    public function __isset($property)
    {
        $property = strtolower($property);

        if ($property === 'mode') {
            return isset($this->mode);
        }

        switch ($this->mode) {
            case self::MODE_MULTIPLE:
                $propData = $this->data[$this->iVCard];
                break;
            case self::MODE_SINGLE:
                $propData = $this->data;
                break;
            case self::MODE_UNKNOWN:
            default:
                return false;
        }

        return isset($propData[$property]);
    }

    /**
     * @param mixed $item
     * @param array $subset
     * @param mixed $default
     * @param bool $checkSelf
     * @return mixed
     */
    public static function getValue($item, array $subset, $default = null, $checkSelf = true)
    {
        foreach ($subset as $sub) {
            if ($sub === '*' && (is_array($item) || is_object($item))) {
                foreach ($item as $key => $value) {
                    // nur den ersten Eintrag zurückgeben
                    $sub = $key;
                    break;
                }
            }

            if (isset($item->$sub)) {
                if (isset($item->$sub->Value)) {
                    return $item->$sub->Value;
                }
                if (is_array($item->$sub) && isset($item->$sub[0])) {
                    return $item->$sub[0];
                }
                if (is_string($item->$sub)) {
                    return $item->$sub;
                }
                if ($checkSelf) {
                    return $item->$sub;
                }
            }
        }

        if ($checkSelf) {
            if (isset($item->Value)) {
                return $item->Value;
            }
            if (is_array($item) && isset($item[0])) {
                return $item[0];
            }
            if (is_string($item)) {
                return $item;
            }
        }

        return $default;
    }

    /**
     * @param int $vCard
     * @return vCard
     */
    public function selectVCard($vCard)
    {
        $vCard        = (int)$vCard;
        $this->iVCard = 0;
        if ($this->mode === self::MODE_MULTIPLE && $vCard >= 0 && $vCard < count($this->data)) {
            $this->iVCard = $vCard;
        }

        return $this;
    }

    /**
     * @return stdClass
     */
    public function asKunde()
    {
        $Kunde = new stdClass();

        if (isset($this->GENDER)) {
            $Kunde->cAnrede = $this->GENDER == 'F' ? 'w' : 'm';
        }

        if (isset($this->N)) {
            if (!empty($this->N->Prefixes)) {
                if (is_array($this->N->Prefixes)) {
                    // Workaround fals prefix für Anrede genutzt wird
                    if (in_array(Shop::Lang()->get('salutationM', 'global'), $this->N->Prefixes, true)) {
                        $Kunde->cAnrede = 'm';
                    } elseif (in_array(Shop::Lang()->get('salutationW', 'global'), $this->N->Prefixes, true)) {
                        $Kunde->cAnrede = 'w';
                    } else {
                        $Kunde->cTitel = implode(' ', $this->N->Prefixes);
                    }
                } else {
                    // Workaround fals prefix für Anrede genutzt wird
                    if (Shop::Lang()->get('salutationM', 'global') === $this->N->Prefixes) {
                        $Kunde->cAnrede = 'm';
                    } elseif (Shop::Lang()->get('salutationW', 'global') === $this->N->Prefixes) {
                        $Kunde->cAnrede = 'w';
                    } else {
                        $Kunde->cTitel = $this->N->Prefixes;
                    }
                }
            }

            $Kunde->cVorname  = isset($this->N->FirstName) ? $this->N->FirstName : '';
            $Kunde->cNachname = isset($this->N->LastName) ? $this->N->LastName : '';
        }

        if (isset($this->ADR)) {
            $adr = self::getValue($this->ADR, ['home', 'work', '*'], null, true);

            $Kunde->cStrasse      = isset($adr->StreetAddress) ? $adr->StreetAddress : '';
            $Kunde->cAdressZusatz = isset($adr->ExtendedAddress) ? $adr->ExtendedAddress : '';
            $Kunde->cPLZ          = isset($adr->PostalCode) ? $adr->PostalCode : '';
            $Kunde->cOrt          = isset($adr->Locality) ? $adr->Locality : '';
            $Kunde->cBundesland   = isset($adr->Region) ? $adr->Region : '';
            $Kunde->cLand         = isset($adr->Country) ? landISO($adr->Country) : '';

            if (preg_match('/^(.*)[\. ]*([0-9]+[a-zA-Z]?)$/U', $Kunde->cStrasse, $hits)) {
                $Kunde->cStrasse    = $hits[1];
                $Kunde->cHausnummer = $hits[2];
            } else {
                $Kunde->cHausnummer = '';
            }
        }

        if (isset($this->TEL)) {
            $Kunde->cMobil = self::getValue($this->TEL, ['cell'], '', false);
            $Kunde->cFax   = self::getValue($this->TEL, ['fax', 'home_fax', 'fax_home', 'work_fax', 'fax_work'], '', false);
            $Kunde->cTel   = self::getValue($this->TEL, ['home', 'work', '*'], '', true);
        }

        if (isset($this->EMAIL)) {
            $Kunde->cMail = self::getValue($this->EMAIL, ['home', 'work', '*'], '', true);
        }

        if (isset($this->URL)) {
            $Kunde->cWWW = self::getValue($this->URL, ['home', 'work', '*'], '', true);
        }

        if (isset($this->BDAY)) {
            $Kunde->dGeburtstag = $this->BDAY->format('d.m.Y');
        }

        // vCard-Daten liegen hier immer in UTF8 vor
        foreach ($Kunde as $property => $data) {
            $Kunde->$property = StringHandler::filterXSS(utf8_decode($data));
        }

        return $Kunde;
    }
}
