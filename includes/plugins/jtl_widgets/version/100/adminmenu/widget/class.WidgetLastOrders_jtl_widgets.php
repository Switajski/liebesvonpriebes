<?php
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_WIDGETS . 'class.WidgetBase.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Bestellung.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'bestellungen_inc.php';

/**
 * Class WidgetLastOrders_jtl_widgets
 */
class WidgetLastOrders_jtl_widgets extends WidgetBase
{
    /**
     *
     */
    public function init()
    {
        $this->oSmarty->assign('oBestellung_arr', gibBestellungsUebersicht(' LIMIT 10', ''));
    }

    /**
     * @return bool|mixed|string
     */
    public function getContent()
    {
        return $this->oSmarty->assign('cDetail', $this->oPlugin->cAdminmenuPfad . '/widget/lastOrdersDetail.tpl')
            ->assign('cDetailPosition', $this->oPlugin->cAdminmenuPfad . '/widget/lastOrdersDetailPosition.tpl')
            ->assign('cAdminmenuPfadURL', $this->oPlugin->cAdminmenuPfadURLSSL)
            ->fetch(__DIR__ . '/widgetLastOrders.tpl');
    }
}
