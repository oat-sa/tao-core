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

namespace oat\tao\scripts\tools\Translation;

use common_persistence_SqlPersistence;
use core_kernel_classes_Class;
use InvalidArgumentException;
use oat\generis\model\data\Ontology;
use oat\generis\model\OntologyRdf;
use oat\generis\model\OntologyRdfs;
use oat\generis\persistence\PersistenceManager;
use oat\oatbox\extension\script\ScriptAction;
use oat\oatbox\reporting\Report;
use oat\oatbox\user\UserLanguageServiceInterface;
use oat\tao\model\featureFlag\FeatureFlagChecker;
use oat\tao\model\featureFlag\FeatureFlagCheckerInterface;
use oat\tao\model\TaoOntology;
use Psr\Container\ContainerInterface;
use Throwable;

/**
 * @example php index.php 'oat\tao\scripts\tools\Translation\PopulateTranslationProperties'
 *          -c http://www.tao.lu/Ontologies/TAOItem.rdf#Item
 *          [[ --chunk-size=1000 ]]
 */
class PopulateTranslationProperties extends ScriptAction
{
    private const OPTION_CLASS = 'class';
    private const OPTION_CHUNK_SIZE = 'chunk-size';

    private const SUPPORTED_TYPES = [
        TaoOntology::CLASS_URI_ITEM,
        TaoOntology::CLASS_URI_TEST,
    ];

    private common_persistence_SqlPersistence $persistence;
    private Report $report;

    protected function provideOptions(): array
    {
        return [
            self::OPTION_CLASS => [
                'prefix' => 'c',
                'longPrefix' => self::OPTION_CLASS,
                'defaultValue' => TaoOntology::CLASS_URI_ITEM,
                'description' => 'A class for recursively filling resource translation properties.',
            ],
            self::OPTION_CHUNK_SIZE => [
                'prefix' => 'cs',
                'longPrefix' => self::OPTION_CHUNK_SIZE,
                'defaultValue' => 1000,
                'cast' => 'integer',
                'description' => 'Chunk size to populate resource translation properties.',
            ],
        ];
    }

    protected function provideDescription(): string
    {
        return 'Script to populate translation properties for resources in given class.';
    }

    protected function run(): Report
    {
        try {
            if (!$this->getFeatureFlagChecker()->isEnabled('FEATURE_FLAG_TRANSLATION_ENABLED')) {
                return Report::createError('Translation properties cannot be populated because this feature is disabled');
            }

            $class = $this->getOntology()->getClass($this->getOption(self::OPTION_CLASS));

            $this->assertClassExists($class);
            $this->assertClassTypeSupported($class);

            $resourceIds = $this->getResourceIdsToPopulateProperties($class);

            if (empty($resourceIds)) {
                return Report::createWarning(
                    'No resources found to populate translation properties in class ' . $class->getUri()
                );
            }

            $this->report(Report::createInfo('Populating translation properties for class ' . $class->getUri()));

            $this->populateProperties($resourceIds);

            return $this->report(
                Report::createSuccess('Successfully populated translation properties for type ' . $class->getUri())
            );
        } catch (Throwable $exception) {
            return $this->report(Report::createError(
                'An error occurred while populating translation properties: ' . $exception->getMessage()
            ));
        }
    }

    private function assertClassExists(core_kernel_classes_Class $class): void
    {
        if (!$class->exists()) {
            throw new InvalidArgumentException(sprintf('Class %s not found', $class->getUri()));
        }
    }

