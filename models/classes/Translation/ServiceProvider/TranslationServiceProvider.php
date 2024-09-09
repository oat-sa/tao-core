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
 * Copyright (c) 2024 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\model\Translation\ServiceProvider;

use oat\generis\model\data\Ontology;
use oat\generis\model\DependencyInjection\ContainerServiceProviderInterface;
use oat\generis\model\kernel\persistence\smoothsql\search\ComplexSearchService;
use oat\oatbox\log\LoggerService;
use oat\tao\model\form\Modifier\FormModifierManager;
use oat\tao\model\Translation\Factory\ResourceTranslatableFactory;
use oat\tao\model\Translation\Factory\ResourceTranslationFactory;
use oat\tao\model\Translation\Form\Modifier\EditTranslationInstanceFormModifier;
use oat\tao\model\Translation\Repository\ResourceTranslatableRepository;
use oat\tao\model\Translation\Repository\ResourceTranslationRepository;
use oat\tao\model\Translation\Service\ResourceTranslatableRetriever;
use oat\tao\model\Translation\Service\ResourceTranslationRetriever;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

/**
 * @codeCoverageIgnore
 */
class TranslationServiceProvider implements ContainerServiceProviderInterface
{
    public function __invoke(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();
        $services->set(ResourceTranslationRepository::class, ResourceTranslationRepository::class)
            ->args(
                [
                    service(Ontology::SERVICE_ID),
                    service(ComplexSearchService::SERVICE_ID),
                    service(ResourceTranslatableRepository::class),
                    service(ResourceTranslationFactory::class),
                    service(LoggerService::SERVICE_ID),
                ]
            );

        $services->set(ResourceTranslatableRepository::class, ResourceTranslatableRepository::class)
            ->args(
                [
                    service(Ontology::SERVICE_ID),
                    service(ComplexSearchService::SERVICE_ID),
                    service(ResourceTranslatableFactory::class)
                ]
            );

        $services->set(ResourceTranslationFactory::class, ResourceTranslationFactory::class)
            ->args(
                [
                    service(Ontology::SERVICE_ID)
                ]
            );

        $services->set(ResourceTranslatableFactory::class, ResourceTranslatableFactory::class)
            ->args(
                [
                    service(Ontology::SERVICE_ID)
                ]
            );

        $services->set(ResourceTranslationRetriever::class, ResourceTranslationRetriever::class)
            ->args(
                [
                    service(ResourceTranslationRepository::class)
                ]
            )
            ->public();

        $services->set(ResourceTranslatableRetriever::class, ResourceTranslatableRetriever::class)
            ->args(
                [
                    service(ResourceTranslatableRepository::class)
                ]
            )
            ->public();

        $services
            ->set(EditTranslationInstanceFormModifier::class, EditTranslationInstanceFormModifier::class)
            ->args([
                service(Ontology::SERVICE_ID)
            ]);

        $formModifierManager = $services->get(FormModifierManager::class);
        $formModifierManager
            ->call(
                'add',
                [
                    service(EditTranslationInstanceFormModifier::class),
                    EditTranslationInstanceFormModifier::ID,
                ]
            );
    }
}
