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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 *
 * @author Sergei Mikhailov <sergei.mikhailov@taotesting.com>
 */

declare(strict_types=1);

use oat\tao\model\Lists\Business\Contract\ValueCollectionSearchRepositoryInterface;
use oat\tao\model\Lists\Business\Domain\ValueCollectionSearchRequest;

class tao_actions_PropertyValues extends tao_actions_CommonModule
{
    public function get()
    {
        if (!$this->hasRequestParameter('propertyUri')) {
            throw new common_exception_BadRequest('propertyUri is required');
        }

        /** @var ValueCollectionSearchRepositoryInterface $repository */
        $repository = $this->getServiceLocator()->get(ValueCollectionSearchRepositoryInterface::SERVICE_ID);

        // TODO: Extract concerns into a request handler
        $searchRequest = new ValueCollectionSearchRequest(
            tao_helpers_Uri::decode($this->getRequestParameter('propertyUri'))
        );

        if ($this->hasRequestParameter('exclude')) {
            $searchRequest->setExcluded(
                array_map(
                    [tao_helpers_Uri::class, 'decode'],
                    $this->getRequestParameter('exclude')
                )
            );
        }

        if ($this->hasRequestParameter('subject')) {
            $searchRequest->setSubject($this->getRequestParameter('subject'));
        }

        // TODO: Extract concerns into a responder
        $this->returnJson(
            [
                'values' => $repository->findAll(
                    $searchRequest
                ),
            ]
        );
    }
}
