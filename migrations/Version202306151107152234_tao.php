<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\oatbox\reporting\Report;
use oat\tao\model\mvc\DefaultUrlModule\TaoPortalResolver;
use oat\tao\model\mvc\DefaultUrlService;
use oat\tao\scripts\tools\migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version202306151107152234_tao extends AbstractMigration
{

    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->registerLogoutActionResolver();

    }

    public function down(Schema $schema): void
    {
        $this->unregisterLogoutActionResolver();

    }

    public function registerLogoutActionResolver(): void
    {
        $this->addReport(
            Report::createInfo(
                'Registering Portal Logout Action Resolver'
            )
        );

        $service = $this->getServiceLocator()->get(DefaultUrlService::SERVICE_ID);
        $options = $service->getOptions();

        $logoutOptions = $options['logout'];
        $logoutRedirect = $logoutOptions['redirect'] ?? [];
        $logoutOptions['redirect']['class'] = TaoPortalResolver::class;
        $logoutOptions['redirect']['options'] = $logoutRedirect;

        $service->setOption('logout', $logoutOptions);

        $this->getServiceLocator()->register(DefaultUrlService::SERVICE_ID, $service);
    }

    public function unregisterLogoutActionResolver(): void
    {
        $this->addReport(
            Report::createInfo(
                'Unregistering Logout Action Resolver'
            )
        );

        $service = $this->getServiceLocator()->get(DefaultUrlService::SERVICE_ID);
        $options = $service->getOptions();

        $logoutOptions = $options['logout'];
        $logoutRedirect = $logoutOptions['redirect'] ?? [];
        $logoutOptions['redirect']['class'] = $logoutRedirect['options']['class'];
        $logoutOptions['redirect']['options'] = $logoutRedirect['options']['options'];

        $service->setOption('logout', $logoutOptions);

        $this->getServiceLocator()->register(DefaultUrlService::SERVICE_ID, $service);
    }
}
