<?php

/**
 * Class y_axis
 */
class y_axis extends y_axis_base
{
    /**
     * y_axis constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param string $colour - The grid are the lines inside the chart.
     * HEX colour, e.g. '#ff0000'
     */
    public function set_grid_colour($colour)
    {
        $tmp        = 'grid-colour';
        $this->$tmp = $colour;
    }
}
