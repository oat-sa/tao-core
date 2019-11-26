<?php

declare(strict_types=1);

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
 * Copyright (c) 2008-2010 (original work) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 */

/**
 * Short description of class tao_helpers_form_data_StoredFileDescription
 *
 * @access public
 * @author Jerome Bogaerts, <jerome@taotesting.com>
 * @package tao
 */
class tao_helpers_form_data_StoredFileDescription extends tao_helpers_form_data_FileDescription
{
    /**
     * Initialise stored file description
     *
     * @param string $serial
     */
    public function __construct($serial)
    {
        $this->setFile($serial);
    }
}
