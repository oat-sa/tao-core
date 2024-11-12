<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\oatbox\event\EventManager;
use oat\tao\model\Translation\Event\TranslationActionEvent;
use oat\tao\model\Translation\Listener\TranslationActionEventListener;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\tao\scripts\update\OntologyUpdater;

/**
 * phpcs:disable Squiz.Classes.ValidClassName
 */
final class Version202411111300522236_tao extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Register new event to detect when translations are touched (created, deleted, updated, synchronized)';
    }

    public function up(Schema $schema): void
    {
        OntologyUpdater::syncModels();

        $eventManager = $this->getEventManager();
        $eventManager->attach(
            TranslationActionEvent::class,
            [TranslationActionEventListener::class, 'populateTranslatedIntoLanguagesProperty'],
        );
        $this->getServiceManager()->register(EventManager::SERVICE_ID, $eventManager);
    }

    public function down(Schema $schema): void
    {
        OntologyUpdater::syncModels();

        $eventManager = $this->getEventManager();
        $eventManager->detach(
            TranslationActionEvent::class,
            [TranslationActionEventListener::class, 'populateTranslatedIntoLanguagesProperty']
        );
        $this->getServiceManager()->register(EventManager::SERVICE_ID, $eventManager);
    }

    private function getEventManager(): EventManager
    {
        return $this->getServiceManager()->get(EventManager::class);
    }
}
