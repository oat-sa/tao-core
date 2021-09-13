<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\helpers\NamespaceHelper;
use oat\tao\scripts\tools\migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version202109101332292234_tao extends AbstractMigration
{

    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->getServiceManager()->register(NamespaceHelper::SERVICE_ID, new NamespaceHelper([
                    'namespaces' => [
                        LOCAL_NAMESPACE
                    ]
                ]
            )
        );
    }

    public function down(Schema $schema): void
    {
        $this->getServiceManager()->unregister(NamespaceHelper::SERVICE_ID);

    }
}
