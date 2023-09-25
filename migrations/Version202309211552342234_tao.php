<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\model\event\ClassPropertiesChangedEvent;
use oat\tao\model\event\ClassPropertyRemovedEvent;
use oat\tao\model\event\DataAccessControlChangedEvent;
use oat\tao\model\listener\ClassPropertiesChangedListener;
use oat\tao\model\listener\ClassPropertyRemovedListener;
use oat\tao\model\listener\DataAccessControlChangedListener;
use oat\tao\model\search\index\DocumentBuilder\IndexDocumentBuilder;
use oat\tao\model\search\index\IndexService;
use oat\tao\model\search\index\IndexUpdaterInterface;
use oat\tao\model\search\strategy\GenerisIndexUpdater;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\oatbox\event\EventManager;

final class Version202309211552342234_tao extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Registers IndexService without DocumentBuilder due to refactoring it used DI';
    }

    public function up(Schema $schema): void
    {
        $this->getServiceManager()->register(IndexService::SERVICE_ID, new IndexService());
    }

    public function down(Schema $schema): void
    {
        $this->getServiceManager()->register(
            IndexService::SERVICE_ID,
            new IndexService(
                [
                    'documentBuilder' => new IndexDocumentBuilder()
                ]
            )
        );
    }
}
