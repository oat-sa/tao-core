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
 *
 *
 */

namespace oat\tao\helpers;

use oat\oatbox\service\ConfigurableService;

class NamespaceHelper extends ConfigurableService
{
    public const SERVICE_ID = 'tao/Namespaces';
    private const OPTION_NAME_SPACES = 'nameSpaces';

    /** @var array */
    private $nameSpaces = [];

    public function getNameSpaces(): array
    {
        if (empty($this->nameSpaces)) {
            $configNamespaces = $this->getConfigNamespaces();
            if (!empty($configNamespaces)) {
                $this->addNameSpaces($configNamespaces);
            }
        }
        return $this->nameSpaces;
    }

    public function addNameSpaces(array $nameSpaces = []): void
    {
        $this->nameSpaces = array_merge($this->nameSpaces, $nameSpaces);
    }

    private function getConfigNamespaces(): array
    {
        $result = [];
        if (defined(LOCAL_NAMESPACE)) {
            array_push($result, LOCAL_NAMESPACE);
        }
        $configurableNamespaces = $this->getOption(self::OPTION_NAME_SPACES);

        return array_merge($result, $configurableNamespaces);
    }
}
