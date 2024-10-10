<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\oatbox\event\EventManager;
use oat\tao\model\Translation\Event\ResourceTranslationChangedEvent;
use oat\tao\model\Translation\Listener\ResourceTranslationChangedEventListener;
use oat\tao\scripts\tools\migrations\AbstractMigration;

/**
 * phpcs:disable Squiz.Classes.ValidClassName
 */
final class Version202410100711422234_tao extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Register new event to detect translation changes';
    }

    public function up(Schema $schema): void
    {
        $eventManager = $this->getEventManager();
        $eventManager->attach(
            ResourceTranslationChangedEvent::class,
            [ResourceTranslationChangedEventListener::class, 'onResourceTranslationChanged']
        );
        $this->getServiceManager()->register(EventManager::SERVICE_ID, $eventManager);
    }

    public function down(Schema $schema): void
    {
        $eventManager = $this->getEventManager();
        $eventManager->detach(
            ResourceTranslationChangedEvent::class,
            [ResourceTranslationChangedEventListener::class, 'onResourceTranslationChanged']
        );
        $this->getServiceManager()->register(EventManager::SERVICE_ID, $eventManager);
    }

    private function getEventManager(): EventManager
    {
        return $this->getServiceManager()->get(EventManager::class);
    }
}
