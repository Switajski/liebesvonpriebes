<?php

/**
 * Class SimpleMail
 */
class SimpleMail
{
    /**
     * E-Mail des Absenders
     *
     * @var string
     */
    private $cVerfasserMail;

    /**
     * Name des Absenders
     *
     * @var string
     */
    private $cVerfasserName;

    /**
     * Betreff der E-Mail
     *
     * @var string
     */
    private $cBetreff;

    /**
     * HTML Inhalt der E-Mail
     *
     * @var string
     */
    private $cBodyHTML;

    /**
     * Text Inhalt der E-Mail
     *
     * @var string
     */
    private $cBodyText;

    /**
     * Pfade zu den Dateien die angehangen werden sollen
     *
     * @var array
     */
    private $cAnhang_arr = [];

    /**
     * Versandmethode
     *
     * @var string
     */
    private $cMethod;

    /**
     * SMTP Benutzer
     *
     * @var string
     */
    private $cSMTPUser;

    /**
     * SMTP Passwort
     *
     * @var string
     */
    private $cSMTPPass;

    /**
     * SMTP Port
     *
     * @var int
     */
    private $cSMTPPort = 25;

    /**
     * SMTP Host
     *
     * @var string
     */
    private $cSMTPHost;

    /**
     * SMTP Auth nutzen 0/1
     *
     * @var int
     */
    private $cSMTPAuth;

    /**
     * Pfad zu Sendmail
     *
     * @var string
     */
    private $cSendMailPfad;

    /**
     * Error Log
     *
     * @var array
     */
    private $cErrorLog = [];

    /**
     *
     * @param bool  $bShopMail
     * @param array $cMailEinstellungen_arr
     */
    public function __construct($bShopMail = true, $cMailEinstellungen_arr = [])
    {
        $bValid = true;

        if ($bShopMail === true) {
            $Einstellungen = Shop::getSettings([CONF_EMAILS]);

            $this->cMethod       = $Einstellungen['emails']['email_methode'];
            $this->cSendMailPfad = $Einstellungen['emails']['email_sendmail_pfad'];
            $this->cSMTPHost     = $Einstellungen['emails']['email_smtp_hostname'];
            $this->cSMTPPort     = $Einstellungen['emails']['email_smtp_port'];
            $this->cSMTPAuth     = $Einstellungen['emails']['email_smtp_auth'];
            $this->cSMTPUser     = $Einstellungen['emails']['email_smtp_user'];
            $this->cSMTPPass     = $Einstellungen['emails']['email_smtp_pass'];

            $this->cVerfasserName = $Einstellungen['emails']['email_master_absender_name'];
            $this->cVerfasserMail = $Einstellungen['emails']['email_master_absender'];
        } elseif (!empty($cMailEinstellungen_arr)) {
            if (isset($cMailEinstellungen_arr['cMethod']) && !empty($cMailEinstellungen_arr['cMethod'])) {
                $bValid = $this->setMethod($cMailEinstellungen_arr['cMethod']);
            }

            $this->cSendMailPfad = $cMailEinstellungen_arr['cSendMailPfad'];
            $this->cSMTPHost     = $cMailEinstellungen_arr['cSMTPHost'];
            $this->cSMTPPort     = $cMailEinstellungen_arr['cSMTPPort'];
            $this->cSMTPAuth     = $cMailEinstellungen_arr['cSMTPAuth'];
            $this->cSMTPUser     = $cMailEinstellungen_arr['cSMTPUser'];
            $this->cSMTPPass     = $cMailEinstellungen_arr['cSMTPPass'];

            if (isset($cMailEinstellungen_arr['cVerfasserName']) && !empty($cMailEinstellungen_arr['cVerfasserName'])) {
                $this->setVerfasserName($cMailEinstellungen_arr['cVerfasserName']);
            }

            if (isset($cMailEinstellungen_arr['cVerfasserMail']) && !empty($cMailEinstellungen_arr['cVerfasserMail'])) {
                $bValid = $this->setVerfasserMail($cMailEinstellungen_arr['cVerfasserMail']);
            }
        } else {
            $bValid = false;
        }

        if ($bValid === false) {
            return $bValid;
        }

        return $this;
    }

