<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\model\menu\SectionVisibilityFilter;
use oat\tao\scripts\tools\migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version202009221232112234_tao extends AbstractMigration
{

    public function getDescription(): string
    {
        return 'Service that will allow hiding sections that contains enabled feature flags';
    }

    public function up(Schema $schema): void
    {
        $this->getServiceManager()->register(
            SectionVisibilityFilter::SERVICE_ID,
            new SectionVisibilityFilter([])
        );
    }

    public function down(Schema $schema): void
    {
        $this->getServiceManager()->unregister(SectionVisibilityFilter::SERVICE_ID);
    }
}
