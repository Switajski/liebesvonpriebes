<?php
/**
 * PHP Integration of Open Flash Chart
 * Copyright (C) 2008 John Glazebrook <open-flash-chart@teethgrinder.co.uk>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 */

require_once('OFC/Elements/OFC_Elements_Axis.php');

class OFC_Elements_Axis_Y extends OFC_Elements_Axis
{
    public function __construct()
    {
        parent::__construct();
    }

    public function set_grid_colour($colour)
    {
        $this->{'grid-colour'} = $colour;
    }

    public function set_stroke($s)
    {
        $this->stroke = $s;
    }

    public function set_tick_length($val)
    {
        $this->{'tick-length'} = $val;
    }

    public function set_range($min, $max, $steps=1)
    {
        $this->min = $min;
        $this->max = $max;
        $this->set_steps($steps);
    }

    public function set_offset($off)
    {
        $this->offset = ($off) ? 1 : 0;
    }

    public function set_labels($labels)
    {
        $this->labels = $labels;
    }
}
