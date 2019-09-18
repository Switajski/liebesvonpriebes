<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

class AdminIO extends IO
{
    /**
     * @var AdminAccount
     */
    protected $oAccount = null;

    /**
     * @param $oAccount
     * @return $this
     */
    public function setAccount($oAccount)
    {
        $this->oAccount = $oAccount;

        return $this;
    }

    /**
     * @param string $name
     * @param null $function
     * @param null $include
     * @param null $permission
     * @return $this
     * @throws Exception
     */
    public function register($name, $function = null, $include = null, $permission = null)
    {
        parent::register($name, $function, $include);
        $this->functions[$name][] = $permission;

        return $this;
    }

    /**
     * @param string $name
     * @param mixed $params
     * @return mixed
     * @throws Exception
     */
    public function execute($name, $params)
    {
        if (!$this->exists($name)) {
            return new IOError("Function not registered");
        }

        $permission = $this->functions[$name][2];

        if ($permission !== null && !$this->oAccount->permission($permission)) {
            return new IOError("User has not the required permission to execute this function", 401);
        }

        return parent::execute($name, $params);
    }
}