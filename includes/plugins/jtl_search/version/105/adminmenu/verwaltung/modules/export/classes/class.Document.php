<?php

/**
 *
 * @author andre
 */
abstract class Document
{
    private function toJSON($bJSONAsObject = false)
    {
        $oObject = new stdClass();
        foreach (get_object_vars($this) as $cKey => $cValue) {
            if (isset($cValue)) {
                if (is_array($cValue)) {
                    foreach ($cValue as $key => $value) {
                        if (is_object($value)) {
                            $oObject->{$cKey}[$key] = $value->toJSON(true);
                        } else {
                            $oObject->{$cKey}[$key] = $this->convertUTF8($value);
                        }
                    }
                } else {
                    $oObject->{$cKey} = $this->convertUTF8($cValue);
                }
            }
        }
        if ($bJSONAsObject) {
            return $oObject;
        } else {
            $oObject->cObjectType = $this->getClassName();
            return json_encode($oObject);
        }
    }

    private function toObject($oObject)
    {
        if (isset($oObject->cObjectType) && $oObject->cObjectType == $this->getClassName()) {
            unset($oObject->cObjectType);
            foreach (get_object_vars($oObject) as $cKey => $cValue) {
                if (isset($cValue)) {
                    $this->{$cKey} = $cValue;
                }
            }
        } else {
            return false;
        }
        //$oObject->cObjectTyp = $this->getClassName();
        return $oObject;
    }

    public function __construct($oObject = null)
    {
        if (isset($oObject) && is_object($oObject)) {
            $this->toObject($oObject);
        }
    }

    public function __toString()
    {
        return $this->convertUTF8($this->toJSON()."\n");
    }

    protected function prepareString($cString)
    {
        return trim(strip_tags(str_replace('>', '> ', $cString)));
    }

    public static function getObject($oObject)
    {
        if (file_exists(PFAD_CLASSES.'class.'.  $oObject->cObjectType. '.php')) {
            require_once(PFAD_CLASSES.'class.'.  $oObject->cObjectType. '.php');
            return new $oObject->cObjectType($oObject);
        } else {
            return false;
        }
    }

    protected function convertUTF8($cData)
    {
        return mb_convert_encoding($cData, "UTF-8", mb_detect_encoding($cData, "UTF-8, ISO-8859-1, ISO-8859-15", true));
    }
}
