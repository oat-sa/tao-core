<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\oatbox\reporting\Report;
use oat\tao\model\user\TaoRoles;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\tao\scripts\update\OntologyUpdater;

final class Version202409040743452141_tao extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add new access to ' . TaoRoles::BACK_OFFICE;
    }

    public function up(Schema $schema): void
    {
        OntologyUpdater::syncModels();
        $this->addReport(Report::createSuccess('Ontology models successfully synchronized'));
    }

    public function down(Schema $schema): void
    {
    }
}
