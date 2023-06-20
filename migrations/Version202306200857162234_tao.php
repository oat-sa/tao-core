<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\oatbox\reporting\Report;
use oat\tao\model\mvc\DefaultUrlService;
use oat\tao\scripts\install\RegisterTaoLogoutActionResolver;
use oat\tao\scripts\tools\migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 *
 * phpcs:disable Squiz.Classes.ValidClassName
 */
final class Version202306200857162234_tao extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Register Tao Logout Action Resolver';
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
                'Registering Tao Logout Action Resolver'
            )
        );

        $registerAction = $this->propagate(new RegisterTaoLogoutActionResolver());
        $registerAction();
    }

    public function unregisterLogoutActionResolver(): void
    {
        $this->addReport(
            Report::createInfo(
                'Unregistering Tao Logout Action Resolver'
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
