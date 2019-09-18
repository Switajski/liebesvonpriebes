<?php

/**
 * Class x_axis_labels
 */
class x_axis_labels
{
    /**
     * x_axis_labels constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param int $steps - which labels are generated
     */
    public function set_steps($steps)
    {
        $this->steps = $steps;
    }

    /**
     * @param int $steps - which labels are visible
     * @return $this
     */
    public function visible_steps($steps)
    {
        $this->{"visible-steps"} = $steps;

        return $this;
    }

    /**
     *
     * @param array $labels - array of [x_axis_label or string]
     */
    public function set_labels($labels)
    {
        $this->labels = $labels;
    }

    /**
     * @param string $colour
     */
    public function set_colour($colour)
    {
        $this->colour = $colour;
    }

    /**
     * @param int $size - font size in pixels
     */
    public function set_size($size)
    {
        $this->size = $size;
    }

    /**
     * rotate labels
     */
    public function set_vertical()
    {
        $this->rotate = 270;
    }

    /**
     * @param float @angle - The angle of the text.
     */
    public function rotate($angle)
    {
        $this->rotate = $angle;
    }

    /**
     * @param string $text - Replace and magic variables with actual x axis position.
     */
    public function text($text)
    {
        $this->text = $text;
    }
}
