<?php
/**
 * Defaults for new template settings in productlist
 *
 * @author fp
 * @created Tue, 13 Jun 2017 14:48:59 +0200
 */

/**
 * Migration
 *
 * Available methods:
 * execute            - returns affected rows
 * fetchOne           - single fetched object
 * fetchAll           - array of fetched objects
 * fetchArray         - array of fetched assoc arrays
 * dropColumn         - drops a column if exists
 * addLocalization    - add localization
 * removeLocalization - remove localization
 * setConfig          - add / update config property
 * removeConfig       - remove config property
 */
class Migration_20170613144859 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = 'Defaults for new template settings in productlist';

    public function up()
    {
        $template = Template::getInstance();
        $config   = $template->getConfig();

        if ($template->xmlData->cName === 'Evo' || $template->xmlData->cParent === 'Evo') {
            if (!isset($config['productlist']['variation_select_productlist'])) {
                $template->setConfig($template->xmlData->cOrdner, 'productlist', 'variation_select_productlist', 'N');
            }
            if (!isset($config['productlist']['variation_select_productlist'])) {
                $template->setConfig($template->xmlData->cOrdner, 'productlist', 'quickview_productlist', 'N');
            }
            if (!isset($config['productlist']['variation_select_productlist'])) {
                $template->setConfig($template->xmlData->cOrdner, 'productlist', 'hover_productlist', 'N');
            }
        }
    }

    public function down()
    {
        $template = Template::getInstance();
        $this->execute("DELETE FROM ttemplateeinstellungen WHERE cTemplate = '" . $template->xmlData->cOrdner . "' AND cSektion = 'productlist'");
    }
}
