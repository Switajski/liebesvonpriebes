<?php

/**
 * Class x_axis
 */
class x_axis
{
    /**
     * x_axis constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param int $stroke - width of the line and ticks
     */
    public function set_stroke($stroke)
    {
        $this->stroke = $stroke;
    }

    /**
     * @param int $stroke
     * @return $this
     */
    public function stroke($stroke)
    {
        $this->set_stroke($stroke);

        return $this;
    }

    /**
     * @param string $colour - HEX colour
     * @param string $grid_colour - HEX colour
     */
    public function set_colours($colour, $grid_colour)
    {
        $this->set_colour($colour);
        $this->set_grid_colour($grid_colour);
    }

    /**
     * @param string $colour - HEX colour
     */
    public function set_colour($colour)
    {
        $this->colour = $colour;
    }

    /**
     * @param string $colour
     * @return $this
     */
    public function colour($colour)
    {
        $this->set_colour($colour);

        return $this;
    }

    /**
     * @param int $height
     */
    public function set_tick_height($height)
    {
        $tmp        = 'tick-height';
        $this->$tmp = $height;
    }

    /**
     * @param int $height
     * @return $this
     */
    public function tick_height($height)
    {
        $this->set_tick_height($height);

        return $this;
    }

    /**
     * @param string $colour
     */
    public function set_grid_colour($colour)
    {
        $tmp        = 'grid-colour';
        $this->$tmp = $colour;
    }

    /**
     * @param string $colour
     * @return $this
     */
    public function grid_colour($colour)
    {
        $this->set_grid_colour($colour);

        return $this;
    }

    /**
     * @param bool $o - If true, the X axis start half a step in
     * This defaults to True
     */
    public function set_offset($o)
    {
        $this->offset = $o ? true : false;
    }

    /**
     * @param bool $o
     * @return $this
     */
    public function offset($o)
    {
        $this->set_offset($o);

        return $this;
    }

    /**
     * @param int $steps - Which grid lines and ticks are visible.
     */
    public function set_steps($steps)
    {
        $this->steps = $steps;
    }

    /**
     * @param int $steps
     * @return $this
     */
    public function steps($steps)
    {
        $this->set_steps($steps);

        return $this;
    }

    /**
     * @param int $val - the height in pixels of the 3D bar. Mostly
     * used for the 3D bar chart.
     */
    public function set_3d($val)
    {
        $tmp        = '3d';
        $this->$tmp = $val;
    }

    /**
     * Use this to customize the labels (colour, font, etc...)
     *
     * @param x_axis_labels $x_axis_labels
     */
    public function set_labels($x_axis_labels)
    {
        $this->labels = $x_axis_labels;
    }

    /**
     * Sugar syntax: helper function to make the examples simpler.
     *
     * @param array $a
     */
    public function set_labels_from_array($a)
    {
        $x_axis_labels = new x_axis_labels();
        $x_axis_labels->set_labels($a);
        $this->labels = $x_axis_labels;

        if (isset($this->steps)) {
            $x_axis_labels->set_steps($this->steps);
        }
    }

    /**
     * @param int $min
     * @param int $max
     */
    public function set_range($min, $max)
    {
        $this->min = $min;
        $this->max = $max;
    }
}
