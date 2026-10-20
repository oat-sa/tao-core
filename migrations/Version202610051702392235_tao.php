<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\Exception\IrreversibleMigration;
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
final class Version202610051702392235_tao extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Revert migration Version202609081655312234_tao (revoke ACL grant for searchMentionUsers)';
    }

    public function up(Schema $schema): void
    {
        AclProxy::revokeRule($this->getRule());
        $this->addReport(Report::createSuccess('Revoked ACL grant for RestResourceComments::searchMentionUsers'));
    }

    public function down(Schema $schema): void
    {
        throw new IrreversibleMigration();
    }

    private function getRule(): AccessRule
    {
        return new AccessRule(
            AccessRule::GRANT,
            TaoRoles::BACK_OFFICE,
            ['ext' => 'taoItems', 'mod' => 'RestResourceComments', 'act' => 'searchMentionUsers']
        );
    }
}
