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
use oat\generis\model\resource\Service\ResourceDeleter;
use oat\oatbox\event\EventManager;
use oat\oatbox\log\LoggerService;
use oat\oatbox\user\UserLanguageServiceInterface;
use oat\tao\model\featureFlag\FeatureFlagChecker;
use oat\tao\model\featureFlag\Service\FeatureFlagPropertiesMapping;
use oat\tao\model\Language\Business\Contract\LanguageRepositoryInterface;
use oat\tao\model\TaoOntology;
use oat\tao\model\Translation\Factory\ResourceTranslatableFactory;
use oat\tao\model\Translation\Factory\ResourceTranslationFactory;
use oat\tao\model\Translation\Form\Modifier\TranslationFormModifier;
use oat\tao\model\Translation\Repository\ResourceTranslatableRepository;
use oat\tao\model\Translation\Repository\ResourceTranslationRepository;
use oat\tao\model\Translation\Service\ResourceLanguageRetriever;
use oat\tao\model\Translation\Service\ResourceMetadataPopulateService;
use oat\tao\model\Translation\Service\ResourceTranslatableRetriever;
use oat\tao\model\Translation\Service\ResourceTranslatableStatusRetriever;
use oat\tao\model\Translation\Service\ResourceTranslationRetriever;
use oat\tao\model\Translation\Service\TranslatedIntoLanguagesSynchronizer;
use oat\tao\model\Translation\Service\TranslationCreationService;
use oat\tao\model\Translation\Service\TranslationDeletionService;
use oat\tao\model\Translation\Service\TranslationSyncService;
use oat\tao\model\Translation\Service\TranslationUniqueIdSetter;
use oat\tao\model\Translation\Service\TranslationUpdateService;
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
        $services->set(ResourceMetadataPopulateService::class, ResourceMetadataPopulateService::class)
            ->args(
                [
                    service(Ontology::SERVICE_ID),
                ]
            );

        $services->set(ResourceTranslationRepository::class, ResourceTranslationRepository::class)
            ->args(
                [
                    service(Ontology::SERVICE_ID),
                    service(ComplexSearchService::SERVICE_ID),
                    service(ResourceTranslationFactory::class),
                    service(LoggerService::SERVICE_ID),
                ]
            );

        $services->set(ResourceTranslatableRepository::class, ResourceTranslatableRepository::class)
            ->args(
                [
                    service(Ontology::SERVICE_ID),
                    service(ResourceTranslatableFactory::class)
                ]
            );

        $services->set(ResourceTranslationFactory::class, ResourceTranslationFactory::class)
            ->args(
                [
                    service(ResourceMetadataPopulateService::class)
                ]
            );

        $services->set(ResourceTranslatableFactory::class, ResourceTranslatableFactory::class)
            ->args(
                [
                    service(ResourceMetadataPopulateService::class)
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
            ->set(TranslationFormModifier::class, TranslationFormModifier::class)
            ->args([
                service(FeatureFlagChecker::class),
                service(FeatureFlagPropertiesMapping::class),
                service(Ontology::SERVICE_ID),
            ]);

        $services
            ->set(TranslatedIntoLanguagesSynchronizer::class, TranslatedIntoLanguagesSynchronizer::class)
            ->args([
                service(Ontology::SERVICE_ID),
                service(ResourceTranslationRepository::class),
            ]);

        $services
            ->set(TranslationCreationService::class, TranslationCreationService::class)
            ->args(
                [
                    service(Ontology::SERVICE_ID),
                    service(ResourceTranslatableRepository::class),
                    service(ResourceTranslationRepository::class),
                    service(LanguageRepositoryInterface::class),
                    service(LoggerService::SERVICE_ID),
                    service(TranslatedIntoLanguagesSynchronizer::class),
                    service(EventManager::SERVICE_ID),
                ]
            )
            ->public();

        $services
            ->set(TranslationDeletionService::class, TranslationDeletionService::class)
            ->args(
                [
                    service(Ontology::SERVICE_ID),
                    service(ResourceDeleter::class),
                    service(ResourceTranslationRepository::class),
                    service(LoggerService::SERVICE_ID),
                    service(TranslatedIntoLanguagesSynchronizer::class),
                    service(EventManager::SERVICE_ID),
                ]
            )
            ->public();

        $services
            ->set(TranslationUpdateService::class, TranslationUpdateService::class)
            ->args(
                [
                    service(Ontology::SERVICE_ID),
                    service(LoggerService::SERVICE_ID),
                    service(TranslatedIntoLanguagesSynchronizer::class),
                    service(EventManager::SERVICE_ID),
                ]
            )
            ->public();

        $services
            ->set(TranslationSyncService::class, TranslationSyncService::class)
            ->args([
                service(Ontology::SERVICE_ID),
                service(ResourceTranslationRepository::class),
                service(LoggerService::SERVICE_ID),
                service(TranslatedIntoLanguagesSynchronizer::class),
            ])
            ->public();

        $services
            ->set(ResourceLanguageRetriever::class, ResourceLanguageRetriever::class)
            ->args([
                service(UserLanguageServiceInterface::SERVICE_ID),
            ])
            ->public();

        $services
            ->set(TranslationUniqueIdSetter::class, TranslationUniqueIdSetter::class)
            ->args([
                service(FeatureFlagChecker::class),
                service(Ontology::SERVICE_ID),
            ]);

        $services
            ->set(ResourceTranslatableStatusRetriever::class, ResourceTranslatableStatusRetriever::class)
            ->args([
                service(Ontology::SERVICE_ID),
                service(LoggerService::SERVICE_ID),
            ])
            ->public();

        $services
            ->get(FeatureFlagPropertiesMapping::class)
            ->call(
                'addFeatureProperties',
                [
                    'FEATURE_FLAG_TRANSLATION_ENABLED',
                    [
                        TaoOntology::PROPERTY_TRANSLATION_ORIGINAL_RESOURCE_URI,
                        TaoOntology::PROPERTY_LANGUAGE,
                        TaoOntology::PROPERTY_TRANSLATION_STATUS,
                        TaoOntology::PROPERTY_TRANSLATION_PROGRESS,
                        TaoOntology::PROPERTY_TRANSLATION_TYPE,
                        TaoOntology::PROPERTY_TRANSLATED_INTO_LANGUAGES,
                    ],
                ]
            );
    }
}
