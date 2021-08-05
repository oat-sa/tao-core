<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\tao\scripts\install\SetImageAligmentConfig;
use oat\tao\model\ClientLibConfigRegistry;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version202108051320552234_tao extends AbstractMigration
{

    public function getDescription(): string
    {
        return 'Set Image Aligment disabled in Authoring';
    }

    public function up(Schema $schema): void
    {
        $this->propagate(new SetImageAligmentConfig())([false]);
    }

    public function down(Schema $schema): void
    {
        ClientLibConfigRegistry::getRegistry()->remove('ui/image/ImgStateActive');
    }
}
