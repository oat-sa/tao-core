<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\Exception\IrreversibleMigration;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\tao\scripts\update\OntologyUpdater;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version202007161231482234_tao extends AbstractMigration
{

    public function getDescription(): string
    {
        return 'Update the Ontology model';
    }

    public function up(Schema $schema): void
    {
        OntologyUpdater::syncModels();
    }

    public function down(Schema $schema): void
    {
        throw new IrreversibleMigration('Ontology should be re-synchronized after editing the source files.');
    }
}
