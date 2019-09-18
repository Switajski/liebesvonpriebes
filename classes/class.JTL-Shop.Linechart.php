<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Linechart
 */
class Linechart extends Chartdata
{
    /**
     * @var array
     */
    public $series;

    /**
     * @var array
     */
    public $_xAxis;

    /**
     * @param $label
     * @return $this
     */
    public function addAxis($label)
    {
        if ($this->_xAxis === null) {
            $this->_xAxis             = new stdClass();
            $this->_xAxis->categories = [];
        }
        $this->_xAxis->categories[] = $label;

        return $this;
    }

    /**
     * @param        $name
     * @param array  $data
     * @param string $linecolor
     * @param string $color
     * @return $this
     */
    public function addSerie($name, array $data, $linecolor = '#989898', $color = '#F78D23')
    {
        if ($this->_series === null) {
            $this->_series = [];
        }
        $serie            = new stdClass();
        $serie->name      = $name;
        $serie->data      = $data;
        $serie->lineColor = $linecolor;
        $serie->color     = $color;
        $this->_series[]  = $serie;

        return $this;
    }
}
