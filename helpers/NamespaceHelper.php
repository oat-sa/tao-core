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
    public const SERVICE_ID = 'tao/NamespaceHelper';
    private const OPTION_NAME_SPACES = 'namespaces';

    /** @var array */
    private $namespaces;

    public function __construct(array $options = [])
    {
        parent::__construct($options);

        $this->namespaces = [];

        if (defined(LOCAL_NAMESPACE)) {
            $this->namespaces[] = LOCAL_NAMESPACE;
        }

        $this->namespaces = array_merge($this->namespaces, $this->getOption(self::OPTION_NAME_SPACES, []));
    }

    public function getNameSpaces(): array
    {
        return $this->namespaces;
    }

    public function addNameSpaces(string ...$namespaces): void
    {
        $this->namespaces = array_merge($this->namespaces, $namespaces);
    }

    public function isNamespaceSupported(string $namespace): bool
    {
        return in_array($namespace, $this->getNamespaces());
    }
}
