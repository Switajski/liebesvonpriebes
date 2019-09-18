<?php

require_once JTLSEARCH_PFAD_CLASSES . 'class.JTLSearch_Form.php';

/**
 * Description of JTLSearch_Form_Activate
 *
 * @author andre
 */
class JTLSearch_Form_Activate extends JTLSearch_Form
{
    /**
     * @param $xValue
     * @param $xOptParam
     * @return bool
     */
    protected function rule_base64decodeable($xValue, $xOptParam)
    {
        $cData_arr = explode(':::', base64_decode($xValue));
        if (count($cData_arr) === 2) {
            return true;
        }

        return false;
    }
}
