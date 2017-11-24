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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA
 *
 */

namespace oat\tao\model\export;

/**
 * Class ExportElementException define an exception when exporting a resource
 * @package oat\tao\model\export
 */
class ExportElementException extends \Exception implements \common_exception_UserReadableException
{
    /**
     * @param \core_kernel_classes_Resource $element
     */
    private $element;

    /**
     * Message that is save to display to user
     *
     * @var string
     */
    private $userMessage;

    public function __construct(\core_kernel_classes_Resource $element, $userMessage) {
        parent::__construct($userMessage.' '.$element->getUri().' '.$element->getLabel());
        $this->element = $element;
        $this->userMessage = $userMessage;
    }

    /**
     * @return string
     */
    public function getUserMessage() {
        return $this->userMessage;
    }
}
