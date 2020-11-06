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


final class Version202007231447592234_tao extends AbstractMigration
{

    public function getDescription(): string
    {
        return 'Register advanced search related Events/Listeners';
    }

    public function up(Schema $schema): void
    {
        /** @var EventManager $eventManager */
        $eventManager = $this->getServiceManager()->get(EventManager::SERVICE_ID);
        $eventManager->attach(ClassPropertiesChangedEvent::class, [ClassPropertiesChangedListener::SERVICE_ID, 'handleEvent']);
        $eventManager->attach(ClassPropertyRemovedEvent::class, [ClassPropertyRemovedListener::SERVICE_ID, 'handleEvent']);
        $eventManager->attach(DataAccessControlChangedEvent::class, [DataAccessControlChangedListener::SERVICE_ID, 'handleEvent']);

        $this->getServiceManager()->register(EventManager::SERVICE_ID, $eventManager);
        $this->getServiceManager()->register(ClassPropertiesChangedListener::SERVICE_ID, new ClassPropertiesChangedListener());
        $this->getServiceManager()->register(ClassPropertyRemovedListener::SERVICE_ID, new ClassPropertyRemovedListener());
        $this->getServiceManager()->register(DataAccessControlChangedListener::SERVICE_ID, new DataAccessControlChangedListener());

        $this->getServiceManager()->register(IndexUpdaterInterface::SERVICE_ID, new GenerisIndexUpdater());
        $this->getServiceManager()->register(
            IndexService::SERVICE_ID,
            new IndexService(
                [
                    'documentBuilder' => new IndexDocumentBuilder()
                ]
            )
        );
    }

    public function down(Schema $schema): void
    {
        $this->getServiceManager()->unregister(ClassPropertiesChangedListener::SERVICE_ID);
        $this->getServiceManager()->unregister(ClassPropertiesChangedListener::SERVICE_ID);
        $this->getServiceManager()->unregister(DataAccessControlChangedListener::SERVICE_ID);

        /** @var EventManager $eventManager */
        $eventManager = $this->getServiceManager()->get(EventManager::SERVICE_ID);
        $eventManager->detach(ClassPropertiesChangedEvent::class, [ClassPropertiesChangedListener::SERVICE_ID, 'handleEvent']);
        $eventManager->detach(ClassPropertyRemovedEvent::class, [ClassPropertyRemovedListener::SERVICE_ID, 'handleEvent']);
        $eventManager->detach(DataAccessControlChangedEvent::class, [DataAccessControlChangedListener::SERVICE_ID, 'handleEvent']);

        $this->getServiceManager()->register(EventManager::SERVICE_ID, $eventManager);

        $this->getServiceManager()->unregister(IndexUpdaterInterface::SERVICE_ID);
        $this->getServiceManager()->unregister(IndexService::SERVICE_ID);
    }
}
