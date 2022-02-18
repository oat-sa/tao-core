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
 * Copyright (c) 2018-2022 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\search\tasks;

use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\action\Action;
use oat\oatbox\reporting\ReportInterface;
use oat\tao\model\taskQueue\Task\TaskAwareInterface;
use oat\tao\model\taskQueue\Task\TaskAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Class UpdateResourceInIndex
 *
 * @author Ilya Yarkavets <ilya@taotesting.com>
 * @package oat\tao\model\search\tasks
 */
class UpdateResourceInIndex
    extends AbstractSearchTask
    implements Action, TaskAwareInterface
{
    use OntologyAwareTrait;
    use TaskAwareTrait;

    public function __invoke($params): ReportInterface
    {
        if (empty($params) || empty($params[0])) {
            throw new \common_exception_MissingParameter();
        }

        $document = $this->getDocumentBuilder()->createDocumentFromResource(
            $this->getResource($params[0])
        );

        $numberOfIndexed = $this->getLegacySearchService()->index([$document]);

        if ($numberOfIndexed === 0) {
            return $this->buildErrorReport(
                'Zero documents were added/updated in index.'
            );
        } elseif ($numberOfIndexed === 1) {
            return $this->buildSuccessReport(
                'Document in index was successfully updated.'
            );
        } else {
            return $this->buildWarningReport(
                'The number or indexed documents is different than the expected total'
            );
        }
    }
}
