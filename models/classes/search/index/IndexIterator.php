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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA;
 */
namespace oat\tao\model\search\index;

use oat\oatbox\service\ServiceManager;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Class IndexIterator
 * @package oat\tao\model\search\index
 */
class IndexIterator extends \IteratorIterator implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /** @var IndexService  */
    private $indexService = null;

        /**
     * @return \oat\oatbox\service\ConfigurableService|IndexService
     */
    protected function getIndexer() {
        if (is_null($this->indexService)) {
            $this->indexService = $this->getServiceLocator()->get(IndexService::SERVICE_ID);
        }

        return $this->indexService;
    }

    public function valid()
    {
        return $this->getInnerIterator()->valid();
    }

    /**
     * @return mixed|IndexDocument
     * @throws \common_Exception
     * @throws \common_exception_InconsistentData
     */
    public function current() {
        return $this->getIndexer()->createDocumentFromResource($this->getInnerIterator()->current());
    }
}