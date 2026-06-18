<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\oatbox\reporting\Report;
use oat\tao\model\accessControl\func\AccessRule;
use oat\tao\model\accessControl\func\AclProxy;
use oat\tao\model\user\TaoRoles;
use oat\tao\scripts\update\OntologyUpdater;
use oat\tao\scripts\tools\migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 *
 * phpcs:disable Squiz.Classes.ValidClassName
 */
final class Version202606181022442234_tao extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Grant MetadataImport access to Metadata Import Administrator role';
    }

    public function up(Schema $schema): void
    {
        OntologyUpdater::syncModels();
        $this->addReport(Report::createSuccess('Ontology models successfully synchronized'));

        AclProxy::applyRule($this->getRule());
        $this->addReport(
            Report::createSuccess(
                'Allow MetadataImport access for ' . TaoRoles::METADATA_IMPORT_ADMINISTRATOR
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
