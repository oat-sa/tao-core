<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\model\session\Business\Contract\SessionCookieServiceInterface;
use oat\tao\scripts\install\RegisterSessionCookieService;
use oat\tao\scripts\tools\migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version202008201045492234_tao extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Register session cookie services';
    }

    public function up(Schema $schema): void
    {
        $this->addReport(
            $this->propagate(
                new RegisterSessionCookieService()
            )()
        );
    }

    public function down(Schema $schema): void
    {
        $this->getServiceManager()->unregister(SessionCookieServiceInterface::SERVICE_ID);
    }
}
