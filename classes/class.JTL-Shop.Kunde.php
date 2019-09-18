<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_BLOWFISH . 'xtea.class.php';
require_once PFAD_ROOT . PFAD_INCLUDES_LIBS . 'password_compat/password.php';

/**
 * Class Kunde
 */
class Kunde
{
    /**
     * @var int
     */
    public $kKunde;

    /**
     * @var int
     */
    public $kKundengruppe;

    /**
     * @var int
     */
    public $kSprache;

    /**
     * @var int
     */
    public $nRegistriert;

    /**
     * @var float
     */
    public $fRabatt = 0.00;

    /**
     * @var float
     */
    public $fGuthaben = 0.00;

    /**
     * @var string
     */
    public $cKundenNr;

    /**
     * @var string
     */
    public $cPasswort;

    /**
     * @var string
     */
    public $cAnrede = '';

    /**
     * @var string
     */
    public $cAnredeLocalized = '';

    /**
     * @var string
     */
    public $cTitel;

    /**
     * @var string
     */
    public $cVorname;

    /**
     * @var string
     */
    public $cNachname;

    /**
     * @var string
     */
    public $cFirma;

    /**
     * @var string
     */
    public $cStrasse = '';

    /**
     * @var string
     */
    public $cHausnummer;

    /**
     * @var string
     */
    public $cAdressZusatz;

    /**
     * @var string
     */
    public $cPLZ = '';

    /**
     * @var string
     */
    public $cOrt = '';

    /**
     * @var string
     */
    public $cBundesland = '';

    /**
     * @var string
     */
    public $cLand;

    /**
     * @var string
     */
    public $cTel;

    /**
     * @var string
     */
    public $cMobil;

    /**
     * @var string
     */
    public $cFax;

    /**
     * @var string
     */
    public $cMail = '';

    /**
     * @var string
     */
    public $cUSTID = '';

    /**
     * @var string
     */
    public $cWWW = '';

    /**
     * @var string
     */
    public $cSperre = 'N';

    /**
     * @var string
     */
    public $cNewsletter = '';

    /**
     * @var string
     */
    public $dGeburtstag = '0000-00-00';

    /**
     * @var string
     */
    public $dGeburtstag_formatted;

    /**
     * @var string
     */
    public $cHerkunft = '';

    /**
     * @var string
     */
    public $cAktiv;

    /**
     * @var string
     */
    public $cAbgeholt;

    /**
     * @var string
     */
    public $dErstellt = '0000-00-00';

    /**
     * @var string
     */
    public $dVeraendert;

    /**
     * @var array
     */
    public $cKundenattribut_arr;

    /**
     * @var string
     */
    public $cZusatz;

    /**
     * @var string
     */
    public $cGuthabenLocalized;

    /**
     * @var string
     */
    public $angezeigtesLand;

    /**
     * @var string
     */
    public $dErstellt_DE;

    /**
     * @var string
     */
    public $cPasswortKlartext;

    /**
     * @var int
     */
    public $nLoginversuche = 0;

    /**
     * Konstruktor
     *
     * @param int $kKunde - Falls angegeben, wird der Kunde mit angegebenem kKunde aus der DB geholt
     */
    public function __construct($kKunde = 0)
    {
        if ((int)$kKunde > 0) {
            $this->loadFromDB($kKunde);
        }
    }

    /**
     * get customer by email address
     *
     * @param string $cEmail
     * @return Kunde|null
     */
    public function holRegKundeViaEmail($cEmail)
    {
        if (strlen($cEmail) > 0) {
            $oKundeTMP = Shop::DB()->select('tkunde', 'cMail', StringHandler::filterXSS($cEmail), null, null, null, null, false, 'kKunde');

            if (isset($oKundeTMP->kKunde) && $oKundeTMP->kKunde > 0) {
                return new self($oKundeTMP->kKunde);
            }
        }

        return null;
    }

