<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\oatbox\reporting\Report;
use oat\tao\model\accessControl\func\AccessRule;
use oat\tao\model\accessControl\func\AclProxy;
use oat\tao\model\user\TaoRoles;
use oat\tao\scripts\tools\migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 *
 * phpcs:disable Squiz.Classes.ValidClassName
 */
final class Version202502270920282234_tao extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add new access to Backoffice User';
    }

    public function up(Schema $schema): void
    {
        AclProxy::applyRule($this->getRule());
        $this->addReport(Report::createSuccess('Allow translation access for ' . TaoRoles::BACK_OFFICE));

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
            TaoRoles::BACK_OFFICE,
            [
                'ext' => 'tao',
                'mod' => 'ResourceMetadata'
            ]
        );
    }

}
