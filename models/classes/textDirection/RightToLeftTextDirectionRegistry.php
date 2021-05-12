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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\textDirection;

use oat\tao\model\ClientLibConfigRegistry;

class RightToLeftTextDirectionRegistry extends ClientLibConfigRegistry
{
    public const ID = 'util/locale';
    public const RTL = 'rtl';

    public function addRtlLocale(string $locale): void
    {
        $allLocales[] = $locale;
        if ($this->isRegistered(self::ID)) {
            $config = $this->get(self::ID);
            if (isset($config[self::RTL])) {
                $allLocales = array_unique(array_merge($allLocales, $config[self::RTL]));
            }
        }

        $this->register(
            self::ID,
            [
                self::RTL => $allLocales
            ]
        );
    }

    public function getRtlLocales(): array
    {
        if (!$this->isRegistered(self::ID) && !isset($this->get(self::ID)[self::RTL])) {
            return [];
        }

        return $this->get(self::ID)[self::RTL];
    }
}