    /**
     * @param array $post
     * @return bool|int - true, if captcha verified or no captcha necessary
     */
    public function verifyLoginCaptcha($post)
    {
        $conf          = Shop::getSettings([CONF_KUNDEN]);
        $cBenutzername = $post['email'];
        if (isset($conf['kunden']['kundenlogin_max_loginversuche']) &&
            $conf['kunden']['kundenlogin_max_loginversuche'] !== '' &&
            $conf['kunden']['kundenlogin_max_loginversuche'] > 1 &&
            strlen($cBenutzername) > 0
        ) {
            $attempts = Shop::DB()->select(
                'tkunde',
                'cMail', StringHandler::filterXSS($cBenutzername),
                'nRegistriert', 1,
                null, null,
                false,
                'nLoginversuche'
            );
            if (isset($attempts->nLoginversuche) &&
                (int)$attempts->nLoginversuche >= (int)$conf['kunden']['kundenlogin_max_loginversuche']
            ) {
                if (validateCaptcha($_POST)) {
                    return true;
                }

                return (int)$attempts->nLoginversuche;
            }
        }

        return true;
    }

    /**
     * Setzt Kunde mit Daten aus der DB mit spezifiziertem Primary Key
     *
     * @param string $cBenutzername
     * @param string $cPasswort
     * @return int 1 = Alles O.K., 2 = Kunde ist gesperrt
     */
    public function holLoginKunde($cBenutzername, $cPasswort)
    {
        if (strlen($cBenutzername) > 0 && strlen($cPasswort) > 0) {
            $oUser = $this->checkCredentials($cBenutzername, $cPasswort);
            if ($oUser === false) {
                return 0;
            }
            if (isset($oUser->cSperre) && $oUser->cSperre === 'Y') {
                return 2; // Kunde ist gesperrt
            }
            if (isset($oUser->cAktiv) && $oUser->cAktiv === 'N') {
                return 3; // Kunde ist nicht aktiv
            }
            if (isset($oUser->kKunde) && $oUser->kKunde > 0) {
                foreach (get_object_vars($oUser) as $k => $v) {
                    $this->$k = $v;
                }
                $this->angezeigtesLand = ISO2land($this->cLand);
                $this->holeKundenattribute();
                //check if password has to be updated because of PASSWORD_DEFAULT method changes or using old md5 hash
                if ((isset($oUser->cPasswort) && password_needs_rehash($oUser->cPasswort, PASSWORD_DEFAULT)) &&
                    version_compare(Shop::getShopVersion(), 350, '>=') === true
                ) {
                    $_upd            = new stdClass();
                    $_upd->cPasswort = password_hash($cPasswort, PASSWORD_DEFAULT);
                    Shop::DB()->update('tkunde', 'kKunde', (int)$oUser->kKunde, $_upd);
                }
            }
            executeHook(HOOK_KUNDE_CLASS_HOLLOGINKUNDE, [
                'oKunde'        => &$this,
                'oUser'         => $oUser,
                'cBenutzername' => $cBenutzername,
                'cPasswort'     => $cPasswort
            ]);
            if ($this->kKunde > 0) {
                $this->entschluesselKundendaten();
                // Anrede mappen
                $this->cAnredeLocalized   = mappeKundenanrede($this->cAnrede, $this->kSprache);
                $this->cGuthabenLocalized = $this->gibGuthabenLocalized();

                return 1;
            }
        }

        return 0;
    }

