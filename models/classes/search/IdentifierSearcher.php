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

namespace oat\tao\model\search;

use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\service\ConfigurableService;

class IdentifierSearcher extends ConfigurableService
{
    use OntologyAwareTrait;

    /** @var string */
    private $localNamespace;

    public function withLocalNamespace(string $localNameSpace): self
    {
        $this->localNamespace = $localNameSpace;

        return $this;
    }

    public function search(SearchQuery $query): ResultSet
    {
        if ($this->isUriSearch($query->getTerm())) {
            $resource = $this->getResource($query->getTerm());
            $class = $this->getClass($query->getParentClass());

            if ($resource->exists() && $resource->isInstanceOf($class)) {
                return new ResultSet(
                    [
                        [
                            'id' => $resource->getUri(),
                            'label' => $resource->getLabel(),
                        ],
                    ],
                    1
                );
            }
        }

        return new ResultSet([], 0);
    }

    private function isUriSearch(string $queryString): bool
    {
        $localNameSpace = $this->getLocalNamespace();

        if (!empty($localNameSpace) && strpos($queryString, $localNameSpace) === 0) {
            return true;
        }

        return filter_var($queryString, FILTER_VALIDATE_URL) !== false;
    }

    private function getLocalNamespace(): ?string
    {
        return empty($this->localNamespace) && defined('LOCAL_NAMESPACE')
            ? LOCAL_NAMESPACE
            : $this->localNamespace;
    }
}
