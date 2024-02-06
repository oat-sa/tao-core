<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\scripts\SyncModels;
use oat\tao\scripts\tools\migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 *
 * phpcs:disable Squiz.Classes.ValidClassName
 */
final class Version202402061413282234_tao extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update Ontology models';
    }

    public function up(Schema $schema): void
    {
        $this->runAction(new SyncModels());
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException(
            'A manual ontology definition update and synchronization of the RDF models is required in order to revert this migration.'
        );
    }
}
