<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\model\Middleware\MiddlewareChainBuilder;
use oat\tao\scripts\tools\migrations\AbstractMigration;

final class Version202109081902202234_tao extends AbstractMigration
{

    public function getDescription(): string
    {
        return sprintf('Register %s', MiddlewareChainBuilder::class);
    }

    public function up(Schema $schema): void
    {
        $this->getServiceManager()->register(
            MiddlewareChainBuilder::SERVICE_ID,
            new MiddlewareChainBuilder([MiddlewareChainBuilder::MAP => []])
        );
    }

    public function down(Schema $schema): void
    {
        $this->getServiceManager()->unregister(MiddlewareChainBuilder::SERVICE_ID);
    }
}
