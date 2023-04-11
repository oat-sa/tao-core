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
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA.
 *
 * @author Andrei Shapiro <andrei.shapiro@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\model\clientConfig;

use oat\tao\model\ClientLibConfigRegistry;

class ClientLibConfigSwitcher
{
    private ClientLibConfigRegistry $registry;

    /** @var ClientLibConfigHandlerInterface[] */
    private iterable $handlers;

    public function __construct(ClientLibConfigRegistry $registry, iterable $handlers)
    {
        $this->registry = $registry;
        $this->handlers = $handlers;
    }

    public function getSwitchedClientLibConfig(): array
    {
        $config = $this->registry->getMap();

        foreach ($this->handlers as $handler) {
            if ($handler instanceof ClientLibConfigHandlerInterface) {
                $config = $handler($config);
            }
        }

        return $config;
    }
}
