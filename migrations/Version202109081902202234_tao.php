<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\model\Middleware\MiddlewareRequestHandler;
use oat\tao\scripts\tools\migrations\AbstractMigration;

final class Version202109081902202234_tao extends AbstractMigration
{

    public function getDescription(): string
    {
        return sprintf('Register %s', MiddlewareRequestHandler::class);
    }

    public function up(Schema $schema): void
    {
        $this->getServiceManager()->register(
            MiddlewareRequestHandler::SERVICE_ID,
            new MiddlewareRequestHandler([MiddlewareRequestHandler::MAP => []])
        );
    }

    public function down(Schema $schema): void
    {
        $this->getServiceManager()->unregister(MiddlewareRequestHandler::SERVICE_ID);
    }
}
