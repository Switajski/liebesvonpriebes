<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

/**
 * Class UnitsOfMeasure
 *
 * @see http://unitsofmeasure.org/ucum.html
 */
class UnitsOfMeasure
{
    /**
     * ucum code to print mapping table
     *
     * @var array
     */
    public static $UCUMcodeToPrint = [
        'm'      => 'm',
        'mm'     => 'mm',
        'cm'     => 'cm',
        'dm'     => 'dm',
        '[in_i]' => '&Prime;', //inch
        'km'     => 'km',
        'kg'     => 'kg',
        'mg'     => 'mg',
        'g'      => 'g',
        't'      => 't',
        'm2'     => 'm<sup>2</sup>', //square meters
        'mm2'    => 'mm<sup>2</sup>',
        'cm2'    => 'cm<sup>2</sup>',
        'L'      => 'l',
        'mL'     => 'ml',
        'dL'     => 'dl',
        'cL'     => 'cl',
        'm3'     => 'm<sup>3</sup>',
        'cm3'    => 'cm<sup>3</sup>'
    ];

    protected static $conversionTable = [
        'mm'  => null,
        'cm'  => [10 => 'mm'],
        'dm'  => [10 => 'cm'],
        'm'   => [10 => 'dm'],
        'km'  => [1000 => 'm'],
        'mg'  => null,
        'g'   => [1000 => 'mg'],
        'kg'  => [1000 => 'g'],
        't'   => [1000 => 'kg'],
        'mL'  => null,
        'cm3' => [1 => 'mL'],
        'cL'  => [10 => 'cm3'],
        'dL'  => [10 => 'cL'],
        'L'   => [10 => 'dL'],
        'm3'  => [1000 => 'L'],
        'mm2' => null,
        'cm2' => [100 => 'mm2'],
        'm2'  => [1000 => 'cm2'],
    ];

    /**
     * @param string $ucumCode
     * @return mixed
     */
    public static function getPrintAbbreviation($ucumCode)
    {
        return ($ucumCode !== null && !empty(self::$UCUMcodeToPrint[$ucumCode]))
            ? self::$UCUMcodeToPrint[$ucumCode]
            : '';
    }

    /**
     * @return stdClass[]
     */
    public static function getUnits()
    {
        static $units = [];

        if (count($units) === 0) {
            $units_tmp = Shop::DB()->query(
                "SELECT kMassEinheit, cCode
                    FROM tmasseinheit
                    WHERE cCode IN ('" . implode("', '", array_keys(self::$UCUMcodeToPrint)) . "')", 2
            );

            if (isset($units_tmp)) {
                foreach ($units_tmp as $unit) {
                    $units[$unit->kMassEinheit] = $unit;
                }
            }
        }

        return $units;
    }

    /**
     * @param int $kMassEinheit
     * @return stdClass|null
     */
    public static function getUnit($kMassEinheit)
    {
        $units = self::getUnits();

        return isset($units[(int)$kMassEinheit]) ? $units[(int)$kMassEinheit] : null;
    }

    /**
     * @param string $unitFrom
     * @param string $unitTo
     * @return int
     */
    private static function iGetConversionFaktor($unitFrom, $unitTo)
    {
        $result = null;

        if (isset(self::$conversionTable[$unitFrom])) {
            $result = key(self::$conversionTable[$unitFrom]);
            $nextTo = current(self::$conversionTable[$unitFrom]);

            if ($nextTo !== $unitTo) {
                $factor = self::iGetConversionFaktor($nextTo, $unitTo);
                $result = $factor === null ? null : $result * $factor;
            }
        }

        return $result;
    }

    /**
     * @param string $unitFrom
     * @param string $unitTo
     * @return int
     */
    public static function getConversionFaktor($unitFrom, $unitTo)
    {
        $result = self::iGetConversionFaktor($unitFrom, $unitTo);

        if ($result === null) {
            $result = self::iGetConversionFaktor($unitTo, $unitFrom);

            return $result === null ? null : 1 / $result;
        }

        return $result;
    }
}
