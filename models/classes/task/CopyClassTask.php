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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA.
 *
 * @author Andrei Shapiro <andrei.shapiro@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\model\task;

use Throwable;
use oat\oatbox\action\Action;
use oat\oatbox\reporting\Report;
use oat\oatbox\log\LoggerAwareTrait;
use oat\generis\model\OntologyAwareTrait;
use oat\tao\model\taskQueue\Task\TaskAwareTrait;
use Laminas\ServiceManager\ServiceLocatorAwareTrait;
use oat\tao\model\taskQueue\Task\TaskAwareInterface;
use oat\tao\model\resources\Service\ClassCopierProxy;
use Laminas\ServiceManager\ServiceLocatorAwareInterface;
use oat\tao\model\resources\Contract\ClassCopierInterface;
use oat\oatbox\log\logger\extender\ContextExtenderInterface;

class CopyClassTask implements Action, TaskAwareInterface, ServiceLocatorAwareInterface
{
    use OntologyAwareTrait;
    use ServiceLocatorAwareTrait;
    use LoggerAwareTrait;
    use TaskAwareTrait;

    public const PARAM_CLASS_URI = 'classUri';
    public const PARAM_DESTINATION_CLASS_URI = 'destinationClassUri';

    public function __invoke($params): Report
    {
        try {
            $class = $this->getClass($params[self::PARAM_CLASS_URI]);
            $destinationClass = $this->getClass($params[self::PARAM_DESTINATION_CLASS_URI]);
            $newClass = $this->getClassCopier()->copy($class, $destinationClass);

            return Report::createSuccess(__('The class has been copied.'), $newClass);
        } catch (Throwable $exception) {
            $this->logError($exception->getMessage(), [ContextExtenderInterface::CONTEXT_EXCEPTION => $exception]);

            return Report::createError(__('Failed to copy class.'));
        }
    }

    private function getClassCopier(): ClassCopierInterface
    {
        return $this->getServiceLocator()->getContainer()->get(ClassCopierProxy::class);
    }
}
