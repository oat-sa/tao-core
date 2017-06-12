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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA
 *
 */
namespace oat\tao\model\cliArgument;

use oat\oatbox\action\Action;
use oat\tao\model\cliArgument\argument\Argument;

class ArgumentService extends ArgumentLoader
{
    const SERVICE_ID = 'tao/cliArgumentLoader';

    const ARGUMENT_OPTION = 'arguments';

    /**
     * Get arguments from config and check if there are applicable
     * In case of yes, process them
     *
     * @param Action $action
     * @param array $params
     */
    public function load(Action $action, array $params)
    {
        /** @var Argument $argument */
        foreach ($this->getArguments() as $argument) {
            if ($argument->isApplicable($params)) {
                $this->getServiceManager()->propagate($argument);
                $argument->load($action);
            }
        }
    }

    /**
     * Get list of serialized arguments from options
     *
     * @return array
     */
    protected function getOptionArguments()
    {
        return $this->hasOption(self::ARGUMENT_OPTION) ? $this->getOption(self::ARGUMENT_OPTION) : [];
    }

}