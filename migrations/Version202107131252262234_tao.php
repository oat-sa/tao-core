<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\model\action\ActionBlackList;
use oat\tao\scripts\tools\migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version202107131252262234_tao extends AbstractMigration
{

    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->getServiceManager()->register(
            ActionBlackList::SERVICE_ID,
            new ActionBlackList([
                ActionBlackList::OPTION_DISABLED_ACTIONS => []
            ])
        );
    }

    public function down(Schema $schema): void
    {
        $this->getServiceManager()->unregister(ActionBlackList::SERVICE_ID);

    }
}
