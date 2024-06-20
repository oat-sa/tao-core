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
 * Copyright (c) 2024 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\Parser;

use oat\oatbox\filesystem\File;

class Parser
{
    /**
     * Short description of attribute SOURCE_FILE
     *
     * @access public
     * @var int
     */

    public const SOURCE_FILE = 1;

    /**
     * Short description of attribute SOURCE_URL
     *
     * @access public
     * @var int
     */
    public const SOURCE_URL = 2;

    /**
     * Short description of attribute SOURCE_STRING
     *
     * @access public
     * @var int
     */
    public const SOURCE_STRING = 3;

    /**
     * Current file is \oat\oatbox\filesystem\File object
     */
    public const SOURCE_FLYFILE = 4;
    public function setSource($source)
    {
        $sourceType = false;

        if ($source instanceof File) {
            $sourceType = self::SOURCE_FLYFILE;
        } elseif (is_string($source)) {
            if (preg_match("/^<\?xml(.*)?/m", trim($source))) {
                $sourceType = self::SOURCE_STRING;
            } elseif (preg_match("/^http/", $source)) {
                $sourceType = self::SOURCE_URL;
            } elseif (is_file($source)) {
                $sourceType = self::SOURCE_FILE;
            } else {
                $uploadFile = ServiceManager::getServiceManager()
                    ->get(UploadService::SERVICE_ID)
                    ->universalizeUpload($source);
                if ($uploadFile instanceof \oat\oatbox\filesystem\File) {
                    $sourceType = self::SOURCE_FLYFILE;
                    $source = $uploadFile;
                }
            }
        }

        if ($sourceType === false) {
            throw new common_exception_Error(
                "Denied content in the source parameter! " . get_class($this)
                . " accepts either XML content, a URL to an XML Content or the path to a file but got "
                . substr($source, 0, 500)
            );
        }

        $this->sourceType = $sourceType;
        $this->source = $source;

        if (isset($options['extension'])) {
            $this->fileExtension = $options['extension'];
        }
    }

}
