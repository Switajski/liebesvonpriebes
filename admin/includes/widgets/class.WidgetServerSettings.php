<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_WIDGETS . 'class.WidgetBase.php';

/**
 * Class WidgetServerSettings
 */
class WidgetServerSettings extends WidgetBase
{
    /**
     *
     */
    public function init()
    {
        $this->oSmarty->assign('maxExecutionTime', ini_get('max_execution_time'));
        $this->oSmarty->assign('bMaxExecutionTime', $this->checkMaxExecutionTime());
        $this->oSmarty->assign('maxFilesize', ini_get('upload_max_filesize'));
        $this->oSmarty->assign('bMaxFilesize', $this->checkMaxFilesize());
        $this->oSmarty->assign('memoryLimit', ini_get('memory_limit'));
        $this->oSmarty->assign('bMemoryLimit', $this->checkMemoryLimit());
        $this->oSmarty->assign('postMaxSize', ini_get('post_max_size'));
        $this->oSmarty->assign('bPostMaxSize', $this->checkPostMaxSize());
        $this->oSmarty->assign('bAllowUrlFopen', $this->checkAllowUrlFopen());
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->oSmarty->fetch('tpl_inc/widgets/serversettings.tpl');
    }

    /**
     * @return bool
     * @deprecated - ImageMagick is not required anymore
     */
    public function checkImageMagick()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function checkMaxExecutionTime()
    {
        return Shop()->PHPSettingsHelper()->hasMinExecutionTime(60);
    }

    /**
     * @return bool
     */
    public function checkMaxFilesize()
    {
        return Shop()->PHPSettingsHelper()->hasMinUploadSize(5);
    }

    /**
     * @return bool
     */
    public function checkMemoryLimit()
    {
        return Shop()->PHPSettingsHelper()->hasMinLimit(64);
    }

    /**
     * @return bool
     */
    public function checkPostMaxSize()
    {
        return Shop()->PHPSettingsHelper()->hasMinPostSize(8);
    }

    /**
     * @return bool
     */
    public function checkAllowUrlFopen()
    {
        return Shop()->PHPSettingsHelper()->fopenWrapper();
    }
}
