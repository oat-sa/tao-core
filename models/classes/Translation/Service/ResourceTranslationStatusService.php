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
 * Copyright (c) 2024 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\Translation\Service;

use InvalidArgumentException;
use oat\generis\model\data\Ontology;
use oat\tao\model\Translation\Entity\ResourceTranslationStatus;
use oat\tao\model\Translation\Query\ResourceTranslationStatusQuery;
use Psr\Http\Message\ServerRequestInterface;

class ResourceTranslationStatusService
{
    private Ontology $ontology;

    public function __construct(Ontology $ontology)
    {
        $this->ontology = $ontology;
    }

    public function getStatus(ResourceTranslationStatusQuery $query): ResourceTranslationStatus
    {
        return new ResourceTranslationStatus($query->getOriginResourceId());
    }

    public function getStatusByRequest(ServerRequestInterface $request): ResourceTranslationStatus
    {
        $uri = $request->getQueryParams()['resourceUri'] ?? ($request->getServerParams()['resourceUri'] ?? null);

        if (empty($uri)) {
            throw new InvalidArgumentException('Param resourceUri is required');
        }

        return $this->getStatus(new ResourceTranslationStatusQuery($uri));
    }
}
