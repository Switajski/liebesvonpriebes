<?php

/**
 * Class bar_base
 */
class bar_base
{
    /**
     * bar_base constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param string $text - the key text
     * @param int    $size - size in pixels
     */
    public function set_key($text, $size)
    {
        $this->text = $text;
        $tmp = 'font-size';
        $this->$tmp = $size;
    }

    /**
     * @param string $text
     * @param int    $size
     */
    public function key($text, $size)
    {
        $this->set_key($text, $size);
    }

    /**
     * @param array $v - a mix of:
     * 	- a bar_value class. You can use this to customise the paramters of each bar.
     * 	- integer. This is the Y position of the top of the bar.
     */
    public function set_values($v)
    {
        $this->values = $v;
    }

    /**
     * @param array $v
     */
    public function append_value($v)
    {
        $this->values[] = $v;
    }
    
    /**
     * @param string $colour - a HEX colour, e.g. '#ff0000' red
     */
    public function set_colour($colour)
    {
        $this->colour = $colour;
    }

    /**
     * syntatical sugar
     *
     * @param string $colour
     */
    public function colour($colour)
    {
        $this->set_colour($colour);
    }

    /**
     * @param float $alpha - (range 0 to 1), e.g. 0.5 is half transparent
     */
    public function set_alpha($alpha)
    {
        $this->alpha = $alpha;
    }
    
    /**
     * @param string $tip - the tip to show. May contain various magic variables.
     */
    public function set_tooltip($tip)
    {
        $this->tip = $tip;
    }
    
    /**
     *@param line_on_show $on_show - line_on_show object
     */
    public function set_on_show($on_show)
    {
        $this->{'on-show'} = $on_show;
    }

    /**
     * @param string $text
     */
    public function set_on_click($text)
    {
        $tmp = 'on-click';
        $this->$tmp = $text;
    }

    /**
     *
     */
    public function attach_to_right_y_axis()
    {
        $this->axis = 'right';
    }
}
