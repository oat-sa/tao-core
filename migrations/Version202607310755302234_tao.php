<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\model\accessControl\func\AccessRule;
use oat\tao\model\accessControl\func\AclProxy;
use oat\tao\model\user\TaoRoles;
use oat\tao\scripts\tools\migrations\AbstractMigration;

final class Version202607310755302234_tao extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Apply roles to access tao/Log/logFrontendAction';
    }

    public function up(Schema $schema): void
    {
        AclProxy::applyRule($this->getRule());
    }

    public function down(Schema $schema): void
    {
        AclProxy::revokeRule($this->getRule());
    }

    private function getRule(): AccessRule
    {
        return new AccessRule(
            AccessRule::GRANT,
            TaoRoles::BASE_USER,
            ['ext' => 'tao', 'mod' => 'Log', 'act' => 'logFrontendAction']
        );
    }
}
