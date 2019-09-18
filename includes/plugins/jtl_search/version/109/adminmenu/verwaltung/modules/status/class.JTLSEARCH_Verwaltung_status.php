<?php

require_once JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_CLASSES . 'class.JTLSEARCH_Verwaltung_Base.php';

/**
 * Class JTLSEARCH_Verwaltung_status
 */
class JTLSEARCH_Verwaltung_status extends JTLSEARCH_Verwaltung_Base
{
    /**
     * @param IDebugger $oDebugger
     */
    public function __construct(IDebugger $oDebugger)
    {
        $this->oDebugger = $oDebugger;
    }

    /**
     * @param bool $bForce
     * @return $this
     */
    public function generateContent($bForce = false)
    {
        if ($this->getIssetContent() === false || $bForce === true) {
            $this->setIssetContent(true)
                 ->setSort(1)
                 ->setContentTemplate('verwaltung_status.tpl')
                 ->setName('Index Status')
                 ->setContentVar('xIndexStatus_arr', $this->getIndexStatus());
            $this->getServereinstellungenURL();
        }

        return $this;
    }

    /**
     * @return array|mixed
     */
    public function getIndexStatus()
    {
        require_once JTLSEARCH_PFAD_CLASSES . 'class.Communication.php';
        require_once JTLSEARCH_PFAD_CLASSES . 'class.Security.php';
        // Security Objekt erstellen und Parameter zum Senden der Daten setzen
        $oServerSettings = self::getServerSettings();

        $oSecurity = new Security($oServerSettings->cProjectId, $oServerSettings->cAuthHash);
        $oSecurity->setParam_arr(array('getindexstatus'));

        $xData_arr['a']   = 'getindexstatus';
        $xData_arr['p']   = $oSecurity->createKey();
        $xData_arr['pid'] = $oServerSettings->cProjectId;

        $cResult = Communication::postData(urldecode($oServerSettings->cServerUrl) . 'importdaemon/index.php', $xData_arr);

        if (strlen($cResult) > 0) {
            $oIndexStatus = json_decode($cResult);
            if ($oIndexStatus === null || $oIndexStatus === false) {
                $this->oDebugger->doDebug(__FILE__ . ': $cResult : ' . $cResult);
            }

            return $oIndexStatus;
        } else {
            $this->oDebugger->doDebug(__FILE__ . ': $cResult ist leer', JTLSEARCH_DEBUGGING_LEVEL_NOTICE);
        }

        return array();
    }

    /**
     *
     */
    protected function getServereinstellungenURL()
    {
        require_once JTLSEARCH_PFAD_CLASSES . 'class.Security.php';
        // Link zu den Servereinstellungen erzeugen
        $oServerSettings = self::getServerSettings();
        $oSecurity       = new Security($oServerSettings->cProjectId, $oServerSettings->cAuthHash);
        $oSecurity->setParam_arr(array(URL_SHOP));

        $cServereinstellungenURL = false;
        if (verifyGPDataString('a') === 'createtmplogin') {
            $cRequestUrl = str_replace('https', 'http', urldecode($oServerSettings->cServerUrl)) . "admin/adminlogin/index/pid/{$oServerSettings->cProjectId}/auth/{$oSecurity->createKey()}";
            // JTL Search loginId request
            $cLoginId = $this->requestJTLSearchLoginId($cRequestUrl);

            if ($cLoginId !== null) {
                $cServereinstellungenURL = str_replace('https', 'http', urldecode($oServerSettings->cServerUrl)) . "admin/index/login/pid/{$oServerSettings->cProjectId}/auth/{$cLoginId}";
            }
        }
        $this->setContentVar('cServereinstellungenURL', $cServereinstellungenURL);
    }

    /**
     * @param $cRequestUrl
     * @return mixed|null|string
     */
    protected function requestJTLSearchLoginId($cRequestUrl)
    {
        if (function_exists('curl_init') && strlen($cRequestUrl) > 0) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, array());
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1');
            curl_setopt($ch, CURLOPT_URL, $cRequestUrl);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
            curl_setopt($ch, CURLOPT_AUTOREFERER, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

            $cContent = curl_exec($ch);

            curl_close($ch);

            $cContent = trim($cContent);
            if ($cContent !== "0") {
                return $cContent;
            }
        }

        return null;
    }
}
