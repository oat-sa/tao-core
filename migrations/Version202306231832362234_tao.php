<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\oatbox\reporting\Report;
use oat\tao\model\theme\PortalTheme;
use oat\tao\model\theme\ThemeServiceInterface;
use oat\tao\scripts\install\RegisterPortalTheme;
use oat\tao\scripts\tools\migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version202306231832362234_tao extends AbstractMigration
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

        $this->runAction(new RegisterPortalTheme());
    }

    public function unregisterPortalTheme(): void
    {
        $this->addReport(
            Report::createInfo(
                'Unregistering Portal Theme'
            )
        );

        /** @var ThemeServiceInterface $service */
        $service = $this->getServiceLocator()->get(ThemeServiceInterface::SERVICE_ID);
        $service->removeThemeById(PortalTheme::THEME_ID);

        $this->getServiceLocator()->register(ThemeServiceInterface::SERVICE_ID, $service);
    }
}
