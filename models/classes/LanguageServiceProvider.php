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
 * Copyright (c) 2021-2022 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\model;

use oat\generis\model\data\Ontology;
use oat\generis\model\DependencyInjection\ContainerServiceProviderInterface;
use oat\tao\model\Language\Business\Contract\LanguageRepositoryInterface;
use oat\tao\model\Language\Repository\LanguageRepository;
use Symfony\Component\DependencyInjection\Loader\Configurator\Traits\FactoryTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use tao_models_classes_LanguageService;
use oat\tao\model\Language\AscendingLabelListSorterComparator;
use oat\tao\model\Language\Service\LanguageListElementSortService;
use oat\tao\model\Language\Business\Specification\LanguageClassSpecification;

use function Symfony\Component\DependencyInjection\Loader\Configurator\inline_service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

class LanguageServiceProvider implements ContainerServiceProviderInterface
{
    use FactoryTrait;

    public function __invoke(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();
        $services
            ->set(tao_models_classes_LanguageService::class, tao_models_classes_LanguageService::class)
            ->public()
            ->factory(tao_models_classes_LanguageService::class . '::singleton');

        $services
            ->set(LanguageClassSpecification::class, LanguageClassSpecification::class)
            ->public();

        $services
            ->set(LanguageListElementSortService::class, LanguageListElementSortService::class)
            ->public()
            ->args([
                inline_service(AscendingLabelListSorterComparator::class),
            ]);

        $services
            ->set(LanguageRepositoryInterface::class, LanguageRepository::class)
            ->public()
            ->args(
                [
                    service(Ontology::SERVICE_ID),
                    service(tao_models_classes_LanguageService::class),
                ]
            );
    }
}
