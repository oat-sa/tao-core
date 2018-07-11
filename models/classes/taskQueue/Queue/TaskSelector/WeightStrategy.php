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

namespace oat\tao\model\taskQueue\Queue\TaskSelector;

use oat\oatbox\log\LoggerAwareTrait;
use oat\oatbox\PhpSerializeStateless;
use oat\tao\model\taskQueue\QueueInterface;
use Psr\Log\LoggerAwareInterface;

/**
 * Implements a strategy for selecting a queue randomly taking into account the known weights.
 *
 * @author Gyula Szucs <gyula@taotesting.com>
 */
class WeightStrategy implements SelectorStrategyInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;
    use PhpSerializeStateless;

    /**
     * @inheritdoc
     */
    public function pickNextTask(array $queues)
    {
        $pickedQueue = $this->pickQueueByWeight($queues);

        $this->logDebug('Queue "' . strtoupper($pickedQueue->getName()) . '" picked by WeightStrategy');

        return $pickedQueue->dequeue();
    }

    /**
     * @return int
     */
    public function getWaitTime()
    {
        return 1;
    }

    /**
     * Picks randomly a queue based on weight.
     *
     * For example, an array like ['A'=>5, 'B'=>45, 'C'=>50] means that "A" has a 5% chance of being selected, "B" 45%, and "C" 50%.
     * The values are simply relative to each other. If one value weight was 2, and the other weight of 1,
     * the value with the weight of 2 has about a 66% chance of being selected.
     *
     * @inheritdoc
     */
    private function pickQueueByWeight(array $queues)
    {
        $weights = array_map(function(QueueInterface $queue) {
            return $queue->getWeight();
        }, $queues);

        $rand = mt_rand(1, array_sum($weights));

        /** @var QueueInterface $queue */
        foreach ($queues as $queue) {
            $rand -= $queue->getWeight();
            if ($rand <= 0) {
                return $queue;
            }
        }
    }
}
