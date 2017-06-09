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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\tao\model\cliArgument\argument\implementation\verbose;

use Psr\Log\LogLevel;

class Error extends Verbose
{
    /**
     * Check if params array contains targeted arguments, Short and Long
     * In case of Long, check is done is following param argument
     *
     * @param array $params
     * @return bool
     */
    public function isApplicable(array $params)
    {
        $this->setOutputColorVisibility($params);

        return $this->hasParameter($params, '-v') || $this->hasParameter($params, '--verbose', 1);
    }

    /**
     * Return the Psr3 logger level minimum to send log to logger
     *
     * @return string
     */
    public function getMinimumLogLevel()
    {
        return LogLevel::ERROR;
    }
}
