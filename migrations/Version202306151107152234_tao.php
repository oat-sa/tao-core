<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\oatbox\reporting\Report;
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
        return 'Registering Portal Theme';
    }

    public function up(Schema $schema): void
    {
        $this->registerPortalTheme();
    }

    public function down(Schema $schema): void
    {
        $this->unregisterPortalTheme();
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
        $option[PortalTheme::THEME_ID] = new PortalTheme();

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
        unset($option[PortalTheme::THEME_ID]);

        $service->setOption('available', $option);

        $this->getServiceLocator()->register(ThemeService::SERVICE_ID, $service);
    }
}
