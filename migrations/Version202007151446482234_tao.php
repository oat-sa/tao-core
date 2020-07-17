<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\model\Lists\DataAccess\Repository\RdsValueCollectionRepository;
use oat\tao\scripts\install\CreateRdsListStore;
use oat\tao\scripts\tools\migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version202007151446482234_tao extends AbstractMigration
{

    public function getDescription(): string
    {
        return 'Create an RDS store for lists';
    }

    public function up(Schema $schema): void
    {
        (new CreateRdsListStore())->create($schema);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable(RdsValueCollectionRepository::TABLE_LIST_ITEMS);
    }
}
