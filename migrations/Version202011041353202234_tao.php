<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\model\extension\UpdatingNotificationService;
use oat\tao\scripts\tools\migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version202011041353202234_tao extends AbstractMigration
{

    public function getDescription(): string
    {
        return 'Registration UpdateErrorNotifier. Service for error reporting during taoUpdate';
    }

    public function up(Schema $schema): void
    {
        $updatingNotificationService = new UpdatingNotificationService();
        $updatingNotificationService->setOption('notifiers', []);

        $this->getServiceManager()->register(
            $updatingNotificationService::SERVICE_ID,
            $updatingNotificationService
        );
    }

    public function down(Schema $schema): void
    {
        $this->getServiceManager()->unregister(UpdatingNotificationService::SERVICE_ID);
    }
}
