<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\model\security\xsrf\TokenService;
use oat\tao\scripts\tools\migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version202008030748582234_tao extends AbstractMigration
{

    public function getDescription(): string
    {
        return 'Set new option `validateTokens` to `true`. This option can disable validation tokens on FE side.';
    }

    public function up(Schema $schema): void
    {
        $service = $this->getServiceLocator()->get(TokenService::SERVICE_ID);
        if (!$service->hasOption(TokenService::VALIDATE_TOKENS_OPT)) {
            $service->setOption(TokenService::VALIDATE_TOKENS_OPT, true);
            $this->getServiceManager()->register(TokenService::SERVICE_ID, $service);
        }
    }

    public function down(Schema $schema): void
    {
        $service = $this->getServiceLocator()->get(TokenService::SERVICE_ID);
        if ($service->hasOption(TokenService::VALIDATE_TOKENS_OPT)) {
            $options = $service->getOptions();
            unset($options[TokenService::VALIDATE_TOKENS_OPT]);
            $service->setOptions($options);
            $this->getServiceManager()->register(TokenService::SERVICE_ID, $service);
        }
    }
}
