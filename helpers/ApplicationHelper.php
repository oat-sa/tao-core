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
     * Returns the maximum size for fileuploads in bytes.
     *
     * @return int The upload file limit.
     */
    public static function getFileUploadLimit()
    {

        $max_upload = self::toBytes(ini_get('upload_max_filesize'));
        $max_post = self::toBytes(ini_get('post_max_size'));
        $memory_limit = self::toBytes(ini_get('memory_limit'));

        $returnValue = min($max_upload, $max_post, $memory_limit);

        return (int)$returnValue;
    }

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

    /**
     * Get the size in bytes of a PHP variable given as a string.
     *
     * @param  string $phpSyntax The PHP syntax to describe the variable.
     * @return int The size in bytes.
     */
    private static function toBytes($phpSyntax)
    {
        $val = trim($phpSyntax);
        $last = strtolower($val[strlen($val) - 1]);
        if (!is_numeric($last)) {
            $val = substr($val, 0, -1);
            switch ($last) {
                case 'g':
                    $val *= 1024;
                case 'm':
                    $val *= 1024;
                case 'k':
                    $val *= 1024;
            }
        }
        return $val;
    }
}