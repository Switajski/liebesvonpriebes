<?php

/**
 * Class QueryTracking
 */
class QueryTracking
{
    /**
     * @param array $Products
     * @return array
     */
    public static function filterProductKeys(array $Products)
    {
        $Productkeys = [];
        if (is_array($Products) && count($Products) > 0) {
            foreach ($Products as $Product) {
                $Productkeys[] = (int)$Product->nId;
            }
        }

        return $Productkeys;
    }

    /**
     * @param array $Products
     * @param array $ProductsExist
     * @return int
     */
    public static function addProducts(array $Products, array &$ProductsExist)
    {
        $i = 0;
        if (is_array($Products) && count($Products) > 0 && is_array($ProductsExist)) {
            foreach ($Products as $Product) {
                if (!in_array($Product, $ProductsExist)) {
                    $ProductsExist[] = $Product;
                    $i++;
                }
            }
        }

        return $i;
    }

    /**
     * @param array $QueryTrackings
     * @return array|null
     */
    public static function orderQueryTrackings(array $QueryTrackings)
    {
        if (is_array($QueryTrackings) && count($QueryTrackings) > 0) {
            $QueryTrackingsOrdered = [];
            foreach ($QueryTrackings as $QueryTracking) {
                $QueryTrackingsOrdered[$QueryTracking->nQueryTracking] = $QueryTracking;
            }
            ksort($QueryTrackingsOrdered);

            return array_reverse($QueryTrackingsOrdered);
        }

        return null;
    }
}
