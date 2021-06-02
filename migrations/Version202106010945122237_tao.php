<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\model\search\SearchProxy;
use oat\tao\model\search\Search;
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
        /** @var Search $legacySearch */
        $currentSearch = $this->getServiceManager()->get(Search::SERVICE_ID);
        $isGenerisSearch = $currentSearch instanceof GenerisSearch;
        $defaultSearch = $isGenerisSearch ? $currentSearch : new GenerisSearch();

        $searchProxy = new SearchProxy();
        $searchProxy->setOption(SearchProxy::OPTION_DEFAULT_SEARCH_CLASS, $defaultSearch);

        if (!$isGenerisSearch) {
            $searchProxy->setOption(SearchProxy::OPTION_ADVANCED_SEARCH_CLASS, $currentSearch);
        }

        $this->getServiceManager()->register(SearchProxy::SERVICE_ID, $searchProxy);
    }

    public function down(Schema $schema): void
    {
        /** @var SearchProxy $searchProxy */
        $searchProxy = $this->getServiceManager()->get(SearchProxy::SERVICE_ID);

        /** @var Search $search */
        $legacySearch = $searchProxy->hasOption(SearchProxy::OPTION_ADVANCED_SEARCH_CLASS)
            ? $searchProxy->getOption(SearchProxy::OPTION_ADVANCED_SEARCH_CLASS)
            : $searchProxy->getOption(SearchProxy::OPTION_DEFAULT_SEARCH_CLASS);

        $this->getServiceManager()->unregister(SearchProxy::SERVICE_ID);
        $this->getServiceManager()->register(Search::SERVICE_ID, $legacySearch);
    }
}
