<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\Exception\IrreversibleMigration;
use oat\tao\scripts\SyncModels;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\tao\scripts\update\OntologyUpdater;

final class Version202110130633112234_tao extends AbstractMigration
{

    public function getDescription(): string
    {
        return 'Adds new Japanese locales ';
    }

    public function up(Schema $schema): void
    {
        OntologyUpdater::syncModels();
    }

    public function down(Schema $schema): void
    {
        throw new IrreversibleMigration(
            'You cannot remove locales from configuration'
        );
    }
}
