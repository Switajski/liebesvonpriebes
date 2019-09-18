<?php
/**
 *-------------------------------------------------------------------------------
 *	JTL-Shop 3
 *	File: startcats.php, php file
 *
 *	JTL-Shop 3
 *
 * Do not use, modify or sell this code without permission / licence.
 *
 * @author JTL-Software <daniel.boehmer@jtl-software.de>
 * @copyright 2010, JTL-Software
 * @link http://jtl-software.de/jtlshop.php
 * @version 1.0
 *-------------------------------------------------------------------------------
*/
    
require_once(dirname(__FILE__) . "/classes/class.StartCats.php");

if (isset($oPlugin->oPluginEinstellungAssoc_arr['jtl_startcats_aktiv']) && $oPlugin->oPluginEinstellungAssoc_arr['jtl_startcats_aktiv'] == "Y" && isset($GLOBALS['nSeitenTyp']) && $GLOBALS['nSeitenTyp'] === PAGE_STARTSEITE) {
    if (class_exists("StartCats")) {
        $oStartCats = new StartCats();
        $oStartKategorie_arr = $oStartCats->getKategories();
        
        if (is_array($oStartKategorie_arr) && count($oStartKategorie_arr) > 0) {
            $cTitle = "";
            if (isset($oPlugin->oPluginSprachvariableAssoc_arr['jtl_startcats_title']) && strlen($oPlugin->oPluginSprachvariableAssoc_arr['jtl_startcats_title']) > 0) {
                $cTitle = '<h1 class="underline">' . $oPlugin->oPluginSprachvariableAssoc_arr['jtl_startcats_title'] . '</h1>';
            }
            
            $cHTML = '<div class="container">
						' . $cTitle . '
						<ul class="hlist articles">';
            foreach ($oStartKategorie_arr as $i => $oStartKategorie) {
                $cClear = "";
                if ($i % 3 == 0) {
                    $cClear = " clear";
                }
            
                $cHTML .= ' <li class="p33 tcenter' . $cClear . '">
								<div>
									<p><a href="' . $oStartKategorie->cURL . '"><img src="' . $oStartKategorie->cBildURL . '" class="image" alt="' . $oStartKategorie->cName . '" /></a></p>
									<p><a href="' . $oStartKategorie->cURL . '">' . $oStartKategorie->cName. '</a></p>
								</div>
							</li>';
            }
            
            $cHTML .= '	 </ul>
					   </div>';

            $cSelector = "#content .custom_content:eq(0)";
            if (isset($oPlugin->oPluginEinstellungAssoc_arr['jtl_startcats_selector']) && strlen($oPlugin->oPluginEinstellungAssoc_arr['jtl_startcats_selector']) > 0) {
                $cSelector = $oPlugin->oPluginEinstellungAssoc_arr['jtl_startcats_selector'];
            }
            
            pq($cSelector)->after($cHTML);
        }
    }
}