    /**
     * @param string $cBenutzername
     * @param string $cPasswort
     * @return bool|stdClass
     */
    public function checkCredentials($cBenutzername, $cPasswort)
    {
        $cBenutzername = StringHandler::filterXSS($cBenutzername);
        // Work Around Passwort 32, 40 oder mehr Zeichen
        $oUser           = Shop::DB()->select(
            'tkunde',
            'cMail',
            $cBenutzername,
            'nRegistriert',
            1,
            null,
            null,
            false,
            'kKunde, cPasswort, cSperre, cAktiv, nLoginversuche'
        );
        $updatePassword  = false;
        $verify          = false;
        $oldPasswordHash = '';
        if (isset($oUser->cPasswort)) {
            if (strlen($oUser->cPasswort) === 32) { // Alter md5
                $oldPasswordHash = md5($cPasswort);
                $updatePassword  = true;
            } elseif (strlen($oUser->cPasswort) === 40) {  // Neuer Hash bis 4.0
                $cCrypted = cryptPasswort($cPasswort, $oUser->cPasswort);
                if (empty($cCrypted)) {
                    return false;
                }
                $oldPasswordHash = $cCrypted;
                $updatePassword  = true;
            } else { //ab 4.0
                $verify = password_verify($cPasswort, $oUser->cPasswort);
            }
            $oUser->kKunde         = (int)$oUser->kKunde;
            $oUser->nLoginversuche = (int)$oUser->nLoginversuche;
        }
        if ($updatePassword === true) {
            //get customer by mail and old password hash
            $obj = Shop::DB()->select(
                'tkunde',
                'cMail',
                $cBenutzername,
                'cPasswort',
                $oldPasswordHash,
                'kKunde',
                (int)$oUser->kKunde,
                false,
                '*, date_format(dGeburtstag, \'%d.%m.%Y\') AS dGeburtstag_formatted'
            );
        } elseif ($verify === true) {
            //get customer by mail since new hash verification was successful
            $obj = Shop::DB()->select(
                'tkunde',
                'kKunde',
                (int)$oUser->kKunde,
                null,
                null,
                null,
                null,
                false,
                '*, date_format(dGeburtstag, \'%d.%m.%Y\') AS dGeburtstag_formatted'
            );
            //reset unsuccessful login attempts
            if ($oUser->nLoginversuche > 0) {
                $upd = new stdClass();

                $upd->nLoginversuche = 0;
                Shop::DB()->update('tkunde', 'kKunde', (int)$oUser->kKunde, $upd);
            }
        } else {
            $obj = false;
            if (isset($oUser->nLoginversuche)) {
                //increment unsuccessful login attempts
                $this->nLoginversuche = (int)$oUser->nLoginversuche + 1;
                $_upd                 = new stdClass();
                $_upd->nLoginversuche = $this->nLoginversuche;
                Shop::DB()->update('tkunde', 'kKunde', (int)$oUser->kKunde, $_upd);
            }
        }
        if (isset($obj->kKunde)) {
            $obj->kKunde         = (int)$obj->kKunde;
            $obj->kKundengruppe  = (int)$obj->kKundengruppe;
            $obj->kSprache       = (int)$obj->kSprache;
            $obj->nLoginversuche = (int)$obj->nLoginversuche;
            $obj->nRegistriert   = (int)$obj->nRegistriert;
        }

        return $obj;
    }

    /**
     * @return string
     */
    public function gibGuthabenLocalized()
    {
        return gibPreisStringLocalized($this->fGuthaben);
    }

    /**
     * Setzt Kunde mit Daten aus der DB mit spezifiziertem Primary Key
     *
     * @param int $kKunde
     * @return $this
     */
    public function loadFromDB($kKunde)
    {
        $kKunde = (int)$kKunde;
        if ($kKunde > 0) {
            $obj = Shop::DB()->select('tkunde', 'kKunde', $kKunde);

            if (isset($obj->kKunde) && $obj->kKunde > 0) {
                $members = array_keys(get_object_vars($obj));
                if (is_array($members) && count($members) > 0) {
                    foreach ($members as $member) {
                        $this->$member = $obj->$member;
                    }
                }
                // Anrede mappen
                $this->cAnredeLocalized = mappeKundenanrede($this->cAnrede, $this->kSprache);
                $this->angezeigtesLand  = ISO2land($this->cLand);
                //$this->cLand = landISO($this->cLand);
                $this->holeKundenattribute();
                $this->entschluesselKundendaten();
                $this->kKunde         = (int)$this->kKunde;
                $this->kKundengruppe  = (int)$this->kKundengruppe;
                $this->kSprache       = (int)$this->kSprache;
                $this->nLoginversuche = (int)$this->nLoginversuche;
                $this->nRegistriert   = (int)$this->nRegistriert;

                $this->dGeburtstag_formatted = date_format(date_create($this->dGeburtstag), 'd.m.Y');
                $this->cGuthabenLocalized    = $this->gibGuthabenLocalized();
                $cDatum_arr                  = gibDatumTeile($this->dErstellt);
                $this->dErstellt_DE          = $cDatum_arr['cTag'] . '.' .
                    $cDatum_arr['cMonat'] . '.' .
                    $cDatum_arr['cJahr'];
                executeHook(HOOK_KUNDE_CLASS_LOADFROMDB);
            }
        }

        return $this;
    }

