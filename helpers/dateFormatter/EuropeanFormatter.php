<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2015-2017 (original work) Open Assessment Technologies SA;
 *
 *
 */

namespace oat\tao\helpers\dateFormatter;

use tao_helpers_Date as DateHelper;

/**
 * Utility to display dates in european format.
 */
class EuropeanFormatter extends AbstractDateFormatter
{
    protected $datetimeFormats = [
        DateHelper::FORMAT_LONG => 'd/m/Y H:i:s',
        DateHelper::FORMAT_LONG_MICROSECONDS => 'd/m/Y H:i:s.u',
        DateHelper::FORMAT_DATEPICKER => 'Y-m-d H:i',
        DateHelper::FORMAT_VERBOSE => 'F j, Y, g:i:s a',
        DateHelper::FORMAT_ISO8601 => 'Y-m-d\TH:i:s.v',
    ];
}
