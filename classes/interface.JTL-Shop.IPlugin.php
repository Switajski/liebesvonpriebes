<?php

/**
 * Interface IPlugin
 */
interface IPlugin
{
    /**
     * @param EventDispatcher $dispatcher
     */
    public function boot(EventDispatcher $dispatcher);

    /**
     * @return mixed
     */
    public function installed();

    /**
     * @return mixed
     */
    public function uninstalled();

    /**
     * @return mixed
     */
    public function enabled();

    /**
     * @return mixed
     */
    public function disabled();

    /**
     * @param int         $type
     * @param string      $title
     * @param null|string $description
     */
    public function addNotify($type, $title, $description = null);
}
