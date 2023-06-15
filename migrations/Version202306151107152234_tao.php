<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\oatbox\reporting\Report;
use oat\tao\model\mvc\DefaultUrlModule\TaoPortalResolver;
use oat\tao\model\mvc\DefaultUrlService;
use oat\tao\model\theme\PortalTheme;
use oat\tao\model\theme\ThemeService;
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
        $this->registerPortalTheme();
    }

    public function down(Schema $schema): void
    {
        $this->unregisterLogoutActionResolver();
        $this->unregisterPortalTheme();
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

    public function registerPortalTheme(): void
    {
        $this->addReport(
            Report::createInfo(
                'Registering Portal Theme'
            )
        );

        $service = $this->getServiceLocator()->get(ThemeService::SERVICE_ID);
        $option = $service->getOption('available');
        $option['portal'] = new PortalTheme();

        $service->setOption('available', $option);

        $this->getServiceLocator()->register(ThemeService::SERVICE_ID, $service);
    }

    public function unregisterPortalTheme(): void
    {
        $this->addReport(
            Report::createInfo(
                'Unregistering Portal Theme'
            )
        );

        $service = $this->getServiceLocator()->get(ThemeService::SERVICE_ID);
        $option = $service->getOption('available');
        unset($option['portal']);

        $service->setOption('available', $option);

        $this->getServiceLocator()->register(ThemeService::SERVICE_ID, $service);
    }
}
