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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA.
 *
 * @author Andrei Shapiro <andrei.shapiro@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\model\resources\Service;

use InvalidArgumentException;
use oat\tao\model\resources\Contract\ClassCopierInterface;

class ClassCopierManager
{
    public const PRIORITY_HIGHEST = 0;
    public const PRIORITY_HIGH = 1;
    public const PRIORITY_LOW = 2;
    public const PRIORITY_LOWEST = 3;

    /** @var array<int, ClassCopierInterface[]> */
    private $classCopiers = [];

    public function add(ClassCopierInterface $classCopier, int $priority = self::PRIORITY_LOWEST): void
    {
        if ($classCopier instanceof ClassCopierProxy) {
            throw new InvalidArgumentException(
                sprintf(
                    'Proxy "%s" cannot be added the the list.',
                    ClassCopierProxy::class
                )
            );
        }

        if ($priority < self::PRIORITY_HIGHEST || $priority > self::PRIORITY_LOWEST) {
            throw new InvalidArgumentException(
                sprintf(
                    'Priority cannot must be between %d and %d.',
                    self::PRIORITY_HIGHEST,
                    self::PRIORITY_LOWEST
                )
            );
        }

        if (!isset($this->classCopiers[$priority])) {
            $this->classCopiers[$priority] = [];
        }

        $this->classCopiers[$priority][] = $classCopier;
        ksort($this->classCopiers);
    }

    public function all(): array
    {
        return $this->classCopiers;
    }
}
