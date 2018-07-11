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

namespace oat\tao\model\taskQueue;

use oat\oatbox\PhpSerializable;
use oat\tao\model\taskQueue\Queue\Broker\QueueBrokerInterface;
use oat\tao\model\taskQueue\Task\TaskInterface;
use Psr\Log\LoggerAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

/**
 * @author Gyula Szucs <gyula@taotesting.com>
 */
interface QueueInterface extends QueuerInterface, LoggerAwareInterface, PhpSerializable, ServiceLocatorAwareInterface
{
    /**
     * QueueInterface constructor.
     *
     * @param string               $name
     * @param QueueBrokerInterface $broker
     * @param int                  $weight
     */
    public function __construct($name, QueueBrokerInterface $broker, $weight = 1);

    /**
     * @return string
     */
    public function __toString();

    /**
     * Initialize queue.
     *
     * @return void
     */
    public function initialize();

    /**
     * Returns queue name.
     *
     * @return string
     */
    public function getName();

    /**
     * Returns queue weight.
     *
     * @return int
     */
    public function getWeight();

    /**
     * @param int $weight
     * @return QueueInterface
     */
    public function setWeight($weight);

    /**
     * Is the given queue a sync queue?
     *
     * @return bool
     */
    public function isSync();

    /**
     * The amount of tasks that can be received in one pop by this queue.
     *
     * @return int
     */
    public function getNumberOfTasksToReceive();

    /**
     * Set new broker.
     *
     * @param QueueBrokerInterface $broker
     * @return QueueInterface
     */
    public function setBroker(QueueBrokerInterface $broker);
}