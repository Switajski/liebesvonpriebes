<?php

/**
 * A private class. All the other line-dots inherit from this.
 * Gives them all some common methods.
 */
class dot_base
{
    /**
     * @param string $type
     * @param int    $value
     */
    public function __construct($type, $value = null)
    {
        $this->type = $type;
        if (isset($value)) {
            $this->value($value);
        }
    }

    /**
     * For line charts that only require a Y position
     * for each point.
     * @param int $value - the Y position
     */
    public function value($value)
    {
        $this->value = $value;
    }

    /**
     * For scatter charts that require an X and Y position for
     * each point.
     *
     * @param int $x
     * @param int $y
     */
    public function position($x, $y)
    {
        $this->x = $x;
        $this->y = $y;
    }

    /**
     * @param string $colour - HEX colour, e.g. '#FF0000' red
     * @return $this
     */
    public function colour($colour)
    {
        $this->colour = $colour;

        return $this;
    }

    /**
     * The tooltip for this dot.
     *
     * @param string $tip
     * @return $this
     */
    public function tooltip($tip)
    {
        $this->tip = $tip;

        return $this;
    }

    /**
     * @param int $size - Size of the dot
     * @return $this
     */
    public function size($size)
    {
        $tmp        = 'dot-size';
        $this->$tmp = $size;

        return $this;
    }

    /**
     * a private method
     *
     * @param string $type
     * @return $this
     */
    public function type($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @param int $size - The size of the hollow 'halo' around the dot that masks the line.
     * @return $this
     */
    public function halo_size($size)
    {
        $tmp        = 'halo-size';
        $this->$tmp = $size;

        return $this;
    }

    /**
     * @param string $do - One of three options (examples):
     *  - "http://example.com" - browse to this URL
     *  - "https://example.com" - browse to this URL
     *  - "trace:message" - print this message in the FlashDevelop debug pane
     *  - all other strings will be called as Javascript functions, so a string "hello_world"
     *  will call the JS function "hello_world(index)". It passes in the index of the
     *  point.
     */
    public function on_click($do)
    {
        $tmp        = 'on-click';
        $this->$tmp = $do;
    }
}

/**
 * Class hollow_dot
 */
class hollow_dot extends dot_base
{
    /**
     * hollow_dot constructor.
     * @param null $value
     */
    public function __construct($value = null)
    {
        parent::__construct('hollow-dot', $value);
    }
}

/**
 * Class star
 */
class star extends dot_base
{
    /**
     * star constructor.
     * @param null $value
     */
    public function __construct($value = null)
    {
        parent::__construct('star', $value);
    }

    /**
     * @param int $angle
     * @return $this
     */
    public function rotation($angle)
    {
        $this->rotation = $angle;

        return $this;
    }

    /**
     * @param bool $is_hollow
     */
    public function hollow($is_hollow)
    {
        $this->hollow = $is_hollow;
    }
}

/**
 * Class bow
 */
class bow extends dot_base
{
    /**
     * bow constructor.
     * @param null $value
     */
    public function __construct($value = null)
    {
        parent::__construct('bow', $value);
    }

    /**
     * Rotate the anchor object.
     *
     * @param int $angle
     * @return $this
     */
    public function rotation($angle)
    {
        $this->rotation = $angle;

        return $this;
    }
}

/**
 * Class anchor
 */
class anchor extends dot_base
{
    /**
     * anchor constructor.
     * @param null $value
     */
    public function __construct($value = null)
    {
        parent::__construct('anchor', $value);
    }

    /**
     * Rotate the anchor object.
     *
     * @param int $angle
     * @return $this
     */
    public function rotation($angle)
    {
        $this->rotation = $angle;

        return $this;
    }

    /**
     * @param int $sides - Number of sides this shape has.
     * @return $this
     */
    public function sides($sides)
    {
        $this->sides = $sides;

        return $this;
    }
}

/**
 * Class dot
 */
class dot extends dot_base
{
    /**
     * dot constructor.
     * @param null $value
     */
    public function __construct($value = null)
    {
        parent::__construct('dot', $value);
    }
}

/**
 * Class solid_dot
 */
class solid_dot extends dot_base
{
    /**
     * solid_dot constructor.
     * @param null $value
     */
    public function __construct($value = null)
    {
        parent::__construct('solid-dot', $value);
    }
}
