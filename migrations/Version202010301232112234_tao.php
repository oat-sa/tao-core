<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\model\accessControl\func\AccessRule;
use oat\tao\model\accessControl\func\AclProxy;
use oat\tao\model\user\TaoRoles;
use oat\tao\scripts\tools\migrations\AbstractMigration;

final class Version202010301232112234_tao extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Give access to resource relations endpoint';
    }

    public function up(Schema $schema): void
    {
        AclProxy::applyRule($this->createRule());
    }

    public function down(Schema $schema): void
    {
        AclProxy::revokeRule($this->createRule());
    }

    public function createRule(): AccessRule
    {
        return new AccessRule(
            AccessRule::GRANT,
            TaoRoles::BASE_USER,
            [
                'ext' => 'tao',
                'mod' => 'ResourceRelations'
            ]
        );
    }
}
