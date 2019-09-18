<?php
//
// Omar Kilani's php C extension for encoding JSON has been incorporated in stock PHP since 5.2.0
// http://www.aurore.net/projects/php-json/
//
// -- Marcus Engene
//
if (!function_exists('json_encode')) {
    include_once 'JSON.php';
}

include_once 'json_format.php';

// ofc classes
include_once 'ofc_title.php';
include_once 'ofc_y_axis_base.php';
include_once 'ofc_y_axis.php';
include_once 'ofc_y_axis_right.php';
include_once 'ofc_y_axis_labels.php';
include_once 'ofc_y_axis_label.php';
include_once 'ofc_x_axis.php';


include_once 'ofc_pie.php';
//include_once 'ofc_bar.php';
include_once 'ofc_bar_glass.php';
include_once 'ofc_bar_filled.php';
include_once 'ofc_bar_stack.php';
//include_once 'ofc_bar_3d.php';
include_once 'ofc_hbar.php';
include_once 'ofc_line_base.php';
include_once 'ofc_line.php';
//include_once 'ofc_line_dot.php';
//include_once 'ofc_line_hollow.php';
include_once 'ofc_candle.php';
include_once 'ofc_area_base.php';
include_once 'ofc_tags.php';
include_once 'ofc_arrow.php';
//include_once 'ofc_area_hollow.php';
//include_once 'ofc_area_line.php';

include_once 'ofc_x_legend.php';
include_once 'ofc_y_legend.php';
include_once 'ofc_bar_sketch.php';
include_once 'ofc_scatter.php';
include_once 'ofc_scatter_line.php';
include_once 'ofc_x_axis_labels.php';
include_once 'ofc_x_axis_label.php';
include_once 'ofc_tooltip.php';
include_once 'ofc_shape.php';
include_once 'ofc_radar_axis.php';
include_once 'ofc_radar_axis_labels.php';
include_once 'ofc_radar_spoke_labels.php';
include_once 'ofc_line_style.php';

include_once 'dot_base.php';
include_once 'ofc_menu.php';

/**
 * Class open_flash_chart
 */
class open_flash_chart
{
    /**
     * open_flash_chart constructor.
     */
    public function __construct()
    {
        $this->elements = array();
    }

    /**
     * @param string $t
     */
    public function set_title($t)
    {
        $this->title = $t;
    }

    /**
     * @param x_axis|null $x
     */
    public function set_x_axis($x)
    {
        $this->x_axis = $x;
    }

    /**
     * @param y_axis|null $y
     */
    public function set_y_axis($y)
    {
        $this->y_axis = $y;
    }

    /**
     * @param y_axis $y
     */
    public function add_y_axis($y)
    {
        $this->y_axis = $y;
    }

    /**
     * @param int $y
     */
    public function set_y_axis_right($y)
    {
        $this->y_axis_right = $y;
    }

    /**
     * @param area|pie|pie_fade|pie_bounce|pie_value|area_hollow|area_line|bar $e
     */
    public function add_element($e)
    {
        $this->elements[] = $e;
    }

    /**
     * @param string $x
     */
    public function set_x_legend($x)
    {
        $this->x_legend = $x;
    }

    /**
     * @param string $y
     */
    public function set_y_legend($y)
    {
        $this->y_legend = $y;
    }

    /**
     * @param string $colour
     */
    public function set_bg_colour($colour)
    {
        $this->bg_colour = $colour;
    }

    /**
     * @param string $radar
     */
    public function set_radar_axis($radar)
    {
        $this->radar_axis = $radar;
    }

    /**
     * @param string $tooltip
     */
    public function set_tooltip($tooltip)
    {
        $this->tooltip = $tooltip;
    }

    /**
     * This is a bit funky :(
     *
     * @param int  $num_decimals - Truncate the decimals to $num_decimals, e.g. set it
     * to 5 and 3.333333333 will display as 3.33333. 2.0 will display as 2 (or 2.00000 - see below)
     * @param bool $is_fixed_num_decimals_forced - If true it will pad the decimals.
     * @param bool $is_decimal_separator_comma
     * @param bool $is_thousand_separator_disabled
     *
     * This needs a bit of love and attention
     */
    public function set_number_format(
        $num_decimals,
        $is_fixed_num_decimals_forced,
        $is_decimal_separator_comma,
        $is_thousand_separator_disabled
    ) {
        $this->num_decimals                   = $num_decimals;
        $this->is_fixed_num_decimals_forced   = $is_fixed_num_decimals_forced;
        $this->is_decimal_separator_comma     = $is_decimal_separator_comma;
        $this->is_thousand_separator_disabled = $is_thousand_separator_disabled;
    }

    /**
     * This is experimental and will change as we make it work
     *
     * @param ofc_menu $m
     */
    public function set_menu($m)
    {
        $this->menu = $m;
    }

    /**
     * @return mixed|string
     */
    public function toString()
    {
        if (function_exists('json_encode')) {
            return json_encode($this);
        }
        $json = new Services_JSON();

        return $json->encode($this);
    }

    /**
     * @return string
     */
    public function toPrettyString()
    {
        return json_format($this->toString());
    }
}