    /**
     * Anhang hinzufügen
     * array('cName' => 'Mein Anhang', 'cPath' => '/pfad/zu/meiner/datei.txt');
     *
     * @param string $cName
     * @param string $cPath
     * @param string $cEncoding
     * @param string $cType
     * @return bool
     */
    public function addAttachment($cName, $cPath, $cEncoding = 'base64', $cType = 'application/octet-stream')
    {
        if (!empty($cName) && file_exists($cPath)) {
            $cAnhang_arr              = [];
            $cAnhang_arr['cName']     = $cName;
            $cAnhang_arr['cPath']     = $cPath;
            $cAnhang_arr['cEncoding'] = $cEncoding;
            $cAnhang_arr['cType']     = $cType;
            $this->cAnhang_arr[]      = $cAnhang_arr;

            return true;
        }

        return false;
    }

    /**
     * Validierung der Daten
     *
     * @throws Exception
     * @return bool
     */
    public function validate()
    {
        if (empty($this->cVerfasserMail) || empty($this->cVerfasserName)) {
            $this->setErrorLog('cVerfasserMail', 'Verfasser nicht gesetzt!');
        }

        if (empty($this->cBodyHTML) && empty($this->cBodyText)) {
            $this->setErrorLog('cBody', 'Inhalt der E-Mail nicht gesetzt!');
        }

        if (empty($this->cBetreff)) {
            $this->setErrorLog('cBetreff', 'Betreff nicht gesetzt!');
        }

        if (empty($this->cMethod)) {
            $this->setErrorLog('cMethod', 'Versandmethode nicht gesetzt!');
        } else {
            switch ($this->cMethod) {
                case 'PHP Mail()':
                case 'sendmail':
                    if (empty($this->cSendMailPfad)) {
                        $this->setErrorLog('cSendMailPfad', 'SendMailPfad nicht gesetzt!!');
                    }
                    break;
                case 'QMail':
                    break;
                case 'smtp':
                    if (empty($this->cSMTPAuth) || empty($this->cSMTPHost) || empty($this->cSMTPPass) || empty($this->cSMTPUser)) {
                        $this->setErrorLog('SMTP', 'SMTP Daten nicht gesetzt!');
                    }
                    break;
            }
        }

        $cErrorLog = $this->getErrorLog();
        if (!empty($cErrorLog)) {
            return false;
        }

        return true;
    }

    /**
     * E-Mail verschicken
     *
     * @param array $cEmpfaenger_arr
     * @param array $cCC_arr
     * @param array $cBCC_arr
     * @param array $cReply_arr
     * @return bool
     */
    public function send(array $cEmpfaenger_arr, $cCC_arr = [], $cBCC_arr = [], $cReply_arr = [])
    {
        if ($this->validate() === true) {
            $oPHPMailer            = new \PHPMailer\PHPMailer\PHPMailer();
            $oPHPMailer->Timeout   = SOCKET_TIMEOUT;
            $oPHPMailer->PluginDir = PFAD_ROOT . PFAD_PHPMAILER;
            $oPHPMailer->From      = $this->cVerfasserMail;
            $oPHPMailer->Sender    = $this->cVerfasserMail;
            $oPHPMailer->FromName  = $this->cVerfasserName;

            if (!empty($cEmpfaenger_arr)) {
                foreach ($cEmpfaenger_arr as $cEmpfaenger) {
                    $oPHPMailer->addAddress($cEmpfaenger['cMail'], $cEmpfaenger['cName']);
                }
            }

            if (!empty($cCC_arr)) {
                foreach ($cCC_arr as $cCC) {
                    $oPHPMailer->addCC($cCC['cMail'], $cCC['cName']);
                }
            }

            if (!empty($cBCC_arr)) {
                foreach ($cBCC_arr as $cBCC) {
                    $oPHPMailer->addBCC($cBCC['cMail'], $cBCC['cName']);
                }
            }

            if (!empty($cReply_arr)) {
                foreach ($cReply_arr as $cReply) {
                    $oPHPMailer->addReplyTo($cReply['cMail'], $cReply['cName']);
                }
            }

            $oPHPMailer->Subject = $this->cBetreff;

            switch ($this->cMethod) {
                case 'mail':
                    $oPHPMailer->isMail();
                    break;
                case 'sendmail':
                    $oPHPMailer->isSendmail();
                    $oPHPMailer->Sendmail = $this->cSendMailPfad;
                    break;
                case 'qmail':
                    $oPHPMailer->isQmail();
                    break;
                case 'smtp':
                    $oPHPMailer->isSMTP();
                    $oPHPMailer->Host          = $this->cSMTPHost;
                    $oPHPMailer->Port          = $this->cSMTPPort;
                    $oPHPMailer->SMTPKeepAlive = true;
                    $oPHPMailer->SMTPAuth      = $this->cSMTPAuth;
                    $oPHPMailer->Username      = $this->cSMTPUser;
                    $oPHPMailer->Password      = $this->cSMTPPass;
                    break;
            }

            if (!empty($this->cBodyHTML)) {
                $oPHPMailer->isHTML(true);
                $oPHPMailer->Body    = $this->cBodyHTML;
                $oPHPMailer->AltBody = $this->cBodyText;
            } else {
                $oPHPMailer->isHTML(false);
                $oPHPMailer->Body = $this->cBodyText;
            }

            if (count($this->cAnhang_arr) > 0) {
                foreach ($this->cAnhang_arr as $cAnhang_arr) {
                    $oPHPMailer->addAttachment($cAnhang_arr['cPath'], $cAnhang_arr['cName'], $cAnhang_arr['cEncoding'], $cAnhang_arr['cType']);
                }
            }

            $bSent = $oPHPMailer->send();

            $oPHPMailer->clearAddresses();

            return $bSent;
        }

        return false;
    }

