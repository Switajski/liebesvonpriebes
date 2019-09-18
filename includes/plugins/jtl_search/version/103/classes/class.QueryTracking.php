<?php
class QueryTracking
{
    // Statics
    public static function filterProductKeys(array $Products)
    {
        $Productkeys = array();
        if (is_array($Products) && count($Products) > 0) {
            foreach ($Products as $Product) {
                $Productkeys[] = (int) $Product->nId;
            }
        }
        
        return $Productkeys;
    }
    
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
    
    public static function orderQueryTrackings(array $QueryTrackings)
    {
        if (is_array($QueryTrackings) && count($QueryTrackings) > 0) {
            $QueryTrackingsOrdered = array();
            foreach ($QueryTrackings as $QueryTracking) {
                $QueryTrackingsOrdered[$QueryTracking->nQueryTracking] = $QueryTracking;
            }

            ksort($QueryTrackingsOrdered);
            
            return array_reverse($QueryTrackingsOrdered);
        }
        
        return null;
    }
}
