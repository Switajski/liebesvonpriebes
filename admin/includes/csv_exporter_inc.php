<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * If the "Export CSV" button was clicked with the id $exporterId, offer a CSV download and stop execution of current
 * script. Call this function as soon as you can provide data to be exported but before any page output has been done!
 * Call this function for each CSV exporter on a page with its unique $exporterId!
 *
 * @param string $exporterId
 * @param string $csvFilename
 * @param array|callable $source - array of objects to be exported as CSV or function that gives that array back on
 *      demand
 * @param array $fields - array of property/column names to be included or empty array for all columns (taken from
 *      first item of $source)
 * @param array $excluded - array of property/column names to be excluded
 * @param string $delim
 * @param bool   $bHead
 * @return bool - false = failure or exporter-id-mismatch
 */
function handleCsvExportAction($exporterId, $csvFilename, $source, $fields = [], $excluded = [], $delim = ',',
    $bHead = true)
{
    if (validateToken() && verifyGPDataString('exportcsv') === $exporterId) {
        if (is_callable($source)) {
            $arr = $source();
        } elseif (is_array($source)) {
            $arr = $source;
        } else {
            return false;
        }

        if (count($fields) === 0) {
            $fields = array_diff(array_keys(get_object_vars($arr[0])), $excluded);
        }

        header('Content-Disposition: attachment; filename=' . $csvFilename);
        header('Content-Type: text/csv');
        $fs = fopen('php://output', 'w');

        if ($bHead) {
            fputcsv($fs, $fields);
        }

        foreach ($arr as $elem) {
            $csvRow = [];

            foreach ($fields as $field) {
                $csvRow[] = (string)$elem->$field;
            }

            fputcsv($fs, $csvRow, $delim);
        }
        exit();
    }

    return false;
}
