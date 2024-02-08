<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\generis\model\data\event\ResourceDeleted;
use oat\tao\model\listener\ClassPropertyRemovedListener;
use oat\oatbox\event\EventManager;
use oat\tao\scripts\tools\migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 *
 * phpcs:disable Squiz.Classes.ValidClassName
 */
final class Version202402086838312237_tao extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rolled back the listener for ResourceDeleted';
    }

    public function up(Schema $schema): void
    {
        /** @var EventManager $eventManager */
        $eventManager = $this->getServiceManager()->get(EventManager::SERVICE_ID);
        $eventManager->detach(
            ResourceDeleted::class,
            [ClassPropertyRemovedListener::SERVICE_ID, 'handleDeletedEvent']
        );
        $this->getServiceManager()->register(EventManager::SERVICE_ID, $eventManager);
    }

    public function down(Schema $schema): void
    {
        /** @var EventManager $eventManager */
        $eventManager = $this->getServiceManager()->get(EventManager::SERVICE_ID);
        $eventManager->attach(
            ResourceDeleted::class,
            [ClassPropertyRemovedListener::SERVICE_ID, 'handleDeletedEvent']
        );
        $this->getServiceManager()->register(EventManager::SERVICE_ID, $eventManager);
    }
}
