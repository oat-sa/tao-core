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

/**
 * Class Log
 *
 * Controller is used to aggregate log given from tao client side (or other client services).
 *
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
class tao_actions_Log extends \tao_actions_CommonModule
{

    /**
     * Log the message sent from client side
     */
    public function log()
    {
        $result = [];
        if ($this->hasRequestParameter('messages')) {
            $messages = json_decode($this->getRawParameter('messages'), true);
            foreach ($messages as $message) {
                \common_Logger::singleton()->log($this->getLevel($message['level']), json_encode($message), ['frontend']);
            }
        }
        $this->returnJson($result);
    }

    /**
     * Map log level
     * @todo make it compatible with PRS-3 log levels
     * @param $level
     * @return int
     */
    private function getLevel($level)
    {
        $result = \common_Logger::TRACE_LEVEL;
        switch ($level) {
            case 'fatal' :
                $result = \common_Logger::FATAL_LEVEL;
                break;
            case 'error' :
                $result = \common_Logger::ERROR_LEVEL;
                break;
            case 'warn' :
                $result = \common_Logger::WARNING_LEVEL;
                break;
            case 'info' :
                $result = \common_Logger::INFO_LEVEL;
                break;
            case 'debug' :
                $result = \common_Logger::DEBUG_LEVEL;
                break;
            case 'trace' :
                $result = \common_Logger::DEBUG_LEVEL;
                break;
        }
        return $result;
    }

}