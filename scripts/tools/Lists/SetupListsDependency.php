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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\scripts\tools\Lists;

use Doctrine\DBAL\Schema\Schema;
use oat\oatbox\reporting\Report;
use oat\generis\model\data\Ontology;
use oat\oatbox\extension\script\ScriptAction;
use oat\generis\model\data\import\RdfImporter;
use oat\generis\persistence\PersistenceManager;
use oat\tao\model\featureFlag\FeatureFlagChecker;
use common_ext_ExtensionsManager as ExtensionsManager;
use common_ext_ExtensionException as ExtensionException;
use common_persistence_SqlPersistence as SqlPersistence;
use oat\tao\model\featureFlag\FeatureFlagCheckerInterface;
use oat\generis\model\kernel\persistence\file\FileIterator;
use oat\tao\model\Lists\DataAccess\Repository\RdsValueCollectionRepository;

class SetupListsDependency extends ScriptAction
{
    public const OPTION_ACTION = 'action';

    public const ACTION_SETUP = 'setup';
    public const ACTION_REMOVE = 'remove';

    /** @var SqlPersistence */
    private $persistence;

    /** @var string */
    private $filePath;

    /**
     * @return array[]
     */
    protected function provideOptions()
    {
        return [
            self::OPTION_ACTION => [
                'prefix' => 'a',
                'longPrefix' => self::OPTION_ACTION,
                'cast' => 'string',
                'defaultValue' => self::ACTION_SETUP,
                'description' => sprintf(
                    'Action to configure lists dependency feature. Available actions: %s, %s.',
                    self::ACTION_SETUP . ' (default)',
                    self::ACTION_REMOVE
                ),
            ],
        ];
    }

    /**
     * @return string
     */
    protected function provideDescription()
    {
        return 'Allow to set up or remove configuration for lists dependency feature.';
    }

    /**
     * @return Report
     */
    protected function run()
    {
        if (!$this->isListsDependencyEnabled()) {
            return Report::createWarning('Lists dependency feature is disabled.');
        }

        $action = $this->getOption(self::OPTION_ACTION);

        if (!in_array($action, [self::ACTION_SETUP, self::ACTION_REMOVE], true)) {
            return Report::createWarning(
                sprintf(
                    'Action "%s" is not supported. Supported actions: %s, %s.',
                    $action,
                    self::ACTION_SETUP . ' (default)',
                    self::ACTION_REMOVE
                )
            );
        }

        /**
         * $this->setup()
         * $this->remove()
         */
        return $this->{$action}();
    }

    private function isListsDependencyEnabled(): bool
    {
        /** @var FeatureFlagCheckerInterface $featureFlagChecker */
        $featureFlagChecker = $this->getServiceLocator()->get(FeatureFlagChecker::class);

        return $featureFlagChecker->isEnabled(
            FeatureFlagCheckerInterface::FEATURE_FLAG_LISTS_DEPENDENCY_ENABLED
        );
    }

    private function setup(): Report
    {
        $this->removeTriples();
        $this->getRdfImporter()->importFile($this->getFilePath());

        [$fromSchema, $schema] = $this->getSchemas();
        $this->addColumn($schema);
        $this->migrate($fromSchema, $schema);

        return Report::createSuccess(
            sprintf(
                'Column "%s" successfully added to table "%s"',
                RdsValueCollectionRepository::FIELD_DEPENDENCY_ITEM_URI,
                RdsValueCollectionRepository::TABLE_LIST_ITEMS
            )
        );
    }

    private function remove(): Report
    {
        $this->removeTriples();

        [$fromSchema, $schema] = $this->getSchemas();
        $this->removeColumn($schema);
        $this->migrate($fromSchema, $schema);

        return Report::createSuccess(
            sprintf(
                'Column "%s" successfully removed from table "%s"',
                RdsValueCollectionRepository::FIELD_DEPENDENCY_ITEM_URI,
                RdsValueCollectionRepository::TABLE_LIST_ITEMS
            )
        );
    }

    private function getSchemas(): array
    {
        /** @var Schema $schema */
        $schema = $this->getPersistence()->getDriver()->getSchemaManager()->createSchema();
        $fromSchema = clone $schema;

        return [$fromSchema, $schema];
    }

    private function addColumn(Schema $schema): void
    {
        $listItemsTable = $schema->getTable(RdsValueCollectionRepository::TABLE_LIST_ITEMS);

        if (!$listItemsTable->hasColumn(RdsValueCollectionRepository::FIELD_DEPENDENCY_ITEM_URI)) {
            $listItemsTable->addColumn(
                RdsValueCollectionRepository::FIELD_DEPENDENCY_ITEM_URI,
                'string',
                ['length' => 255, 'notnull' => false]
            );

            $listItemsTable->addIndex([RdsValueCollectionRepository::FIELD_DEPENDENCY_ITEM_URI]);
        }
    }

    private function removeColumn(Schema $schema): void
    {
        $listItemsTable = $schema->getTable(RdsValueCollectionRepository::TABLE_LIST_ITEMS);

        if ($listItemsTable->hasColumn(RdsValueCollectionRepository::FIELD_DEPENDENCY_ITEM_URI)) {
            $listItemsTable->dropColumn(RdsValueCollectionRepository::FIELD_DEPENDENCY_ITEM_URI);
        }
    }

    private function migrate(Schema $fromSchema, Schema $schema): void
    {
        $queries = $this->getPersistence()->getPlatForm()->getMigrateSchemaSql($fromSchema, $schema);

        foreach ($queries as $query) {
            $this->getPersistence()->exec($query);
        }
    }

    /**
     * @throws ExtensionException
     */
    private function removeTriples(): void
    {
        $rdf = $this->getOntology()->getRdfInterface();
        $triples = new FileIterator($this->getFilePath());

        foreach ($triples as $triple) {
            $rdf->remove($triple);
        }
    }

    /**
     * @throws ExtensionException
     */
    private function getFilePath(): string
    {
        if (!$this->filePath) {
            $dir = $this->getExtensionManager()->getExtensionById('tao')->getDir();

            $this->filePath = $dir . 'models/ontology/lists/listsdependency.rdf';
        }

        return $this->filePath;
    }

    private function getRdfImporter(): RdfImporter
    {
        return $this->getServiceLocator()->get(RdfImporter::class);
    }

    private function getOntology(): Ontology
    {
        return $this->getServiceLocator()->get(Ontology::SERVICE_ID);
    }

    private function getExtensionManager(): ExtensionsManager
    {
        return $this->getServiceLocator()->get(ExtensionsManager::SERVICE_ID);
    }

    private function getPersistence(): SqlPersistence
    {
        if (!isset($this->persistence)) {
            $persistenceManager = $this->getServiceLocator()->get(PersistenceManager::SERVICE_ID);

            $this->persistence = $persistenceManager->getPersistenceById('default');
        }

        return $this->persistence;
    }
}
