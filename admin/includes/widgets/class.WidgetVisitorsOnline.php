<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_WIDGETS . 'class.WidgetBase.php';

/**
 * Class WidgetVisitorsOnline
 */
class WidgetVisitorsOnline extends WidgetBase
{
    /**
     *
     */
    public function init()
    {
        archiviereBesucher();
    }

    /**
     * @return array
     */
    public function getVisitors()
    {
        $oVisitors_arr = Shop::DB()->query(
            "SELECT tbesucher.*, tbestellung.fGesamtsumme, tkunde.cVorname, 
                tkunde.cNachname, tkunde.dErstellt, tkunde.cNewsletter
                FROM tbesucher
                LEFT JOIN tbestellung 
                    ON tbesucher.kBestellung = tbestellung.kBestellung
                LEFT JOIN tkunde 
                    ON tbesucher.kKunde = tkunde.kKunde
                WHERE tbesucher.kBesucherBot = 0", 2
        );
        if (is_array($oVisitors_arr)) {
            foreach ($oVisitors_arr as $i => $oVisitor) {
                $oVisitors_arr[$i]->cNachname = trim(entschluesselXTEA($oVisitor->cNachname));
                if ($oVisitor->kBestellung > 0) {
                    $oVisitors_arr[$i]->fGesamtsumme = gibPreisStringLocalized($oVisitor->fGesamtsumme);
                }
            }
        } else {
            $oVisitors_arr = [];
        }

        return $oVisitors_arr;
    }

    /**
     * @param array $oVisitors_arr
     * @return stdClass
     */
    public function getVisitorsInfo($oVisitors_arr)
    {
        $oInfo            = new stdClass();
        $oInfo->nCustomer = 0;
        $oInfo->nAll      = count($oVisitors_arr);
        if ($oInfo->nAll > 0) {
            foreach ($oVisitors_arr as $i => $oVisitor) {
                if ($oVisitor->kKunde > 0) {
                    $oInfo->nCustomer++;
                }
            }
        }
        $oInfo->nUnknown = $oInfo->nAll - $oInfo->nCustomer;

        return $oInfo;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        $oVisitors_arr = $this->getVisitors();
        return $this->oSmarty->assign('oVisitors_arr', $oVisitors_arr)
            ->assign('oVisitorsInfo', $this->getVisitorsInfo($oVisitors_arr))
            ->fetch('tpl_inc/widgets/visitors_online.tpl');
    }
}
