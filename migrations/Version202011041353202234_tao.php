<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\model\extension\UpdateErrorNotifier;
use oat\tao\scripts\tools\migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version202011041353202234_tao extends AbstractMigration
{

    public function getDescription(): string
    {
        return 'Registration UpdateErrorNotifier. Service for error reporting during taoUpdate';
    }

    public function up(Schema $schema): void
    {
        $updateErrorNotifier = new UpdateErrorNotifier();
        $updateErrorNotifier->setOption('notifiers', []);

        $this->getServiceManager()->register(
            $updateErrorNotifier::SERVICE_ID,
            $updateErrorNotifier
        );
    }

    public function down(Schema $schema): void
    {
        $this->getServiceManager()->unregister(UpdateErrorNotifier::SERVICE_ID);
    }
}
