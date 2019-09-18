<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class SmartyResourceNiceDB
 */
class SmartyResourceNiceDB extends Smarty_Resource_Custom
{
    /**
     * @var string
     */
    private $type;

    /**
     * SmartyResourceNiceDB constructor.
     * @param string $type
     */
    public function __construct($type = 'export')
    {
        $this->type  = $type;
    }

    /**
     * @param string $name
     * @param string $source
     * @param int    $mtime
     * @return bool|void
     */
    public function fetch($name, &$source, &$mtime)
    {
        if ($this->type === 'export') {
            $exportformat = Shop::DB()->select('texportformat', 'kExportformat', (int)$name);
            if (empty($exportformat->kExportformat) || $exportformat->kExportformat <= 0) {
                return false;
            }
            $source = $exportformat->cContent;
        } elseif ($this->type === 'mail') {
            $pcs = explode('_', $name);
            if (isset($pcs[0], $pcs[1], $pcs[2], $pcs[3]) &&
                $pcs[3] === 'anbieterkennzeichnung'
            ) {
                // Anbieterkennzeichnungsvorlage holen
                $vl = Shop::DB()->query(
                    "SELECT tevs.cContentHtml, tevs.cContentText
                        FROM temailvorlageoriginal tevo
                        JOIN temailvorlagesprache tevs
                            ON tevs.kEmailVorlage = tevo.kEmailvorlage
                            AND tevs.kSprache = " . (int)$pcs[4] . "
                        WHERE tevo.cModulId = 'core_jtl_anbieterkennzeichnung'", 1
                );
            } else {
                // Plugin Emailvorlage?
                $cTableSprache = 'temailvorlagesprache';
                if (isset($pcs[3]) && (int)$pcs[3] > 0) {
                    $cTableSprache = 'tpluginemailvorlagesprache';
                }
                $vl = Shop::DB()->select($cTableSprache, ['kEmailvorlage', 'kSprache'], [(int)$pcs[1], (int)$pcs[2]]);
            }
            if ($vl !== null && $vl !== false) {
                if ($pcs[0] === 'html') {
                    $source = $vl->cContentHtml;
                } elseif ($pcs[0] === 'text') {
                    $source = $vl->cContentText;
                } else {
                    $source = '';
                    Jtllog::writeLog('Ungueltiger Emailvorlagen-Typ: ' . $pcs[0], JTLLOG_LEVEL_NOTICE);
                }
            } else {
                $source = '';
                Jtllog::writeLog('Emailvorlage mit der ID ' . (int)$pcs[1] .
                    ' in der Sprache ' . (int)$pcs[2] . ' wurde nicht gefunden', JTLLOG_LEVEL_NOTICE);
            }
        } elseif ($this->type === 'newsletter') {
            $cTeile_arr = explode('_', $name);
            $cTabelle   = 'tnewslettervorlage';
            $cFeld      = 'kNewsletterVorlage';
            if ($cTeile_arr[0] === 'NL') {
                $cTabelle = 'tnewsletter';
                $cFeld    = 'kNewsletter';
            }
            $oNewsletter = Shop::DB()->select($cTabelle, $cFeld, $cTeile_arr[1]);

            if ($cTeile_arr[2] === 'html') {
                $source = $oNewsletter->cInhaltHTML;
            } elseif ($cTeile_arr[2] === 'text') {
                $source = $oNewsletter->cInhaltText;
            }
        } else {
            $source = '';
            Jtllog::writeLog('Template-Typ ' . $this->type . ' wurde nicht gefunden', JTLLOG_LEVEL_NOTICE);
        }
    }

    /**
     * @param string $name
     * @return int
     */
    protected function fetchTimestamp($name)
    {
        return time();
    }
}
