<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\oatbox\reporting\Report;
use oat\tao\model\accessControl\func\AccessRule;
use oat\tao\model\accessControl\func\AclProxy;
use oat\tao\model\user\TaoRoles;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\tao\scripts\update\OntologyUpdater;

final class Version202409040743452141_tao extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add new access to ' . TaoRoles::GLOBAL_MANAGER;
    }

    public function up(Schema $schema): void
    {
        OntologyUpdater::syncModels();
        $this->addReport(Report::createSuccess('Ontology models successfully synchronized'));

        AclProxy::applyRule($this->getRule());

        $this->addReport(Report::createSuccess('Allow translation access for ' . TaoRoles::GLOBAL_MANAGER));
    }

    public function down(Schema $schema): void
    {
        AclProxy::revokeRule($this->getRule());

        $this->addReport(Report::createSuccess('Revoke translation access for ' . TaoRoles::GLOBAL_MANAGER));
    }

    private function getRule(): AccessRule
    {
        return new AccessRule(
            AccessRule::GRANT,
            TaoRoles::GLOBAL_MANAGER,
            [
                'ext' => 'tao',
                'mod' => 'Translation'
            ]
        );
    }
}
