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
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

declare(strict_types=1);

namespace oat\tao\model\form\DataProvider;

use oat\generis\model\data\Ontology;
use oat\generis\model\DependencyInjection\ContainerServiceProviderInterface;
use oat\generis\model\kernel\persistence\DataProvider\form\FormDTOProviderInterface;
use oat\generis\model\kernel\persistence\starsql\DataProvider\form\FormDTOProvider;
use oat\generis\model\kernel\persistence\starsql\LanguageProcessor;
use oat\oatbox\user\UserLanguageServiceInterface;
use oat\tao\model\Language\Business\Specification\LanguageClassSpecification;
use oat\tao\model\Language\Filter\LanguageAllowedFilter;
use oat\tao\model\Language\Service\LanguageListElementSortService;
use oat\tao\model\Lists\Business\Service\ValueCollectionService;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

/**
 * @codeCoverageIgnore
 */
class FormDataProviderServiceProvider implements ContainerServiceProviderInterface
{
    public function __invoke(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();

        $services->set(FormDTOProviderInterface::class, FormDTOProvider::class)
            ->public()
            ->args(
                [
                    service(Ontology::SERVICE_ID),
                    service(LanguageProcessor::class),
                    service(UserLanguageServiceInterface::SERVICE_ID),
                ]
            );

        $services->set(BulkFormDataProvider::class, BulkFormDataProvider::class)
            ->public()
            ->args(
                [
                    service(Ontology::SERVICE_ID),
                    service(FormDTOProviderInterface::class),
                ]
            );

        $services->set(OntologyFormDataProvider::class, OntologyFormDataProvider::class)
            ->public()
            ->args(
                [
                    service(LanguageClassSpecification::class),
                    service(LanguageListElementSortService::class),
                    service(ValueCollectionService::class),
                    service(LanguageAllowedFilter::class),
                ]
            );

        $services->set(ProxyFormDataProvider::class, ProxyFormDataProvider::class)
            ->public()
            ->args(
                [
                    service(Ontology::SERVICE_ID),
                    service(BulkFormDataProvider::class),
                    service(OntologyFormDataProvider::class),
                ]
            );
    }
}
