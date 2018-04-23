<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\model\search\index;

use oat\tao\model\TaoOntology;
use oat\tao\model\search\Search;
use oat\generis\model\kernel\persistence\smoothsql\search\ComplexSearchService;
use oat\search\helper\SupportedOperatorHelper;
use oat\tao\model\resources\ResourceIterator;

/**
 * Class IndexSinceLastRunService
 *
 * implementation of IndexService which do indexing of resourced updated or created since last launch of indexing.
 *
 * @package oat\tao\model\search\index
 */
class IndexSinceLastRunService extends IndexService
{
    const OPTION_LASTRUN_STORE = 'lastrun_store';
    const OPTION_INDEX_SINCE_LAST_RUN = 'index_since_last_run';
    const LAST_LAUNCH_TIME_KEY = 'tao/IndexService:lastLaunchTime';

    public function runIndexing()
    {
        $time = microtime(true);
        $iterator = $this->getResourceIterator($this->getLastIndexTime(), $time);
        $indexIterator = new IndexIterator($iterator);
        $indexIterator->setServiceLocator($this->getServiceLocator());
        $searchService = $this->getServiceLocator()->get(Search::SERVICE_ID);
        $result = $searchService->index($indexIterator);
        $this->updateLastIndexTime($time);
        $this->logDebug($result . ' resources have been indexed by ' . static::class);
        return $result;
    }

    /**
     * @return \Iterator
     * @param boolean $sinceLast load resources updated/created since last indexation
     */
    protected function getResourceIterator($from = null, $to = null)
    {
        if ($from === null || $from === 0) {
            return parent::getResourceIterator();
        }
        if ($to === null) {
            $to = microtime(true);
        }
        $search = $this->getServiceLocator()->get(ComplexSearchService::class);
        $queryBuilder = $search->query();
        $criteria = $queryBuilder->newQuery();
        $criteria->addCriterion(
            TaoOntology::PROPERTY_UPDATED_AT,
            SupportedOperatorHelper::BETWEEN,
            [$from, $to]
        );
        $iterator = new ResourceIterator($this->getIndexedClasses(), $criteria);
        $iterator->setServiceLocator($this->getServiceLocator());
        return $iterator;
    }

    /**
     * Update time of the last indexation
     * @throws \common_Exception
     */
    private function updateLastIndexTime($time)
    {
        $this->getPersistence()->set(self::LAST_LAUNCH_TIME_KEY, $time);
    }

    /**
     * Get time of the last indexation. 0 if no time in the storage.
     * @return integer
     */
    private function getLastIndexTime()
    {
        $result = $this->getPersistence()->get(self::LAST_LAUNCH_TIME_KEY);
        return $result ? $result : 0;
    }

    /**
     * @return \common_persistence_KeyValuePersistence
     * @throws
     */
    private function getPersistence()
    {
        if (!$this->hasOption(self::OPTION_LASTRUN_STORE)) {
            throw new \InvalidArgumentException('Persistence for ' . self::SERVICE_ID . ' is not configured');
        }
        $persistenceId = $this->getOption(self::OPTION_LASTRUN_STORE);
        return $this->getServiceLocator()->get(\common_persistence_Manager::SERVICE_ID)->getPersistenceById($persistenceId);
    }
}
