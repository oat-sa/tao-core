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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\helpers;

class ApplicationHelper
{
    /**
     * Returns a whenever or not the current instance is used as demo instance
     *
     * @return boolean
     */
    public static  function isDemo() {
        return in_array(TAO_RELEASE_STATUS, array('demo', 'demoA', 'demoB', 'demoS'));
    }

    /**
     * @return string
     */
    public static function getVersionName()
    {
        $version = TAO_VERSION;

        if(is_readable(ROOT_PATH.'build')){
            $content = file_get_contents(ROOT_PATH.'build');
            $version = 'v' . $version;
            $version = is_numeric($content) ? $version. '+build' . $content : $version;
        }

        return $version;
    }
}