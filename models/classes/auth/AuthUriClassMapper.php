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
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\auth;

use DomainException;

class AuthUriClassMapper
{
    private const MAP = [
        BasicType::class => BasicAuth::CLASS_BASIC_AUTH,
        BasicAuthType::class => BasicAuthType::CLASS_BASIC_AUTH
    ];

    /**
     * @param object|string $class
     *
     * @throws DomainException
     * @return string
     */
    public function getUriByClass($class): string
    {
        $className = is_object($class) ? get_class($class) : $class;

        if (!isset(self::MAP[$className])) {
            throw new DomainException(sprintf('Class %s is not defined', $className));
        }

        return self::MAP[$className];
    }
}
