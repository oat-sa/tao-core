<?php

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\scripts\tools\migrations\AbstractMigration;

/**
 * phpcs:disable Squiz.Classes.ValidClassName
 */
final class Migration_tao extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create user table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('languages');

        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('code', 'string', ['length' => 255, 'notnull' => true]);
        $table->addColumn('name', 'string', ['length' => 255, 'notnull' => true]);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('languages');
    }
}
