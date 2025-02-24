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
 * Copyright (c) 2025 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

use oat\tao\model\http\HttpJsonResponseTrait;
use oat\tao\model\metadata\exception\MetadataNotExist;
use oat\tao\model\metadata\reader\ResourceMetadataService;

class tao_actions_ResourceMetadata extends tao_actions_CommonModule
{
    use HttpJsonResponseTrait;
    public function get()
    {
        try {
            $this->setSuccessJsonResponse([
                'value' => $this->getResourceMetadataService()->getResourceMetadataValue(
                    tao_helpers_Uri::decode($this->getRawParameter('resourceUri')),
                    tao_helpers_Uri::decode($this->getRawParameter('metadataUri'))
                )
            ]);
        } catch (MetadataNotExist $e) {
            $this->setErrorJsonResponse($e->getMessage(), 404);
        }
    }

    private function getResourceMetadataService(): ResourceMetadataService
    {
        return $this->getPsrContainer()->get(ResourceMetadataService::class);
    }
}