    /**
     * encrypt customer data
     *
     * @return $this
     */
    private function verschluesselKundendaten()
    {
        $this->cNachname = verschluesselXTEA(trim($this->cNachname));
        $this->cFirma    = verschluesselXTEA(trim($this->cFirma));
        $this->cZusatz   = verschluesselXTEA(trim($this->cZusatz));
        $this->cStrasse  = verschluesselXTEA(trim($this->cStrasse));

        return $this;
    }

    /**
     * decrypt customer data
     *
     * @return $this
     */
    private function entschluesselKundendaten()
    {
        $this->cNachname = trim(entschluesselXTEA($this->cNachname));
        $this->cFirma    = trim(entschluesselXTEA($this->cFirma));
        $this->cZusatz   = trim(entschluesselXTEA($this->cZusatz));
        $this->cStrasse  = trim(entschluesselXTEA($this->cStrasse));

        return $this;
    }

    /**
     * Fügt Datensatz in DB ein. Primary Key wird in this gesetzt.
     *
     * @return int - Key vom eingefügten Kunden
     */
    public function insertInDB()
    {
        executeHook(HOOK_KUNDE_DB_INSERT, ['oKunde' => &$this]);

        $this->verschluesselKundendaten();
        $obj                 = new stdClass();
        $obj->kKundengruppe  = $this->kKundengruppe;
        $obj->kSprache       = $this->kSprache;
        $obj->cKundenNr      = $this->cKundenNr;
        $obj->cPasswort      = $this->cPasswort;
        $obj->cAnrede        = $this->cAnrede;
        $obj->cTitel         = $this->cTitel;
        $obj->cVorname       = $this->cVorname;
        $obj->cNachname      = $this->cNachname;
        $obj->cFirma         = $this->cFirma;
        $obj->cZusatz        = $this->cZusatz;
        $obj->cStrasse       = $this->cStrasse;
        $obj->cHausnummer    = $this->cHausnummer;
        $obj->cAdressZusatz  = $this->cAdressZusatz;
        $obj->cPLZ           = $this->cPLZ;
        $obj->cOrt           = $this->cOrt;
        $obj->cBundesland    = $this->cBundesland;
        $obj->cLand          = $this->cLand;
        $obj->cTel           = $this->cTel;
        $obj->cMobil         = $this->cMobil;
        $obj->cFax           = $this->cFax;
        $obj->cMail          = $this->cMail;
        $obj->cUSTID         = $this->cUSTID;
        $obj->cWWW           = $this->cWWW;
        $obj->cSperre        = $this->cSperre;
        $obj->fGuthaben      = $this->fGuthaben;
        $obj->cNewsletter    = $this->cNewsletter;
        $obj->dGeburtstag    = $this->dGeburtstag;
        $obj->fRabatt        = $this->fRabatt;
        $obj->cHerkunft      = $this->cHerkunft;
        $obj->dErstellt      = $this->dErstellt;
        $obj->dVeraendert    = $this->dVeraendert;
        $obj->cAktiv         = $this->cAktiv;
        $obj->cAbgeholt      = $this->cAbgeholt;
        $obj->nRegistriert   = $this->nRegistriert;
        $obj->nLoginversuche = $this->nLoginversuche;

        if (empty($obj->dGeburtstag)) {
            $obj->dGeburtstag = '0000-00-00';
        }
        if (empty($obj->dVeraendert)) {
            $obj->dVeraendert = 'now()';
        }
        $obj->cLand   = $this->pruefeLandISO($obj->cLand);
        $this->kKunde = Shop::DB()->insert('tkunde', $obj);
        $this->entschluesselKundendaten();

        // Anrede mappen
        $this->cAnredeLocalized   = mappeKundenanrede($this->cAnrede, $this->kSprache);
        $this->cGuthabenLocalized = $this->gibGuthabenLocalized();
        $cDatum_arr               = gibDatumTeile($this->dErstellt);
        $this->dErstellt_DE       = $cDatum_arr['cTag'] . '.' . $cDatum_arr['cMonat'] . '.' . $cDatum_arr['cJahr'];

        return $this->kKunde;
    }