    /**
     * @return string
     */
    public function getVerfasserMail()
    {
        return $this->cVerfasserMail;
    }

    /**
     *
     * @return string
     */
    public function getVerfasserName()
    {
        return $this->cVerfasserName;
    }

    /**
     *
     * @return string
     */
    public function getBetreff()
    {
        return $this->cBetreff;
    }

    /**
     *
     * @return string
     */
    public function getBodyHTML()
    {
        return $this->cBodyHTML;
    }

    /**
     *
     * @return string
     */
    public function getBodyText()
    {
        return $this->cBodyText;
    }

    /**
     * @param string $cVerfasserMail
     * @return bool
     */
    public function setVerfasserMail($cVerfasserMail)
    {
        if (filter_var($cVerfasserMail, FILTER_VALIDATE_EMAIL)) {
            $this->cVerfasserMail = $cVerfasserMail;

            return true;
        }

        return false;
    }

    /**
     * @param string $cVerfasserName
     * @return $this
     */
    public function setVerfasserName($cVerfasserName)
    {
        $this->cVerfasserName = $cVerfasserName;

        return $this;
    }

    /**
     * @param string $cBetreff
     * @return $this
     */
    public function setBetreff($cBetreff)
    {
        $this->cBetreff = $cBetreff;

        return $this;
    }

    /**
     * @param string $cBodyHTML
     * @return $this
     */
    public function setBodyHTML($cBodyHTML)
    {
        $this->cBodyHTML = $cBodyHTML;

        return $this;
    }

    /**
     * @param string $cBodyText
     * @return $this
     */
    public function setBodyText($cBodyText)
    {
        $this->cBodyText = $cBodyText;

        return $this;
    }

    /**
     * @return null
     */
    public function getErrorInfo()
    {
        return null;
    }

    /**
     * @return array
     */
    public function getErrorLog()
    {
        return $this->cErrorLog;
    }

    /**
     * @param string $cKey
     * @param mixed  $cValue
     */
    public function setErrorLog($cKey, $cValue)
    {
        $this->cErrorLog[$cKey] = $cValue;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->cMethod;
    }

    /**
     * @param string $cMethod
     * @return bool
     */
    public function setMethod($cMethod)
    {
        if ($cMethod == 'QMail' || $cMethod == 'smtp' || $cMethod == 'PHP Mail()' || $cMethod == 'sendmail') {
            $this->cMethod = $cMethod;

            return true;
        }

        return false;
    }
}
