<?php

declare(strict_types = 1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\oatbox\service\exception\InvalidServiceManagerException;
use oat\tao\model\counter\CounterService;
use oat\tao\scripts\install\RegisterCounterService;
use oat\tao\scripts\tools\migrations\AbstractMigration;

final class Version202108081928492234_tao extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Register ' . CounterService::class;
    }

    public function up(Schema $schema): void
    {
        $this->propagate(new RegisterCounterService)();
    }

    /**
     * @throws InvalidServiceManagerException
     */
    public function down(Schema $schema): void
    {
        $this->getServiceManager()->unregister(CounterService::SERVICE_ID);
    }
}
