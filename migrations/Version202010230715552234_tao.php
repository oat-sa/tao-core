<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\scripts\install\InstallOntologyUriResolver;
use oat\tao\scripts\tools\migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version202010230715552234_tao extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'InstallOntologyUriResolver';
    }

    public function up(Schema $schema): void
    {
        $this->runAction(new InstallOntologyUriResolver());
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
