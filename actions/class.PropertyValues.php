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
 * Copyright (c) 2020-2021 (original work) Open Assessment Technologies SA;
 *
 * @author Sergei Mikhailov <sergei.mikhailov@taotesting.com>
 */

declare(strict_types=1);

use GuzzleHttp\Psr7\ServerRequest;
use oat\generis\model\OntologyAwareTrait;
use oat\tao\model\http\HttpJsonResponseTrait;
use oat\tao\model\Lists\Business\Service\ValueCollectionService;
use oat\tao\model\Lists\DataAccess\Repository\DependsOnPropertyRepository;
use oat\tao\model\Lists\DataAccess\Repository\DependentPropertiesRepository;
use oat\tao\model\Lists\Business\Domain\DependentPropertiesRepositoryContext;
use oat\tao\model\Lists\Presentation\Web\RequestHandler\ValueCollectionSearchRequestHandler;

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

    public function getDependOnPropertyList(): void
    {
        $property = $this->hasGetParameter('property_uri')
            ? $this->getProperty(tao_helpers_Uri::decode($this->getGetParameter('property_uri')))
            : null;

        $class = $this->hasGetParameter('class_uri')
            ? $this->getClass(tao_helpers_Uri::decode($this->getGetParameter('class_uri')))
            : null;

        $this->setSuccessJsonResponse(
            $this->getRepository()->findAll(
                [
                    'property' => $property,
                    'class' => $class,
                    'listUri' => $this->getProperty(tao_helpers_Uri::decode($this->getGetParameter('list_uri')))->getUri()
                ]
            )
        );
    }

    public function getDependentProperties(DependentPropertiesRepository $dependentPropertiesRepository): void
    {
        $property = $this->getProperty(
            tao_helpers_Uri::decode(
                $this->getGetParameter('propertyUri', '')
            )
        );

        $dependentProperties = $dependentPropertiesRepository->findAll(
            new DependentPropertiesRepositoryContext([
                DependentPropertiesRepositoryContext::PARAM_PROPERTY => $property,
            ])
        );

        $this->setSuccessJsonResponse(
            array_map(
                static function (core_kernel_classes_Resource $property) {
                    return [
                        'label' => $property->getLabel(),
                    ];
                },
                $dependentProperties
            )
        );
    }

    private function getRepository(): DependsOnPropertyRepository
    {
        return $this->getServiceLocator()->get(DependsOnPropertyRepository::class);
    }
}
