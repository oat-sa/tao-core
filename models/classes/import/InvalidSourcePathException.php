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
 * Copyright (c) 2014-2018 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\tao\model\import;

use common_Exception;
use common_exception_UserReadableException;
use Exception;

class InvalidSourcePathException extends common_Exception implements common_exception_UserReadableException
{
    /**
     * @var string
     */
    private $sourcePath;

    /**
     * @param string $basePath
     * @param string $sourcePath
     * @param Exception|null $previous
     */
    public function __construct($basePath, $sourcePath, Exception $previous = null)
    {
        $this->sourcePath = $sourcePath;

        $message = sprintf('The path to the source file "%s" is outside the base path "%s"', $sourcePath, $basePath);

        parent::__construct($message, 0, $previous);

    }

    /**
     * Get the human-readable message for the end-user. It is supposed
     * to be translated and does not contain any confidential information
     * about the system and its sensitive data.
     *
     * @return string A human-readable message.
     */
    public function getUserMessage()
    {
        return __(
            'Invalid path of a source "%s". Path must point to the existed file inside the package.',
            $this->sourcePath
        );
    }
}
