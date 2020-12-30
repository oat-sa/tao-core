<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\tao\model\service\ApplicationService;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version202012300805132234_tao extends AbstractMigration
{

    public function getDescription(): string
    {
        return 'Set ApplicationService::OPTION_INSTALLATION_FINISHED';
    }

    public function up(Schema $schema): void
    {
        $applicationService = $this->getServiceLocator()->get(ApplicationService::SERVICE_ID);
        $applicationService->setOption(ApplicationService::OPTION_INSTALLATION_FINISHED, true);
        $this->getServiceLocator()->register(ApplicationService::SERVICE_ID, $applicationService);
    }

    public function down(Schema $schema): void
    {
        $applicationService = $this->getServiceLocator()->get(ApplicationService::SERVICE_ID);
        $options = $applicationService->getOptions();
        unset($options[ApplicationService::OPTION_INSTALLATION_FINISHED]);
        $applicationService->setOptions($options);
        $this->getServiceLocator()->register(ApplicationService::SERVICE_ID, $applicationService);
    }
}
