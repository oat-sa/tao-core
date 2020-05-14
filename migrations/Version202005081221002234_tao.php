<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\oatbox\event\EventManager;
use oat\tao\model\migrations\MigrationsService;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\oatbox\service\ServiceManagerAwareInterface;
use oat\oatbox\service\ServiceManagerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version202005081221002234_tao extends AbstractMigration implements ServiceManagerAwareInterface
{
    use ServiceManagerAwareTrait;

    public function getDescription(): string
    {
        return 'Register extensionInstalled event listener to initialize migrations service when tao-core extension installed';
    }

    public function up(Schema $schema): void
    {
        $this->registerService(
            MigrationsService::SERVICE_ID,
            new MigrationsService([MigrationsService::OPTION_PERSISTENCE_ID => 'default'])
        );

        /** @var EventManager $eventManager */
        $eventManager = $this->getServiceManager()->get(EventManager::SERVICE_ID);
        $eventManager->attach(\common_ext_event_ExtensionInstalled::class, [MigrationsService::SERVICE_ID, 'extensionInstalled']);
        $this->getServiceManager()->register(EventManager::SERVICE_ID, $eventManager);
    }

    public function down(Schema $schema): void
    {
        $this->getServiceManager()->unregister(MigrationsService::SERVICE_ID);
        /** @var EventManager $eventManager */
        $eventManager = $this->getServiceManager()->get(EventManager::SERVICE_ID);
        $eventManager->detach(\common_ext_event_ExtensionInstalled::class, [MigrationsService::SERVICE_ID, 'extensionInstalled']);
        $this->getServiceManager()->register(EventManager::SERVICE_ID, $eventManager);
    }
}
