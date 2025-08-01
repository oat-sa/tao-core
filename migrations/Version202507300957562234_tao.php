<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\scripts\tools\migrations\AbstractMigration;

/**
 * Migration to create unique_ids table for collision-free numeric ID generation
 *
 * phpcs:disable Squiz.Classes.ValidClassName
 */
final class Version202507300957562234_tao extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates unique_ids table to guarantee collision-free numeric ID generation';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('unique_ids');
        
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('resource_id', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('resource_type', 'string', ['length' => 255, 'notnull' => true]);
        $table->addColumn('unique_id', 'string', ['length' => 9, 'notnull' => true]);
        $table->addColumn('created_at', 'datetime', ['notnull' => true]);
        
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['unique_id', 'resource_type'], 'uniq_unique_id_resource_type');
        $table->addIndex(['resource_type'], 'idx_unique_ids_resource_type');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('unique_ids');
    }
}
