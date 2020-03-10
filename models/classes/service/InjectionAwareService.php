<?php declare(strict_types=1);

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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\model\service;

use common_Utils;
use oat\oatbox\service\ConfigurableService;

abstract class InjectionAwareService extends ConfigurableService
{
    /** @noinspection MagicMethodsValidityInspection */
    public function __toPhpCode(): string
    {
        return sprintf(
            "new %s(\n%s\n)",
            static::class,
            implode(",\n", $this->getSerializedDependencies())
        );
    }

    private function getSerializedDependencies(): array
    {
        return array_map(
            [common_Utils::class, 'toPHPVariableString'],
            $this->getDependencies()
        );
    }

    /**
     * @return array A list of dependencies to be injected in their order.
     */
    protected function getDependencies(): array
    {
        return [];
    }
}
