<?php
/**
 * copyright (c) 2006-2011 JTL-Software-GmbH, all rights reserved
 *
 * this file may not be redistributed in whole or significant part
 * and is subject to the JTL-Software-GmbH license.
 *
 * license: http://jtl-software.de/jtlshop3license.html
 */
/**
 * Description of JTLSearchDBInfo
 * Geninjat von Daniel Böhmer
 *
 * @author JTL Software
 */
class JTLSearchDBInfo
{
    /**
     * @access private
     * @var string
     */
    private $cDBHost;

    /**
     * @access private
     * @var string
     */
    private $cDBUser;

    /**
     * @access private
     * @var string
     */
    private $cDBPass;

    /**
     * @access private
     * @var string
     */
    private $cDBName;

    /**
     * @access private
     * @var string
     */
    private $cDBCharset;

    /**
     * Constructor
     *
     * @param string cDBHost, string cDBLogin, string cDBPass, string cDBName
     * @access public
     */
    public function __construct($cDBHost, $cDBUser, $cDBPass, $cDBName, $cDBCharset = 'utf8')
    {
        if (strlen($cDBHost) > 0 && strlen($cDBUser) > 0 && strlen($cDBPass) > 0 && strlen($cDBName) > 0 && strlen($cDBCharset) > 0) {
            $this->cDBHost      = $cDBHost;
            $this->cDBUser      = $cDBUser;
            $this->cDBPass      = $cDBPass;
            $this->cDBName      = $cDBName;
            $this->cDBCharset   = $cDBCharset;
        } else {
            throw new Exception("ERROR: Missing database parameters!");
        }
    }

    /**
     * Gets the cDBHost
     * @access public
     * @return string
     */
    public function getHost()
    {
        return $this->cDBHost;
    }

    /**
     * Gets the cDBUser
     * @access public
     * @return string
     */
    public function getUser()
    {
        return $this->cDBUser;
    }

    /**
     * Gets the cDBPass
     * @access public
     * @return string
     */
    public function getPass()
    {
        return $this->cDBPass;
    }

    /**
     * Gets the cDBName
     * @access public
     * @return string
     */
    public function getName()
    {
        return $this->cDBName;
    }

    /**
     * Gets the cDBCharset
     * @access public
     * @return string
     */
    public function getCharset()
    {
        return $this->cDBCharset;
    }
}
