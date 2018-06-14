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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\model\taskQueue\Task;

/**
 * Wrapper class to store callables (even Action instances) in task queue for later execution
 *
 * @author Gyula Szucs <gyula@taotesting.com>
 */
final class CallbackTask extends AbstractTask implements CallbackTaskInterface
{
    private $callable;
    private $enqueued = false;

    /**
     * @return \common_report_Report
     */
    public function __invoke()
    {
        return call_user_func($this->getCallable(), $this->getParameters());
    }

    public function __clone()
    {
        if (is_object($this->callable)) {
            $this->callable = clone $this->callable;
        }

        $this->enqueued = false;

        parent::__clone();
    }

    /**
     * @param callable $callable
     * @return CallbackTaskInterface
     */
    public function setCallable(callable $callable)
    {
        if ($callable instanceof TaskAwareInterface) {
            $callable->setTask($this);
        }

        $this->callable = $callable;

        return $this;
    }

    /**
     * @return callable|string|object
     */
    public function getCallable()
    {
        if (is_null($this->callable) && ($callableFromJSON = $this->getMetadata('__callable__'))) {
            $this->callable = $callableFromJSON;
        }

        return $this->callable;
    }

    /**
     * @return CallbackTaskInterface
     */
    public function markAsEnqueued()
    {
        $this->enqueued = true;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEnqueued()
    {
        return $this->enqueued;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $callableClassOrArray = $this->getCallable();

        if (is_object($callableClassOrArray) && !$callableClassOrArray instanceof \JsonSerializable) {
            $callableClassOrArray = get_class($callableClassOrArray);
        }

        $this->setMetadata('__callable__', $callableClassOrArray);

        return parent::jsonSerialize();
    }

    /**
     * @return CallbackTaskInterface
     */
    public function applyWorkerContext()
    {
        parent::applyWorkerContext();

        if ($this->getCallable() instanceof WorkerContextAwareInterface) {
            $this->getCallable()->applyWorkerContext();
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function hasChildren()
    {
        if ($this->getCallable() instanceof ChildTaskAwareInterface) {
            return $this->getCallable()->hasChildren();
        }

        return parent::hasChildren();
    }

    /**
     * @return string[]
     */
    public function getChildren()
    {
        if ($this->getCallable() instanceof ChildTaskAwareInterface) {
            return $this->getCallable()->getChildren();
        }

        return parent::getChildren();
    }
}