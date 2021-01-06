<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\oatbox\event\EventManager;
use oat\tao\model\event\TaoUpdateEvent;
use oat\tao\model\notifications\AlarmNotificationService;
use oat\tao\scripts\tools\migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version202011041353202234_tao extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Registration of AlarmNotificationService.';
    }

    /**
     * @param Schema $schema
     * @throws \common_Exception
     * @throws \oat\oatbox\service\exception\InvalidServiceManagerException
     */
    public function up(Schema $schema): void
    {
        $updatingNotificationService = new AlarmNotificationService();
        $updatingNotificationService->setOption(AlarmNotificationService::OPTION_NOTIFIERS, []);

        $this->getServiceManager()->register(
            $updatingNotificationService::SERVICE_ID,
            $updatingNotificationService
        );

        /** @var EventManager $eventManager */
        $eventManager = $this->getServiceManager()->get(EventManager::SERVICE_ID);
        $eventManager->attach(TaoUpdateEvent::class, [AlarmNotificationService::SERVICE_ID, 'listenTaoUpdateEvent']);
        $this->getServiceManager()->register(EventManager::SERVICE_ID, $eventManager);
    }

    public function down(Schema $schema): void
    {
        /** @var EventManager $eventManager */
        $this->getServiceManager()->unregister(AlarmNotificationService::SERVICE_ID);
        $eventManager = $this->getServiceManager()->get(EventManager::SERVICE_ID);
        $eventManager->detach(TaoUpdateEvent::class, [AlarmNotificationService::SERVICE_ID, 'listenTaoUpdateEvent']);
        $this->getServiceManager()->register(EventManager::SERVICE_ID, $eventManager);
    }
}
