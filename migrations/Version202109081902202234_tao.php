<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\model\Middleware\MiddlewareRequestHandler;
use oat\tao\model\Middleware\OpenAPISchemaValidateRequestMiddleware;
use oat\tao\scripts\tools\migrations\AbstractMigration;

final class Version202109081902202234_tao extends AbstractMigration
{
    public function getDescription(): string
    {
        return sprintf(
            'Register %s and %s',
            MiddlewareRequestHandler::class,
            OpenAPISchemaValidateRequestMiddleware::SERVICE_ID
        );
    }

    public function up(Schema $schema): void
    {
        $this->getServiceManager()->register(
            MiddlewareRequestHandler::SERVICE_ID,
            new MiddlewareRequestHandler([MiddlewareRequestHandler::OPTION_MAP => []])
        );

        $this->getServiceManager()->register(
            OpenAPISchemaValidateRequestMiddleware::SERVICE_ID,
            new OpenAPISchemaValidateRequestMiddleware(
                [OpenAPISchemaValidateRequestMiddleware::OPTION_SCHEMA_MAP, []]
            )
        );
    }

    public function down(Schema $schema): void
    {
        $this->getServiceManager()->unregister(MiddlewareRequestHandler::SERVICE_ID);
        $this->getServiceManager()->unregister(OpenAPISchemaValidateRequestMiddleware::SERVICE_ID);
    }
}
