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
 * Copyright (c) 2017 (original work) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 *
 */

namespace oat\tao\actions;

/**
 * Class Log
 *
 * Controller is used to aggregate log given from tao client side (or other client services).
 *
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
class Log extends \tao_actions_CommonModule
{

    /**
     *
     */
    public function log()
    {

        if ($this->hasRequestParameter('messages')) {
            $messages = $this->getRequestParameter('messages');
            foreach ($messages as $message) {

            }
            var_dump($messages);
        }
    }

}