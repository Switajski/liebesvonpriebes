<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class MigrationTableTrait
 */
trait MigrationTableTrait
{
    /**
     * @return array
     */
    public function getLocaleSections()
    {
        $result = [];
        $items  = $this->fetchAll("SELECT kSprachsektion AS id, cName AS name FROM tsprachsektion");
        foreach ($items as $item) {
            $result[$item->name] = $item->id;
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getLocales()
    {
        $result = [];
        $items  = $this->fetchAll("SELECT kSprachISO AS id, cISO AS name FROM tsprachiso");
        foreach ($items as $item) {
            $result[$item->name] = $item->id;
        }

        return $result;
    }

    /**
     * @param $table
     * @param $column
     */
    public function dropColumn($table, $column)
    {
        try {
            $this->execute("ALTER TABLE `{$table}` DROP `{$column}`");
        } catch (Exception $e) {
        }
    }

    /**
     * Add or update a row in tsprachwerte
     *
     * @param string $locale locale iso code e.g. "ger"
     * @param string $section section e.g. "global". See tsprachsektion for all sections
     * @param string $key unique name to identify localization
     * @param string $value localized text
     * @param bool   $system optional flag for system-default.
     * @throws Exception if locale key or section is wrong
     */
    public function setLocalization($locale, $section, $key, $value, $system = true)
    {
        $locales  = $this->getLocales();
        $sections = $this->getLocaleSections();

        if (!isset($locales[$locale])) {
            throw new Exception("Locale key '{$locale}' not found");
        }

        if (!isset($sections[$section])) {
            throw new Exception("section name '{$section}' not found");
        }

        $this->execute("INSERT INTO tsprachwerte SET
            kSprachISO = '{$locales[$locale]}', 
            kSprachsektion = '{$sections[$section]}', 
            cName = '{$key}', 
            cWert = '{$value}', 
            cStandard = '{$value}', 
            bSystem = '{$system}' 
            ON DUPLICATE KEY UPDATE 
                cWert = IF(cWert = cStandard, VALUES(cStandard), cWert), cStandard = VALUES(cStandard)"
        );
    }

    /**
     * @param string $key
     */
    public function removeLocalization($key)
    {
        $this->execute("DELETE FROM tsprachwerte WHERE cName='{$key}'");
    }

    /**
     * @return array
     */
    private function getAvailableInputTypes()
    {
        $result = [];
        $items  = $this->fetchAll("
            SELECT DISTINCT cInputTyp 
                FROM `teinstellungenconf` 
                WHERE cInputTyp IS NOT NULL 
                    AND cInputTyp != ''"
        );
        foreach ($items as $item) {
            $result[] = $item->cInputTyp;
        }

        return $result;
    }

    /**
     * @param string $table
     * @param string $column
     * @return mixed
     */
    private function getLastId($table, $column)
    {
        $result = $this->fetchOne(" SELECT `$column` as last_id FROM `$table` ORDER BY `$column` DESC LIMIT 1");

        return ++$result->last_id;
    }

    /**
     * @param string      $configName internal config name
     * @param string      $configValue default config value
     * @param int         $configSection config section
     * @param string      $externalName displayed config name
     * @param string      $inputType config input type (set to NULL and set additionalProperties->cConf to "N" for section header)
     * @param int         $sort internal sorting number
     * @param object|null $additionalProperties
     * @param bool        $overwrite force overwrite of already existing config
     * @throws Exception
     */
    public function setConfig(
        $configName,
        $configValue,
        $configSection,
        $externalName,
        $inputType,
        $sort,
        $additionalProperties = null,
        $overwrite = false
    ) {
        $availableInputTypes = $this->getAvailableInputTypes();

        //input types that need $additionalProperties->inputOptions
        $inputTypeNeedsOptions = ['listbox', 'selectbox'];

        $kEinstellungenConf = (!is_object($additionalProperties) ||
            !isset($additionalProperties->kEinstellungenConf) ||
            !$additionalProperties->kEinstellungenConf)
            ? $this->getLastId('teinstellungenconf', 'kEinstellungenConf')
            : $additionalProperties->kEinstellungenConf;
        if (!$configName) {
            throw new Exception('configName not provided or empty / zero');
        } elseif (!$configSection) {
            throw new Exception('configSection not provided or empty / zero');
        } elseif (!$externalName) {
            throw new Exception('externalName not provided or empty / zero');
        } elseif (!$sort) {
            throw new Exception('sort not provided or empty / zero');
        } elseif (!$inputType && (!is_object($additionalProperties) ||
                !isset($additionalProperties->cConf) ||
                $additionalProperties->cConf != 'N')
        ) {
            throw new Exception('inputType has to be provided if additionalProperties->cConf is not set to "N"');
        } elseif (in_array($inputType, $inputTypeNeedsOptions) &&
            (!is_object($additionalProperties) || !isset($additionalProperties->inputOptions) ||
                !is_array($additionalProperties->inputOptions) || count($additionalProperties->inputOptions) == 0)
        ) {
            throw new Exception('additionalProperties->inputOptions has to be provided if inputType is "' . $inputType . '"');
        } elseif ($overwrite !== true) {
            $count = $this->fetchOne("
                SELECT COUNT(*) AS count 
                    FROM teinstellungen 
                    WHERE cName='{$configName}'"
            );
            if ($count->count != 0) {
                throw new Exception('another entry already present in teinstellungen and overwrite is disabled');
            }
            $count = $this->fetchOne("
                SELECT COUNT(*) AS count 
                    FROM teinstellungenconf 
                    WHERE cWertName='{$configName}' 
                        OR kEinstellungenConf={$kEinstellungenConf}"
            );
            if ($count->count != 0) {
                throw new Exception('another entry already present in teinstellungenconf and overwrite is disabled');
            }
            $count = $this->fetchOne("
                SELECT COUNT(*) AS count 
                    FROM teinstellungenconfwerte 
                    WHERE kEinstellungenConf={$kEinstellungenConf}"
            );
            if ($count->count != 0) {
                throw new Exception('another entry already present in teinstellungenconfwerte and overwrite is disabled');
            }

            unset($count);

            // $overwrite has to be set to true in order to create a new inputType
            if (!in_array($inputType, $availableInputTypes) &&
                (!is_object($additionalProperties) ||
                    !isset($additionalProperties->cConf) ||
                    $additionalProperties->cConf !== 'N')
            ) {
                throw new Exception('inputType "' . $inputType .
                    '" not in available types and additionalProperties->cConf is not set to "N"');
            }
        }
        $this->removeConfig($configName);

        $cConf             = (!is_object($additionalProperties) ||
            !isset($additionalProperties->cConf) ||
            $additionalProperties->cConf != 'N')
            ? 'Y'
            : 'N';
        $inputType         = $cConf === 'N'
            ? ''
            : $inputType;
        $cModulId          = (!is_object($additionalProperties) || !isset($additionalProperties->cModulId))
            ? '_DBNULL_'
            : $additionalProperties->cModulId;
        $cBeschreibung     = (!is_object($additionalProperties) || !isset($additionalProperties->cBeschreibung))
            ? ''
            : $additionalProperties->cBeschreibung;
        $nStandardAnzeigen = (!is_object($additionalProperties) || !isset($additionalProperties->nStandardAnzeigen))
            ? 1
            : $additionalProperties->nStandardAnzeigen;
        $nModul            = (!is_object($additionalProperties) || !isset($additionalProperties->nModul))
            ? 0
            : $additionalProperties->nModul;

        $einstellungen                        = new stdClass();
        $einstellungen->kEinstellungenSektion = $configSection;
        $einstellungen->cName                 = $configName;
        $einstellungen->cWert                 = $configValue;
        $einstellungen->cModulId              = $cModulId;
        Shop::DB()->insertRow('teinstellungen', $einstellungen, true);
        unset($einstellungen);

        $einstellungenConf                        = new stdClass();
        $einstellungenConf->kEinstellungenConf    = $kEinstellungenConf;
        $einstellungenConf->kEinstellungenSektion = $configSection;
        $einstellungenConf->cName                 = $externalName;
        $einstellungenConf->cBeschreibung         = $cBeschreibung;
        $einstellungenConf->cWertName             = $configName;
        $einstellungenConf->cInputTyp             = $inputType;
        $einstellungenConf->cModulId              = $cModulId;
        $einstellungenConf->nSort                 = $sort;
        $einstellungenConf->nStandardAnzeigen     = $nStandardAnzeigen;
        $einstellungenConf->nModul                = $nModul;
        $einstellungenConf->cConf                 = $cConf;
        Shop::DB()->insertRow('teinstellungenconf', $einstellungenConf, true);
        unset($einstellungenConf);

        if (is_object($additionalProperties) &&
            isset($additionalProperties->inputOptions) &&
            is_array($additionalProperties->inputOptions)
        ) {
            $sortIndex              = 1;
            $einstellungenConfWerte = new stdClass();
            foreach ($additionalProperties->inputOptions as $optionKey => $optionValue) {
                $einstellungenConfWerte->kEinstellungenConf = $kEinstellungenConf;
                $einstellungenConfWerte->cName              = $optionValue;
                $einstellungenConfWerte->cWert              = $optionKey;
                $einstellungenConfWerte->nSort              = $sortIndex;
                Shop::DB()->insertRow('teinstellungenconfwerte', $einstellungenConfWerte, true);
                $sortIndex++;
            }
            unset($einstellungenConfWerte);
        }
    }

    /**
     * @param string $key the key name to be removed
     */
    public function removeConfig($key)
    {
        $this->execute("DELETE FROM teinstellungen WHERE cName = '{$key}'");
        $this->execute("
            DELETE FROM teinstellungenconfwerte 
                WHERE kEinstellungenConf = (
                    SELECT kEinstellungenConf 
                        FROM teinstellungenconf 
                        WHERE cWertName = '{$key}'
                )"
        );
        $this->execute("DELETE FROM teinstellungenconf WHERE cWertName = '{$key}'");
    }
}
