<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\scripts\tools\migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 *
 * phpcs:disable Squiz.Classes.ValidClassName
 */
final class Version202610051702392234_tao extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Revert migration Version202609081655312234_tao (revoke ACL grant for searchMentionUsers)';
    }

    public function up(Schema $schema): void
    {
        (new Version202609081655312234_tao())->down($schema);
    }

    public function down(Schema $schema): void
    {
        (new Version202609081655312234_tao())->up($schema);
    }
}