    /**
     * Updatet Daten in der DB. Betroffen ist der Datensatz mit gleichem Primary Key
     *
     * @return string
     */
    public function updateInDB()
    {
        if (preg_match('/^(\d{1,2})\.(\d{1,2})\.(\d{4})$/', $this->dGeburtstag, $matches) === 1) {
            $this->dGeburtstag = $matches[3] . '-' . $matches[2] . '-' . $matches[1];
        }

        $this->verschluesselKundendaten();
        $obj = kopiereMembers($this);

        $cKundenattribut_arr = [];
        if (is_array($obj->cKundenattribut_arr)) {
            $cKundenattribut_arr = $obj->cKundenattribut_arr;
        }

        unset(
            $obj->cKundenattribut_arr,
            $obj->cPasswort,
            $obj->angezeigtesLand,
            $obj->dGeburtstag_formatted,
            $obj->Anrede,
            $obj->cAnredeLocalized,
            $obj->cGuthabenLocalized,
            $obj->dErstellt_DE,
            $obj->cPasswortKlartext
        );
        if ($obj->dGeburtstag === '') {
            $obj->dGeburtstag = '0000-00-00';
        }

        $obj->cLand       = $this->pruefeLandISO($obj->cLand);
        $obj->dVeraendert = 'now()';
        $cReturn          = Shop::DB()->update('tkunde', 'kKunde', $obj->kKunde, $obj);
        if (is_array($cKundenattribut_arr) && count($cKundenattribut_arr) > 0) {
            $obj->cKundenattribut_arr = $cKundenattribut_arr;
        }
        $this->entschluesselKundendaten();

        // Anrede mappen
        $this->cAnredeLocalized   = mappeKundenanrede($this->cAnrede, $this->kSprache);
        $this->cGuthabenLocalized = $this->gibGuthabenLocalized();
        $cDatum_arr               = gibDatumTeile($this->dErstellt);
        $this->dErstellt_DE       = $cDatum_arr['cTag'] . '.' . $cDatum_arr['cMonat'] . '.' . $cDatum_arr['cJahr'];

        return $cReturn;
    }

    /**
     * get customer attributes
     *
     * @return $this
     */
    public function holeKundenattribute()
    {
        $this->cKundenattribut_arr = [];
        $oKundenattribut_arr       = Shop::DB()->selectAll(
            'tkundenattribut',
            'kKunde',
            (int)$this->kKunde,
            '*', 'kKundenAttribut'
        );
        if (is_array($oKundenattribut_arr) && count($oKundenattribut_arr) > 0) {
            foreach ($oKundenattribut_arr as $oKundenattribut) {
                $this->cKundenattribut_arr[$oKundenattribut->kKundenfeld] = $oKundenattribut;
            }
        }

        return $this;
    }

    /**
     * check country ISO code
     *
     * @param string $cLandISO
     * @return string
     */
    public function pruefeLandISO($cLandISO)
    {
        // ISO prüfen
        preg_match('/[a-zA-Z]{2}/', $cLandISO, $cTreffer1_arr);
        if (strlen($cTreffer1_arr[0]) !== strlen($cLandISO)) {
            $cISO = landISO($cLandISO);
            if ($cISO !== 'noISO' && strlen($cISO) > 0) {
                $cLandISO = $cISO;
            }
        }

        return $cLandISO;
    }

    /**
     * copy session
     *
     * @return $this
     */
    public function kopiereSession()
    {
        $oElement_arr = array_keys(get_object_vars($_SESSION['Kunde']));
        if (is_array($oElement_arr) && count($oElement_arr) > 0) {
            foreach ($oElement_arr as $oElement) {
                $this->$oElement = $_SESSION['Kunde']->$oElement;
            }
        }
        // Work Around
        $this->cAnredeLocalized = mappeKundenanrede($this->cAnrede, $this->kSprache);

        return $this;
    }

    /**
     * encrypt all customer data
     *
     * @return $this
     */
    public function verschluesselAlleKunden()
    {
        $oKunden_arr = Shop::DB()->query("SELECT * FROM tkunde", 2);
        if (is_array($oKunden_arr) && count($oKunden_arr) > 0) {
            foreach ($oKunden_arr as $oKunden) {
                if ($oKunden->kKunde > 0) {
                    unset($oKundeTMP);
                    $oKundeTMP = new self($oKunden->kKunde);
                    $oKundeTMP->updateInDB();
                }
            }
        }

        return $this;
    }

