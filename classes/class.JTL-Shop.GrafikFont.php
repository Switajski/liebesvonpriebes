<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class GrafikFont
 */
class GrafikFont
{
    /**
     * @access private
     * @var string
     */
    public $m_strFontDir = '';

    /**
     * @var string
     */
    public $m_strFile = 'png';

    /**
     * @var string
     */
    public $m_strCentKleiner = '';

    /**
     * @access protected
     * @var string
     */
    public $m_strInput;

    /**
     * @var
     */
    public $m_strHTML;

    /**
     * @access protected
     * @var bool
     */
    public $m_bPrice = false;

    /**
     * Konstruktor
     *
     * @param string $strInput
     * @param bool   $bPrice
     * @param string $strFontDir
     * @param bool   $bCentKleiner
     */
    public function __construct($strInput, $bPrice, $strFontDir, $bCentKleiner)
    {
        $this->setInputString($strInput);
        if (is_bool($bPrice)) {
            $this->setPrice($bPrice);
        }
        if ($bCentKleiner) {
            $this->m_strCentKleiner = 'Y';
        }
        $this->m_strFontDir = $strFontDir;
    }

    /**
     * Sets unmodified input string
     *
     * @param string $strInput
     * @return $this
     * @access public
     */
    public function setInputString($strInput)
    {
        $this->m_strInput = $strInput;

        return $this;
    }

    /**
     * Returns unmodified input string
     *
     * @access public
     * @return string
     */
    public function getInputString()
    {
        return $this->m_strInput;
    }

    /**
     * Sets price bool
     *
     * @param bool $bPrice
     * @return $this
     * @access public
     */
    public function setPrice($bPrice)
    {
        $this->m_bPrice = $bPrice;

        return $this;
    }

    /**
     * Returns price bool
     *
     * @access public
     * @return bool
     */
    public function getPrice()
    {
        return $this->m_bPrice;
    }

    /**
     * Returns images with HTML
     *
     * @access public
     * @return string
     */
    public function getHTML()
    {
        return $this->m_strHTML;
    }

    /**
     * Wandelt String in aneinander gereihte Grafiken um
     *
     * @access protected
     * @return bool
     */
    public function transform()
    {
        $bReturn        = false;
        $bCent          = false;
        $bNull          = false;
        $strInputString = StringHandler::htmlentitydecode($this->m_strInput); // ab php 4.3.0
        //$strInputString = htmlspecialchars_decode($strInputString); // ab php 5.1.0
        $strInputString = str_replace('&euro;', '', $strInputString);
        $nCentCounter   = 0;
        if (!$strInputString) {
            return false;
        }
        $strHTML  = '<div class="grafikpreis">';
        $Waehrung = $_SESSION['Waehrung'];
        if (!$Waehrung->kWaehrung) {
            $Waehrung = Shop::DB()->select('twaehrung', 'cStandard', 'Y');
        }
        $strTrennzeichenCent = $Waehrung->cTrennzeichenCent;
        $nLength             = strlen($strInputString);
        for ($i = 0; $i <= $nLength; $i++) {
            $strFileName = '';
            $bMakeHTML   = true;
            $strKlein    = '';
            if ($bCent && $this->m_strCentKleiner === 'Y') {
                $strKlein = '_klein';
            }
            if ($bCent) {
                $nCentCounter++;
            }
            $c = substr($strInputString, $i, 1);

            switch ($c) {
                case '!':
                    $strFileName = 'Ausrufezeichen.' . $this->m_strFile;
                    break;
                case '$':
                    $strFileName = 'Dollar.' . $this->m_strFile;
                    if (!is_file($this->m_strFontDir . $strFileName)) {
                        $bMakeHTML = false;
                        $strHTML .= '<img style="border:none; margin-right:0px;" src="' . $this->m_strFontDir . 'U.' . $this->m_strFile . '" alt="' . $c . '" />';
                        $strHTML .= '<img style="border:none; margin-right:0px;" src="' . $this->m_strFontDir . 'S.' . $this->m_strFile . '" alt="' . $c . '" />';
                        $strHTML .= '<img style="border:none; margin-right:0px;" src="' . $this->m_strFontDir . 'D.' . $this->m_strFile . ' alt="' . $c . '" />';
                    }
                    break;
                case '':
                    $strFileName = "Euro." . $this->m_strFile;
                    if (!is_file($this->m_strFontDir . $strFileName)) {
                        $bMakeHTML = false;
                        $strHTML .= '<img style="border:none; margin-right:0px;" src="' . $this->m_strFontDir . 'E.' . $this->m_strFile . '" alt="' . $c . '" />';
                        $strHTML .= '<img style="border:none; margin-right:0px;" src="' . $this->m_strFontDir . 'U.' . $this->m_strFile . '" alt="' . $c . '" />';
                        $strHTML .= '<img style="border:none; margin-right:0px;" src="' . $this->m_strFontDir . 'R.' . $this->m_strFile . '" alt="' . $c . '" />';
                    }
                    break;
                case '´':
                case '`';
                    $strFileName = 'Hochkomma.' . $this->m_strFile;
                    break;
                case ',':
                    $strFileName = 'Komma.' . $this->m_strFile;
                    break;
                case '-':
                    $strFileName = 'Minus.' . $this->m_strFile;
                    break;
                case '+':
                    $strFileName = 'Plus.' . $this->m_strFile;
                    break;
                case '.':
                    $strFileName = 'Punkt.' . $this->m_strFile;
                    break;
                case ' ':
                    $strFileName = 'empty.' . $this->m_strFile;
                    break;
                case '':
                    $strFileName = '';
                    break;
                default:
                    // wenn cent, dann nächste zahl abwarten, wenn die nicht null ist eine null und die zahl ausgeben, ansonsten für beide Nullen das Minus
                    if ($bCent && $bNull && $c == '0') {
                        $strFileName = 'Minus.' . $this->m_strFile;
                        $bNull       = false;
                    } elseif ($bCent && $bNull && $c != '0') {
                        if (is_numeric($c)) {
                            $strFileName = '0' . $strKlein . '.' . $this->m_strFile;
                            $strHTML .= '<img style="border:none; margin-right:0px;" src="' . $this->m_strFontDir . $strFileName . '" alt="' . $c . '" />';
                            $strFileName = $c . $strKlein . '.' . $this->m_strFile;
                            $strHTML .= '<img style="border:none; margin-right:0px;" src="' . $this->m_strFontDir . $strFileName . '" alt="' . $c . '" />';
                            $bMakeHTML = false;
                        } else {
                            $strFileName = $c . '.' . $this->m_strFile;
                        }
                        $bNull = false;
                        $bCent = false;
                    } elseif ($bCent && $c == '0') {
                        if ($nCentCounter > 1) {
                            $strFileName = $c . $strKlein . '.' . $this->m_strFile;
                        }
                        $bNull = true;
                    } elseif (is_numeric($c)) {
                        $strFileName = $c . $strKlein . '.' . $this->m_strFile;
                    } else {
                        $strFileName = $c . '.' . $this->m_strFile;
                    }
                    break;
            }
            if ($bMakeHTML && is_file($this->m_strFontDir . $strFileName)) {
                $strHTML .= '<img style="border:none; margin-right:0px;" src="' . $this->m_strFontDir . $strFileName . '" alt="' . $c . '" />';
            }

            if ($c == $strTrennzeichenCent) {
                $bCent        = true;
                $nCentCounter = 0;
            }
        }

        $strHTML .= '</div>';
        $this->m_strHTML = $strHTML;

        return $bReturn;
    }
}
