<?php

/**
 * Description of JTLSEARCH_FORM
 *
 * @author andre
 */
class JTLSearch_Form
{
    private $oDebugger;
    private $cFormData = array();
    private $cElementAssoc_arr = array();
    private $cElementRulesAssoc_arr = array();
    private $cErrorAssoc_arr = array();

    public function __construct(IDebugger $oDebugger, $cFormName, $cFormMethod)
    {
        $this->oDebugger = $oDebugger;
        if (strlen($cFormName) > 0 && !strpos($cFormName, ' ') && (strtolower($cFormMethod) == 'post' || strtolower($cFormMethod) == 'get')) {
            $this->cFormData['name'] = $cFormName;
            $this->cFormData['method'] = $cFormMethod;
        }
    }

    public function addElement($cName, $cType, $cLabel = '', $cOpt_arr = array())
    {
        if (strlen($cName) > 0 && !strpos($cName, ' ')) {
            if (strlen($cType) > 0) {
                if (isset($this->cElementAssoc_arr[$cName])) {
                    $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ . '; Element mit dem Namen ' . $cName . ' ist bereits vorhanden.');
                    return false;
                } else {
                    if (!is_array($cOpt_arr)) {
                        $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ . '; $cOpt_arr ist kein array (' . print_r($cOpt_arr) . ')');
                        $cOpt_arr = array();
                    }
                    $this->cElementAssoc_arr[$cName] = array('name' => $cName, 'type' => $cType, 'label' => $cLabel, 'cOpt_arr' => $cOpt_arr);
                }
            } else {
                $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ . '; $cType zu kurz (' . $cType . ')');
                return false;
            }
        } else {
            $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ . '; $cName zu kurz oder enthält Leerzeichen (' . $cName . ')');
            return false;
        }
    }

    public function addRule($cElementName, $cMessage, $cRule, $xOptParam = null)
    {
        if (strlen($cElementName) > 0 && isset($this->cElementAssoc_arr[$cElementName])) {
            if (method_exists($this, 'rule_' . $cRule)) {
                if (isset($this->cElementRulesAssoc_arr[$cElementName])) {
                    array_push($this->cElementRulesAssoc_arr[$cElementName], array('rule' => $cRule, 'message' => $cMessage, 'xOptParam' => $xOptParam));
                } else {
                    $this->cElementRulesAssoc_arr[$cElementName] = array(array('rule' => $cRule, 'message' => $cMessage, 'xOptParam' => $xOptParam));
                }
            } else {
                $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ . '; Zu dieser Regel ist keine Methode bekannt (' . $cRule . ')');
            }
        }
    }

    public function getFormStartHTML()
    {
        $cResult = '';
        if (isset($this->cFormData['name']) && strlen($this->cFormData['name']) > 0 && isset($this->cFormData['method']) && strlen($this->cFormData['method'])) {
            $cResult = '<form name="' . $this->cFormData['name'] . '" method="' . $this->cFormData['method'] . '">';
        }
        return $cResult;
    }

    public function getHiddenElements()
    {
        $cResult = '';
        foreach ($this->cElementAssoc_arr as $cElementAssoc) {
            if (strtolower($cElementAssoc['type']) == 'hidden') {
                $cResult .= '<input type="' . $cElementAssoc['type'] . '" name="' . $cElementAssoc['name'] . '"';
                foreach ($cElementAssoc['cOpt_arr'] as $cKey => $cValue) {
                    if (is_numeric($cValue) || is_string($cValue)) {
                        $cResult .= ' ' . $cKey . '="' . $cValue . '"';
                    }
                }
                $cResult .= ' />';
            }
        }
        return $cResult;
    }

    public function getFormEndHTML()
    {
        return '</form>';
    }

    public function getElementHTML($cElementName)
    {
        if (isset($this->cElementAssoc_arr[$cElementName])) {
            switch (strtolower($this->cElementAssoc_arr[$cElementName]['type'])) {
                case 'textarea':
                    $cResult = '<' . strtolower($this->cElementAssoc_arr[$cElementName]['type']) . ' name="' . $cElementName . '"';
                    $cDefaultvalue = '';
                    foreach ($this->cElementAssoc_arr[$cElementName]['cOpt_arr'] as $cKey => $cValue) {
                        if (strtolower($cKey) == 'value') {
                            $cDefaultvalue = $cValue;
                        } elseif (is_numeric($cValue) || is_string($cValue)) {
                            $cResult .= ' ' . $cKey . '="' . $cValue . '"';
                        }
                    }
                    $cResult .= '>'.$cDefaultvalue.'</'.strtolower($this->cElementAssoc_arr[$cElementName]['type']).'>';
                    return $cResult;
                    break;
                case 'text':
                case 'submit':
                    $cResult = '<input type="' . strtolower($this->cElementAssoc_arr[$cElementName]['type']) . '" name="' . $cElementName . '"';
                    foreach ($this->cElementAssoc_arr[$cElementName]['cOpt_arr'] as $cKey => $cValue) {
                        if (is_numeric($cValue) || is_string($cValue)) {
                            $cResult .= ' ' . $cKey . '="' . $cValue . '"';
                        }
                    }
                    $cResult .= ' />';
                    return $cResult;
                    break;

                default:
                    break;
            }
        } else {
            return '';
        }
    }

    public function getLabelHTML($cElementName)
    {
        if (isset($this->cElementAssoc_arr[$cElementName])) {
            return '<label for="' . $cElementName . '">' . $this->cElementAssoc_arr[$cElementName]['label'] . '</label>';
        } else {
            return '';
        }
    }

    public function isValid()
    {
        $bReturn = true;
        foreach ($this->cElementRulesAssoc_arr as $cElementName => $cElementRulesAssoc_arr) {
            foreach ($cElementRulesAssoc_arr as $cElementRuleAssoc) {
                if (!call_user_func(array(&$this, 'rule_' . $cElementRuleAssoc['rule']), $_POST[$cElementName], $cElementRuleAssoc['xOptParam'])) {
                    if (isset($this->cErrorAssoc_arr[$cElementName])) {
                        array_push($this->cErrorAssoc_arr[$cElementName], $cElementRuleAssoc['message']);
                    } else {
                        $this->cErrorAssoc_arr[$cElementName] = array($cElementRuleAssoc['message']);
                    }
                    $bReturn = false;
                }
            }
        }
        return $bReturn;
    }
    
    public function setError($cError)
    {
        if (strlen($cError) > 0) {
            if (isset($this->cErrorAssoc_arr['error'])) {
                array_push($this->cErrorAssoc_arr['error'], $cError);
            } else {
                $this->cErrorAssoc_arr['error'] = array($cError);
            }
        }
    }

    public function getErrorMessages($cElementName = null)
    {
        $cResult_arr = array();
        if (isset($cElementName) && isset($this->cErrorAssoc_arr[$cElementName])) {
            $cResult_arr = $this->cErrorAssoc_arr[$cElementName];
        } else {
            foreach ($this->cErrorAssoc_arr as $cError_arr) {
                foreach ($cError_arr as $cError) {
                    array_push($cResult_arr, $cError);
                }
            }
        }
        return $cResult_arr;
    }

    protected function rule_required($xValue, $xOptParam)
    {
        $cValue = trim($xValue);
        if (!isset($xValue)) {
            return false;
        } elseif (is_string($xValue) && strlen($xValue) > 0) {
            return true;
        } elseif (is_numeric($xValue)) {
            return true;
        }

        return false;
    }

    protected function rule_minlength($xValue, $nMinLength)
    {
        if (isset($xValue) && strlen($xValue) >= intval($nMinLength)) {
            return true;
        }
        return false;
    }

    protected function rule_maxlength($xValue, $nMaxLength)
    {
        if (isset($xValue) && strlen($xValue) <= intval($nMaxLength)) {
            return true;
        }
        return false;
    }

    protected function rule_email($xValue, $xOptParam)
    {
        $cBlacklist_arr = array(",", " ");

        foreach ($cBlacklist_arr as $cNeedle) {
            if (strpos($xValue, $cNeedle)) {
                return false;
            }
        }

        $cEmail_reg = "^[a-z0-9_\+-]+(\.[a-z0-9_\+-]+)*@[a-z0-9üöä-]+(\.[a-z0-9üöä-]+)*\.([a-z]{2,4})$^";
        if (preg_match($cEmail_reg, strtolower($xValue))) {
            return true;
        }
        return false;
    }
}
