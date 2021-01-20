<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\model\Lists\Business\Service\ClassMetadataService;
use oat\tao\scripts\install\RegisterClassMetadataServices;
use oat\tao\scripts\tools\migrations\AbstractMigration;

final class Version202010042003512234_tao extends AbstractMigration
{

    public function getDescription(): string
    {
        return 'Register services for metadata class discovery endpoint';
    }

    public function up(Schema $schema): void
    {
        (new RegisterClassMetadataServices())
            ->setServiceLocator($this->getServiceManager())
        ();
    }

    public function down(Schema $schema): void
    {
        $this->getServiceManager()->unregister(ClassMetadataService::SERVICE_ID);
    }
}
