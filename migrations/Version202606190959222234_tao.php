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

/**
 * Auto-generated Migration: Please modify to your needs!
 *
 * phpcs:disable Squiz.Classes.ValidClassName
 */
final class Version202606190959222234_tao extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Apply MetadataImport ACL grant for MetadataImportAdministrator';
    }

    public function up(Schema $schema): void
    {
        OntologyUpdater::syncModels();

        // Force deterministic ACL state on environments where previous rollout missed this grant.
        AclProxy::revokeRule($this->getRule());
        AclProxy::applyRule($this->getRule());

        $this->addReport(
            Report::createSuccess(
                'Applied MetadataImport access for ' . TaoRoles::METADATA_IMPORT_ADMINISTRATOR
            )
        );
    }

    public function down(Schema $schema): void
    {
        AclProxy::revokeRule($this->getRule());

        $this->addReport(
            Report::createSuccess(
                'Revoke MetadataImport access for ' . TaoRoles::METADATA_IMPORT_ADMINISTRATOR
            )
        );
    }

    private function getRule(): AccessRule
    {
        return new AccessRule(
            AccessRule::GRANT,
            TaoRoles::METADATA_IMPORT_ADMINISTRATOR,
            [
                'ext' => 'tao',
                'mod' => 'MetadataImport'
            ]
        );
    }
}