    private function assertClassTypeSupported(core_kernel_classes_Class $class): void
    {
        $parentClassesIds = $class->getParentClassesIds();
        $type = array_pop($parentClassesIds) ?? $class->getUri();

        if (!in_array($type, self::SUPPORTED_TYPES, true)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Class type %s is not supported. Allowed types: %s',
                    $type,
                    implode(', ', self::SUPPORTED_TYPES)
                )
            );
        }
    }

    /**
     * @return string[]
     */
    private function getResourceIdsToPopulateProperties(core_kernel_classes_Class $class): array
    {
        $query = <<<'SQL'
            with recursive statements_tree AS (
                select
                    r.subject,
                    r.predicate
                from statements r
                where r.subject = ?
                union all
                select
                    s.subject,
                    s.predicate
                from statements s
                    join statements_tree st
                        on s.object = st.subject
                               and s.predicate in (?, ?)
            )
            select st.subject
            from statements_tree st
            where st.predicate = ?
              and st.subject not in (select ch.subject from statements ch where ch.subject = st.subject and ch.predicate = ?)
            group by st.subject;
            SQL;

        $statement = $this->getPersistence()->query(
            $query,
            [
                $class->getUri(),
                OntologyRdfs::RDFS_SUBCLASSOF,
                OntologyRdf::RDF_TYPE,
                OntologyRdf::RDF_TYPE,
                TaoOntology::PROPERTY_TRANSLATION_TYPE,
            ]
        );

        return array_column($statement->fetchAll(), 'subject');
    }

    private function populateProperties(array $resourceIds): void
    {
        $numberOfPropertiesPerResource = count($resourceIds[0]);
        $numberOfResources = count($resourceIds);
        $chunks = array_chunk(
            $this->getValuesToInsert($resourceIds),
            $this->getOption(self::OPTION_CHUNK_SIZE)
        );
        $inserted = 0;
        $platform = $this->getPersistence()->getPlatForm();

        foreach ($chunks as $chunkValues) {
            $platform->beginTransaction();

            $inserted += $this->getPersistence()->insertMultiple('statements', $chunkValues);

            $platform->commit();

            $this->report(
                Report::createInfo(
                    sprintf(
                        'Progress: %d / %d.',
                        $inserted / $numberOfPropertiesPerResource,
                        $numberOfResources
                    )
                )
            );
        }
    }

    private function getValuesToInsert(array $resourceIds): array
    {
        $defaultLanguage = TaoOntology::LANGUAGE_PREFIX . $this->getUserLanguageService()->getDefaultLanguage();

        $values = [];

        foreach ($resourceIds as $resourceId) {
            $values[] = $this->buildInsertValue(
                $resourceId,
                TaoOntology::PROPERTY_TRANSLATION_TYPE,
                TaoOntology::PROPERTY_VALUE_TRANSLATION_TYPE_ORIGINAL
            );
            $values[] = $this->buildInsertValue(
                $resourceId,
                TaoOntology::PROPERTY_TRANSLATION_STATUS,
                TaoOntology::PROPERTY_VALUE_TRANSLATION_STATUS_NOT_READY
            );
            $values[] = $this->buildInsertValue(
                $resourceId,
                TaoOntology::PROPERTY_LANGUAGE,
                $defaultLanguage
            );
        }

        return $values;
    }

    private function buildInsertValue(string $subject, string $predicate, string $object): array
    {
        return [
            'modelid' => 1,
            'subject' => $subject,
            'predicate' => $predicate,
            'object' => $object,
            'l_language' => '',
        ];
    }

    private function report(Report $report): Report
    {
        !isset($this->report)
            ? $this->report = $report
            : $this->report->add($report);

        return $this->report;
    }

    private function getFeatureFlagChecker(): FeatureFlagCheckerInterface
    {
        return $this->getPsrContainer()->get(FeatureFlagChecker::class);
    }

    private function getOntology(): Ontology
    {
        return $this->getPsrContainer()->get(Ontology::SERVICE_ID);
    }

    private function getUserLanguageService(): UserLanguageServiceInterface
    {
        return $this->getPsrContainer()->get(UserLanguageServiceInterface::SERVICE_ID);
    }

    private function getPersistence(): common_persistence_SqlPersistence
    {
        if (!isset($this->persistence)) {
            /** @var PersistenceManager $persistenceManager */
            $persistenceManager = $this->getPsrContainer()->get(PersistenceManager::SERVICE_ID);

            $this->persistence = $persistenceManager->getPersistenceById('default');
        }

        return $this->persistence;
    }

    private function getPsrContainer(): ContainerInterface
    {
        return $this->getServiceManager()->getContainer();
    }
}
