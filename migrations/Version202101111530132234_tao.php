<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\model\Language\Service\LanguageConfigRepository;
use oat\tao\scripts\tools\migrations\AbstractMigration;

final class Version202101111530132234_tao extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Set Language configuration repository';
    }

    public function up(Schema $schema): void
    {
        $this->getServiceLocator()
            ->register(LanguageConfigRepository::SERVICE_ID, new LanguageConfigRepository());
    }

    public function down(Schema $schema): void
    {
        $this->getServiceLocator()->unregister(LanguageConfigRepository::SERVICE_ID);
    }
}
