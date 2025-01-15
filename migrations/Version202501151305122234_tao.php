<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\scripts\SyncModels;
use oat\tao\scripts\tools\migrations\AbstractMigration;

/**
 * Add "VerticalWritingMode" to languages
 */
final class Version202501151305122234_tao extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update Ontology models';
    }

    public function up(Schema $schema): void
    {
        $this->addReport(
            $this->propagate(new SyncModels())([])
        );
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException(
            'The models should be updated via `SyncModels` script after reverting their RDF definitions.'
        );
    }
}
