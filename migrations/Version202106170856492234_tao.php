<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\scripts\update\OntologyUpdater;
use oat\tao\scripts\tools\migrations\AbstractMigration;

final class Version202106170856492234_tao extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        OntologyUpdater::syncModels();
    }

    public function down(Schema $schema): void
    {
        $this->logInfo('Nothing to execute.');
    }
}
