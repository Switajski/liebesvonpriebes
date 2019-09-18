<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Chartdata
 */
class Chartdata
{
    /**
     * @var bool
     */
    protected $_bActive;

    /**
     * @var object
     */
    protected $_xAxis;

    /**
     * @var array
     */
    protected $_series;

    /**
     * @var string
     */
    protected $_xAxisJSON;

    /**
     * @var string
     */
    protected $_seriesJSON;

    /**
     * @var string
     */
    protected $_url;

    /**
     * @param array $options
     */
    public function __construct(array $options = null)
    {
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    /**
     * @param string $name
     * @param mixed  $value
     * @return $this
     * @throws Exception
     */
    public function __set($name, $value)
    {
        $method = 'set' . $name;
        if (('mapper' == $name) || !method_exists($this, $method)) {
            throw new Exception('Invalid Query property');
        }
        $this->$method($value);

        return $this;
    }

    /**
     * @param string $name
     * @return mixed
     * @throws Exception
     */
    public function __get($name)
    {
        $method = 'get' . $name;
        if (('mapper' == $name) || !method_exists($this, $method)) {
            throw new Exception('Invalid Query property');
        }

        return $this->$method();
    }

    /**
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options)
    {
        $methods = get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (in_array($method, $methods, true)) {
                $this->$method($value);
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $array   = [];
        $members = array_keys(get_object_vars($this));
        foreach ($members as $member) {
            $array[substr($member, 1)] = $this->$member;
        }

        return $array;
    }

    /**
     * @param bool $active
     * @return $this
     */
    public function setActive($active)
    {
        $this->_bActive = (bool) $active;

        return $this;
    }

    /**
     * @param object $axis
     * @return $this
     */
    public function setAxis($axis)
    {
        $this->_xAxis = $axis;

        return $this;
    }

    /**
     * @param array $series
     * @return $this
     */
    public function setSeries($series)
    {
        $this->_series = $series;

        return $this;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->_url = $url;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->_url;
    }

    /**
     * @return bool
     */
    public function getActive()
    {
        return $this->_bActive;
    }

    /**
     * @return object|null
     */
    public function getAxis()
    {
        return $this->_xAxis;
    }

    /**
     * @return array|null
     */
    public function getSeries()
    {
        return $this->_series;
    }

    /**
     * @return string
     */
    public function getAxisJSON()
    {
        return $this->_xAxisJSON;
    }

    /**
     * @return string
     */
    public function getSeriesJSON()
    {
        return $this->_seriesJSON;
    }

    /**
     * @return $this
     */
    public function memberToJSON()
    {
        $this->_seriesJSON = json_encode($this->_series);
        $this->_xAxisJSON  = json_encode($this->_xAxis);

        return $this;
    }
}
