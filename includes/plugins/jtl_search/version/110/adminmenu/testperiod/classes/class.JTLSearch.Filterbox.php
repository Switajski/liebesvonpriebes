<?php

/**
 * Communication Class
 *
 * @access public
 * @author Daniel Boehmer
 * @copyright 2011 JTL-Software GmbH
 */
class Filterbox
{
    /**
     * @return null
     */
    public static function Create()
    {
        if (class_exists('Template')) {
            $oTemplate                = new Template();
            $cTemplate                = $oTemplate->holeAktuellenTemplateOrdner();
            $bBoxenContainerAssoc_arr = $oTemplate->leseBoxenContainerXML($cTemplate);

            if (is_array($bBoxenContainerAssoc_arr) && isset($bBoxenContainerAssoc_arr['left'])) {
                $Model = Filterbox::GetModel();

                if ($Model !== null) {
                    $Pk               = null;
                    $Box              = new stdClass();
                    $Box->kBoxvorlage = $Model->kBoxvorlage;
                    $Box->kCustomID   = $Model->kCustomID;
                    $Box->kContainer  = 0;
                    $Box->cTitel      = $Model->cName;

                    // Linke Box vorhanden?
                    if ($bBoxenContainerAssoc_arr['left']) {
                        $Box->ePosition = 'left';
                        $Pk             = Shop::DB()->insert('tboxen', $Box);
                    } // Rechte Box vorhanden?
                    else {
                        if ($bBoxenContainerAssoc_arr['right']) {
                            $Box->ePosition = 'right';
                            $Pk             = Shop::DB()->insert('tboxen', $Box);
                        }
                    }

                    if ($Pk !== null) {
                        $BoxVis         = new stdClass();
                        $BoxVis->kBox   = $Pk;
                        $BoxVis->kSeite = $Model->cVerfuegbar;
                        $BoxVis->nSort  = 1;
                        $BoxVis->bAktiv = 1;

                        return Shop::DB()->insert('tboxensichtbar', $BoxVis);
                    }
                }
            }
        }

        return null;
    }

    /**
     * @return stdClass|null
     */
    public static function GetModel()
    {
        $Obj = Shop::DB()->query(
            "SELECT tboxvorlage.*
												FROM tplugin
												JOIN tboxvorlage ON tboxvorlage.kCustomID = tplugin.kPlugin
												WHERE tplugin.cPluginID = 'jtl_search'", 1
        );

        if (isset($Obj->kBoxvorlage) && $Obj->kBoxvorlage > 0) {
            return $Obj;
        }

        return null;
    }
}
