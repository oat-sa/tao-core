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
 * Copyright (c) 2020 (original work) (update and modification) Open Assessment Technologies SA (under the project TAO-PRODUCT)
 *
 */

namespace oat\tao\model\oauth\lockout;

/**
 * Lock based on IP
 * @package oat\tao\model\oauth\lockout
 */
class IPFactory
{
    /** @var array */
    private $flags;
    /** @var string */
    private $ip = '';

    public function __construct(array $serverFlags = [])
    {
        $this->flags = $serverFlags;
    }

    /**
     * @return string
     */
    public function create(): string
    {
        if (empty($this->ip) && $this->flags) {
            foreach ($this->flags as $flag) {
                if (!empty($_SERVER[$flag])) {
                    $this->ip = $_SERVER[$flag];
                    break;
                }
            }
        }
        if (empty($this->ip)) {
            $this->ip = $_SERVER['HTTP_CLIENT_IP']
                ?? $_SERVER['HTTP_X_FORWARDED_FOR']
                ?? $_SERVER['REMOTE_ADDR'];
        }
        return $this->ip;
    }
}
