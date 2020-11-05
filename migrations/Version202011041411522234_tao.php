<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\model\externalNotifiers\OpsGenieNotifier;
use oat\tao\scripts\tools\migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version202011041411522234_tao extends AbstractMigration
{

    public function getDescription(): string
    {
        return 'Registration OpsGenieNotifier. Service for error reporting during taoUpdate to OpsGenie';
    }

    public function up(Schema $schema): void
    {
        $opsGenieNotifier = new OpsGenieNotifier();
        $opsGenieNotifier->setOption('base_uri', 'https://api.opsgenie.com/v2/');
        $opsGenieNotifier->setOption('token', '');

        $this->getServiceManager()->register(
            $opsGenieNotifier::SERVICE_ID,
            $opsGenieNotifier
        );

    }

    public function down(Schema $schema): void
    {
        $this->getServiceManager()->unregister(OpsGenieNotifier::SERVICE_ID);
    }
}
