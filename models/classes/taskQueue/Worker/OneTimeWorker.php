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

namespace oat\tao\model\taskQueue\Worker;

/**
 * A simple worker implementation to fetch the latest task from a queue and run it.
 * Exits after one run.
 *
 * @author Gyula Szucs <gyula@taotesting.com>
 */
final class OneTimeWorker extends AbstractWorker
{
    public function run()
    {
        $this->logDebug('Starting OneTimeWorker.');

        try{
            $this->logDebug('Fetching tasks from queue ');

            $task = $this->queuer->dequeue();

            if ($task) {
                $this->processTask($task);
            }

            unset($task);
        } catch (\Exception $e) {
            $this->logError('Fetching data from queue failed with MSG: '. $e->getMessage());
        }

        $this->logDebug('OneTimeWorker finished.');
    }
}