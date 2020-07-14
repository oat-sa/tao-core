<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\model\event\ClassPropertiesChangedEvent;
use oat\tao\model\event\ClassPropertyRemovedEvent;
use oat\tao\model\listener\ClassPropertiesChangedListener;
use oat\tao\model\listener\ClassPropertyRemovedListener;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\oatbox\event\EventManager;


final class Version202007140932102234_tao extends AbstractMigration
{

    public function getDescription(): string
    {
        return 'Register advanced search related Events/Listeners';
    }

    public function up(Schema $schema): void
    {
        /** @var EventManager $eventManager */
        $eventManager = $this->getServiceManager()->get(EventManager::SERVICE_ID);
        $eventManager->attach(ClassPropertiesChangedEvent::class, [ClassPropertiesChangedListener::SERVICE_ID, 'renameClassProperties']);
        $eventManager->attach(ClassPropertyRemovedEvent::class, [ClassPropertyRemovedListener::SERVICE_ID, 'removeClassProperty']);

        $this->getServiceManager()->register(EventManager::SERVICE_ID, $eventManager);
        $this->getServiceManager()->register(ClassPropertiesChangedListener::SERVICE_ID, new ClassPropertiesChangedListener());
        $this->getServiceManager()->register(ClassPropertyRemovedListener::SERVICE_ID, new ClassPropertyRemovedListener());
    }

    public function down(Schema $schema): void
    {
        $this->getServiceManager()->unregister(ClassPropertiesChangedListener::SERVICE_ID);
        $this->getServiceManager()->unregister(ClassPropertiesChangedListener::SERVICE_ID);

        /** @var EventManager $eventManager */
        $eventManager = $this->getServiceManager()->get(EventManager::SERVICE_ID);
        $eventManager->detach(ClassPropertiesChangedEvent::class, [ClassPropertiesChangedListener::SERVICE_ID, 'renameClassProperties']);
        $eventManager->detach(ClassPropertyRemovedEvent::class, [ClassPropertyRemovedListener::SERVICE_ID, 'removeClassProperty']);

        $this->getServiceManager()->register(EventManager::SERVICE_ID, $eventManager);
    }
}
