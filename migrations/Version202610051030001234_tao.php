<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\model\accessControl\func\AccessRule;
use oat\tao\model\accessControl\func\AclProxy;
use oat\tao\model\user\TaoRoles;
use oat\tao\scripts\tools\migrations\AbstractMigration;

/**
 * phpcs:disable Squiz.Classes.ValidClassName
 */
final class Version202610051030001234_tao extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Move comment mention user search ACL from RestUser@searchUsers to RestResourceComments@searchMentionUsers';
    }

    public function up(Schema $schema): void
    {
        AclProxy::revokeRule(
            new AccessRule(
                AccessRule::GRANT,
                TaoRoles::BACK_OFFICE,
                ['ext' => 'tao', 'mod' => 'RestUser', 'act' => 'searchUsers']
            )
        );

        AclProxy::applyRule(
            new AccessRule(
                AccessRule::GRANT,
                TaoRoles::BACK_OFFICE,
                ['ext' => 'taoItems', 'mod' => 'RestResourceComments', 'act' => 'searchMentionUsers']
            )
        );
    }

    public function down(Schema $schema): void
    {
        AclProxy::revokeRule(
            new AccessRule(
                AccessRule::GRANT,
                TaoRoles::BACK_OFFICE,
                ['ext' => 'taoItems', 'mod' => 'RestResourceComments', 'act' => 'searchMentionUsers']
            )
        );

        AclProxy::applyRule(
            new AccessRule(
                AccessRule::GRANT,
                TaoRoles::BACK_OFFICE,
                ['ext' => 'tao', 'mod' => 'RestUser', 'act' => 'searchUsers']
            )
        );
    }
}
