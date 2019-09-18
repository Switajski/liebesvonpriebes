<?php

/**
 * Class line_on_show
 */
class line_on_show
{
    /**
     * @param string $type - Can be any one of:
     * - 'pop-up'
     * - 'explode'
     * - 'mid-slide'
     * - 'drop'
     * - 'fade-in'
     * - 'shrink-in'
     *
     * @param float  $cascade - Cascade in seconds
     * @param float  $delay - Delay before animation starts in seconds.
     */
    public function __construct($type, $cascade, $delay)
    {
        $this->type    = $type;
        $this->cascade = (float)$cascade;
        $this->delay   = (float)$delay;
    }
}

/**
 * Class line
 */
class line
{
    /**
     * line constructor.
     */
    public function __construct()
    {
        $this->type   = "line";
        $this->values = array();
    }

    /**
     * Set the default dot that all the real
     * dots inherit their properties from. If you set the
     * default dot to be red, all values in your chart that
     * do not specify a colour will be red. Same for all the
     * other attributes such as tooltip, on-click, size etc...
     *
     * @param solid_dot $style - any class that inherits base_dot
     */
    public function set_default_dot_style($style)
    {
        $tmp        = 'dot-style';
        $this->$tmp = $style;
    }

    /**
     * @param array $v - can contain any combination of:
     *  - integer, Y position of the point
     *  - any class that inherits from dot_base
     *  - <b>null</b>
     */
    public function set_values($v)
    {
        $this->values = $v;
    }

    /**
     * Append a value to the line.
     *
     * @param mixed $v
     */
    public function append_value($v)
    {
        $this->values[] = $v;
    }

    /**
     * @param int $width
     */
    public function set_width($width)
    {
        $this->width = $width;
    }

    /**
     * @param array $colour
     */
    public function set_colour($colour)
    {
        $this->colour = $colour;
    }

    /**
     * synttical sugar for set_colour
     *
     * @param array $colour
     * @return $this
     */
    public function colour($colour)
    {
        $this->set_colour($colour);

        return $this;
    }

    /**
     * @param int $size
     */
    public function set_halo_size($size)
    {
        $tmp        = 'halo-size';
        $this->$tmp = $size;
    }

    /**
     * @param string $text
     * @param int    $font_size
     */
    public function set_key($text, $font_size)
    {
        $this->text = $text;
        $tmp        = 'font-size';
        $this->$tmp = $font_size;
    }

    /**
     * @param string $tip
     */
    public function set_tooltip($tip)
    {
        $this->tip = $tip;
    }

    /**
     * @param string $text - A javascript function name as a string. The chart will
     * try to call this function, it will pass the chart id as the only parameter into
     * this function. E.g:
     *
     */
    public function set_on_click($text)
    {
        $tmp        = 'on-click';
        $this->$tmp = $text;
    }

    /**
     *
     */
    public function loop()
    {
        $this->loop = true;
    }

    /**
     * @param string $s
     */
    public function line_style($s)
    {
        $tmp        = "line-style";
        $this->$tmp = $s;
    }

    /**
     * Sets the text for the line.
     *
     * @param string $text
     */
    public function set_text($text)
    {
        $this->text = $text;
    }

    /**
     *
     */
    public function attach_to_right_y_axis()
    {
        $this->axis = 'right';
    }

    /**
     * @param line_on_show $on_show
     */
    public function set_on_show($on_show)
    {
        $this->{'on-show'} = $on_show;
    }

    /**
     * @param line_on_show $on_show
     * @return $this
     */
    public function on_show($on_show)
    {
        $this->set_on_show($on_show);

        return $this;
    }
}
