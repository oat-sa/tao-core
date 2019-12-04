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
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\model\search;

/**
 * Search Syntax Exception
 *
 * @author Joel Bout <joel@taotesting.com>
 */
class SyntaxException extends \common_Exception implements \common_exception_UserReadableException
{
    private $query;

    private $error;

    /**
     * @param unknown $queryString
     * @param unknown $userError
     */
    public function __construct($queryString, $userError)
    {
        $this->query = $queryString;
        $this->error = $userError;
        parent::__construct('Error in query "' . $queryString . '": ' . $userError);
    }

    /**
     * (non-PHPdoc)
     * @see common_exception_UserReadableException::getUserMessage()
     */
    public function getUserMessage()
    {
        return $this->error;
    }
}
