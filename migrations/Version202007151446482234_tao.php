<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\model\Lists\DataAccess\Repository\RdsValueCollectionRepository;
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
        $listItemsTable = $schema->createTable(RdsValueCollectionRepository::TABLE_LIST_ITEMS);

        $listItemsTable->addColumn(RdsValueCollectionRepository::FIELD_ITEM_ID, 'integer', ['autoincrement' => true]);
        $listItemsTable->addColumn(RdsValueCollectionRepository::FIELD_ITEM_LABEL, 'string', ['length' => 255]);
        $listItemsTable->addColumn(RdsValueCollectionRepository::FIELD_ITEM_URI, 'string', ['length' => 255]);
        $listItemsTable->addColumn(RdsValueCollectionRepository::FIELD_ITEM_LIST_URI, 'string', ['length' => 255]);

        $listItemsTable->setPrimaryKey([RdsValueCollectionRepository::FIELD_ITEM_ID]);
        $listItemsTable->addUniqueIndex(
            [RdsValueCollectionRepository::FIELD_ITEM_LIST_URI, RdsValueCollectionRepository::FIELD_ITEM_URI]
        );
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable(RdsValueCollectionRepository::TABLE_LIST_ITEMS);
    }
}
