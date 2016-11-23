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
 */

namespace oat\tao\controller\api;

class TaskQueue extends \tao_actions_RestController
{
    const REST_TASK_ID = 'id';

    public function index()
    {
        if (!$this->hasRequestParameter(self::REST_TASK_ID)) {
            throw new \common_exception_MissingParameter(self::REST_TASK_ID, $this->getRequestURI());
        }

        $taskId = $this->getRequestParameter(self::REST_TASK_ID);



        $this->returnSuccess(array('id' => $taskId));
    }
}
