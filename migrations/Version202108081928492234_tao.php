<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use common_Exception;
use Doctrine\DBAL\Schema\Schema;
use oat\oatbox\service\exception\InvalidServiceManagerException;
use oat\oatbox\service\ServiceNotFoundException;
use oat\tao\model\counter\CounterService;
use oat\tao\scripts\install\RegisterCounterService;
use oat\tao\scripts\tools\migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version202108081928492234_tao extends AbstractMigration
{

    public function getDescription(): string
    {
        return 'Register ' . CounterService::class;
    }

    /**
     * @param Schema $schema
     * @throws InvalidServiceManagerException
     * @throws common_Exception
     * @throws ServiceNotFoundException
     */
    public function up(Schema $schema): void
    {
        $registerCounterService = new RegisterCounterService();
        $this->propagate($registerCounterService);
        $registerCounterService([]);
    }

    /**
     * @param Schema $schema
     * @throws InvalidServiceManagerException
     */
    public function down(Schema $schema): void
    {
        $this->getServiceManager()->unregister(CounterService::SERVICE_ID);
    }
}
