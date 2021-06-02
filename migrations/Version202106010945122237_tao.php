<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\search\SearchProxy;
use oat\tao\model\search\Search;
use oat\tao\scripts\tools\migrations\AbstractMigration;

final class Version202106010945122237_tao extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Register ' . SearchProxy::class;
    }

    public function up(Schema $schema): void
    {
        /** @var Search $legacySearch */
        $legacySearch = $this->getServiceManager()->get(Search::SERVICE_ID);

        $searchProxy = new SearchProxy(
            [
                SearchProxy::OPTION_DEFAULT_SEARCH_CLASS => $legacySearch
            ]
        );

        $this->getServiceManager()->register(SearchProxy::SERVICE_ID, $searchProxy);
    }

    public function down(Schema $schema): void
    {
        /** @var SearchProxy $searchProxy */
        $searchProxy = $this->getServiceManager()->get(SearchProxy::SERVICE_ID);

        /** @var ConfigurableService|Search $legacySearch */
        $legacySearch = $searchProxy->getOption(SearchProxy::OPTION_DEFAULT_SEARCH_CLASS);

        $this->getServiceManager()->unregister(SearchProxy::SERVICE_ID);
        $this->getServiceManager()->register(Search::SERVICE_ID, $legacySearch);
    }
}
