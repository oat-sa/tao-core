<?php

declare(strict_types=1);

namespace oat\tao\model\email;

use oat\generis\model\DependencyInjection\ContainerServiceProviderInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

class EmailServiceProvider implements ContainerServiceProviderInterface
{
    public const SERVICE_ID = 'tao.email';

    public function __invoke(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();

        $services
            ->set(EmailAddressResolver::class, EmailAddressResolver::class)
            ->public();
        $services
            ->alias(EmailAddressResolverInterface::class, EmailAddressResolver::class)
            ->public();

        $services
            ->set(NullTransport::class, NullTransport::class)
            ->public();
        $services
            ->alias(EmailTransportInterface::class, NullTransport::class)
            ->public();

        $services
            ->set(EmailHandler::class, EmailHandler::class)
            ->public()
            ->args([
                service(EmailTransportInterface::class),
                service(EmailAddressResolverInterface::class)
            ]);
        $services
            ->alias(EmailHandlerInterface::class, EmailHandler::class)
            ->public();

        $services
            ->set(self::SERVICE_ID, EmailService::class)
            ->public()
            ->args([
                service(EmailHandlerInterface::class)
            ]);
    }
}