    /**
     * @param Kunde $oKundeOne
     * @param Kunde $oKundeTwo
     * @return bool
     */
    public static function isEqual($oKundeOne, $oKundeTwo)
    {
        if (is_object($oKundeOne) && is_object($oKundeTwo)) {
            $cMemberOne_arr = array_keys(get_class_vars(get_class($oKundeOne)));
            $cMemberTwo_arr = array_keys(get_class_vars(get_class($oKundeTwo)));

            if (count($cMemberOne_arr) !== count($cMemberTwo_arr)) {
                return false;
            }
            foreach ($cMemberOne_arr as $cMemberOne) {
                if (!isset($oKundeTwo->{$cMemberOne})) {
                    return false;
                }
                $xValueOne = $oKundeOne->{$cMemberOne};
                $xValueTwo = null;
                foreach ($cMemberTwo_arr as $cMemberTwo) {
                    if ($cMemberOne == $cMemberTwo) {
                        $xValueTwo = $oKundeTwo->{$cMemberOne};
                    }
                }
                if ($xValueOne != $xValueTwo) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param null|string $password
     * @return $this
     */
    public function updatePassword($password = null)
    {
        if ($password === null) {
            $cPasswortKlartext = $this->generatePassword(12);
            $this->cPasswort   = $this->generatePasswordHash($cPasswortKlartext);

            $_upd                 = new stdClass();
            $_upd->cPasswort      = $this->cPasswort;
            $_upd->nLoginversuche = 0;
            Shop::DB()->update('tkunde', 'kKunde', (int)$this->kKunde, $_upd);

            $obj                 = new stdClass();
            $obj->tkunde         = $this;
            $obj->neues_passwort = $cPasswortKlartext;
            sendeMail(MAILTEMPLATE_PASSWORT_VERGESSEN, $obj);
        } else {
            $this->cPasswort = $this->generatePasswordHash($password);

            $_upd                     = new stdClass();
            $_upd->cPasswort          = $this->cPasswort;
            $_upd->nLoginversuche     = 0;
            Shop::DB()->update('tkunde', 'kKunde', (int)$this->kKunde, $_upd);
        }

        return $this;
    }

    /**
     * @param int $length
     * @return bool|string
     */
    public function generatePassword($length = 12)
    {
        return gibUID($length, strtoupper(substr(md5($this->kKunde . $this->cMail . time() . $this->cStrasse), 5, 8)));
    }

    /**
     * @param string $password
     * @return false|string
     */
    public function generatePasswordHash($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }


    /**
     * creates a random string for password reset validation
     *
     * @return bool - true if valid account
     * @throws Exception
     */
    public function prepareResetPassword()
    {
        if (!$this->kKunde) {
            return false;
        }
        $key        = bin2hex(random_bytes(32));
        $linkHelper = LinkHelper::getInstance();
        $expires    = new DateTime();
        $interval   = new DateInterval('P1D');
        $expires->add($interval);
        Shop::DB()->executeQueryPrepared(
            "INSERT INTO tpasswordreset(kKunde, cKey, dExpires)
            VALUES (:kKunde, :cKey, :dExpires)
            ON DUPLICATE KEY UPDATE cKey = :cKey, dExpires = :dExpires",
            [
                'kKunde'   => $this->kKunde,
                'cKey'     => $key,
                'dExpires' => $expires->format(DateTime::ISO8601),
            ],
            3
        );

        require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';
        $linkParams             = ['fpwh' => $key];
        $obj                    = new stdClass();
        $obj->tkunde            = $this;
        $obj->passwordResetLink = $linkHelper->getStaticRoute('pass.php') .
            '?' . http_build_query($linkParams, null, '&');
        $obj->cHash             = $key;
        $obj->neues_passwort    = 'Es ist leider ein Fehler aufgetreten. Bitte kontaktieren Sie uns.';
        sendeMail(MAILTEMPLATE_PASSWORT_VERGESSEN, $obj);

        return true;
    }
}
