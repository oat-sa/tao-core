<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\scripts\install\EnableFuriganaRubyPlugin;
use oat\tao\scripts\tools\migrations\AbstractMigration;

/**
 * Enable Ruby CKEditor plugin
 */
final class Version202303200919202234_tao extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Furigana ruby plugin enabled.';
    }

    public function up(Schema $schema): void
    {
        $this->addReport(
            $this->propagate(new EnableFuriganaRubyPlugin())([])
        );
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
