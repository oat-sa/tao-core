<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\model\Lists\Business\Service\ClassMetadataSearcherProxy;
use oat\tao\model\Lists\Business\Service\ClassMetadataService;
use oat\tao\scripts\tools\migrations\AbstractMigration;

final class Version202101050805132234_tao extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Set Class Metadata Search Proxy';
    }

    public function up(Schema $schema): void
    {
        $this->getServiceManager()->register(
            ClassMetadataSearcherProxy::SERVICE_ID,
            new ClassMetadataSearcherProxy(
                [
                    ClassMetadataSearcherProxy::OPTION_ACTIVE_SEARCHER => ClassMetadataService::SERVICE_ID,
                ]
            )
        );
    }

    public function down(Schema $schema): void
    {
        $this->getServiceManager()->unregister(ClassMetadataSearcherProxy::SERVICE_ID);
    }
}
