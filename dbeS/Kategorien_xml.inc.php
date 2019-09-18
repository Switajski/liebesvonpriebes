<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * update lft/rght values for categories in the nested set model
 *
 * @param int $parent_id
 * @param int $left
 * @param int $level
 * @return int
 */
function rebuildCategoryTree($parent_id, $left, $level = 0)
{
    $left = (int)$left;
    // the right value of this node is the left value + 1
    $right = $left + 1;
    // get all children of this node
    $result = Shop::DB()->selectAll('tkategorie', 'kOberKategorie', (int)$parent_id, 'kKategorie', 'nSort, cName');
    foreach ($result as $_res) {
        $right = rebuildCategoryTree($_res->kKategorie, $right, $level + 1);
    }
    // we've got the left value, and now that we've processed the children of this node we also know the right value
    Shop::DB()->update('tkategorie', 'kKategorie', $parent_id, (object)[
        'lft'    => $left,
        'rght'   => $right,
        'nLevel' => $level,
    ]);

    // return the right value of this node + 1
    return $right + 1;
}

/**
 * @return void
 */
function Kategorien_xml_Finish()
{
    Jtllog::writeLog('Finish Kategorien_xml: updateKategorieLevel, rebuildCategoryTree', JTLLOG_LEVEL_DEBUG);
    rebuildCategoryTree(0, 1);
    Shop::Cache()->flushTags([CACHING_GROUP_CATEGORY]);
}
