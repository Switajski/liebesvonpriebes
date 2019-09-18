<?php
/**
 * Communication Class
 * @access public
 * @author Daniel Boehmer
 * @copyright 2011 JTL-Software GmbH
 */
class Filterbox
{
    public static function Create()
    {
        if (class_exists("Template")) {
            $oTemplate = new Template();
            $cTemplate = $oTemplate->holeAktuellenTemplateOrdner();
            $bBoxenContainerAssoc_arr = $oTemplate->leseBoxenContainerXML($cTemplate);
            
            if (is_array($bBoxenContainerAssoc_arr) && isset($bBoxenContainerAssoc_arr['left'])) {
                $Model = Filterbox::GetModel();
                
                if ($Model !== NULL) {
                    $Pk = null;
                    $Box = new stdClass();
                    $Box->kBoxvorlage = $Model->kBoxvorlage;
                    $Box->kCustomID = $Model->kCustomID;
                    $Box->kContainer = 0;
                    $Box->cTitel = $Model->cName;
                    
                    // Linke Box vorhanden?
                    if ($bBoxenContainerAssoc_arr['left']) {
                        $Box->ePosition = "left";
                        $Pk = $GLOBALS['DB']->insertRow("tboxen", $Box);
                    }
                     
                    // Rechte Box vorhanden?
                    elseif ($bBoxenContainerAssoc_arr['right']) {
                        $Box->ePosition = "right";
                        $Pk = $GLOBALS['DB']->insertRow("tboxen", $Box);
                    }
                    
                    if ($Pk !== null) {
                        $BoxVis = new stdClass();
                        $BoxVis->kBox = $Pk;
                        $BoxVis->kSeite = $Model->cVerfuegbar;
                        $BoxVis->nSort = 1;
                        $BoxVis->bAktiv = 1;

                        return $GLOBALS['DB']->insertRow("tboxensichtbar", $BoxVis);
                    }
                }
            }
        }
        
        return null;
    }
    
    public static function GetModel()
    {
        $Obj = $GLOBALS['DB']->executeQuery("SELECT tboxvorlage.*
												FROM tplugin
												JOIN tboxvorlage ON tboxvorlage.kCustomID = tplugin.kPlugin
												WHERE tplugin.cPluginID = 'jtl_search'", 1);
        
        if (isset($Obj->kBoxvorlage) && $Obj->kBoxvorlage > 0) {
            return $Obj;
        }
        
        return NULL;
    }
}
