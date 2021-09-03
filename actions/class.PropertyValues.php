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

use GuzzleHttp\Psr7\ServerRequest;
use oat\tao\model\http\HttpJsonResponseTrait;
use oat\tao\model\Lists\Business\Service\ValueCollectionService;
use oat\tao\model\Lists\Presentation\Web\RequestHandler\ValueCollectionSearchRequestHandler;
use oat\tao\model\Lists\DataAccess\Repository\DependsOnPropertyRepository;
use oat\generis\model\OntologyAwareTrait;

class tao_actions_PropertyValues extends tao_actions_CommonModule
{
    use HttpJsonResponseTrait;
    use OntologyAwareTrait;

    public function get(
        ServerRequest $request,
        ValueCollectionSearchRequestHandler $valueCollectionSearchRequestHandler,
        ValueCollectionService $valueCollectionService
    ): void {
        $this->setSuccessJsonResponse(
            $valueCollectionService->findAll(
                $valueCollectionSearchRequestHandler->handle($request)
            )
        );
    }

    public function getDependOnPropertyList()
    {
        $property = $this->getProperty(tao_helpers_Uri::decode($this->getRequestParameter('property_uri')));
        $listUri = $this->getProperty(tao_helpers_Uri::decode($this->getRequestParameter('list_uri')))->getUri();
        $collection = $this->getRepository()->findAll(
            [
                'property' => $property
            ],
            $listUri
        );

        if ($collection->count() === 0) {
            $this->setSuccessJsonResponse([]);
        }
        
        $options = [];
        
        foreach ($collection as $prop) {
            $encodedUri = $prop->getUriEncoded();
            $options[$encodedUri] = $prop->getLabel();
        }
        asort($options);
        $this->setSuccessJsonResponse($options);
    }

    private function getRepository(): DependsOnPropertyRepository
    {
        return $this->getServiceLocator()->get(DependsOnPropertyRepository::class);
    }
}
