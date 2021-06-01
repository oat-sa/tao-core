<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\model\search\SearchProxy;
use oat\tao\model\search\strategy\GenerisSearch;
use oat\tao\scripts\tools\migrations\AbstractMigration;

final class Version202106010945122237_tao extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Register ' . SearchProxy::class;
    }

    public function up(Schema $schema): void
    {
        $this->getServiceManager()->register(
            SearchProxy::SERVICE_ID,
            new SearchProxy(
                [
                    SearchProxy::OPTION_DEFAULT_SEARCH_CLASS => GenerisSearch::class
                ]
            )
        );
    }

    public function down(Schema $schema): void
    {
        $this->getServiceManager()->unregister(SearchProxy::SERVICE_ID);
    }
}
