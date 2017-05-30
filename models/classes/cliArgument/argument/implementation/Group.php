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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\tao\model\cliArgument\argument\implementation;

use oat\oatbox\action\Action;
use oat\tao\model\cliArgument\argument\Argument;
use oat\tao\model\cliArgument\ArgumentLoader;

class Group extends ArgumentLoader implements Argument
{
    /** @var Argument */
    protected $argument = null;

    /**
     * Load the action with only one applicable argument, the first matched into isApplicable() method
     *
     * @param Action $action
     */
    public function load(Action $action)
    {
        if (! is_null($this->argument)) {
            $this->argument->load($action);
        }
    }

    /**
     * Check if provided params fit into one of option arguments
     * Only first applicable is taken under consideration
     *
     * @param array $params
     * @return bool
     */
    public function isApplicable(array $params)
    {
        foreach ($this->getArguments() as $argument) {
            if ($argument->isApplicable($params)) {
                $this->argument = $argument;
                return true;
            }
        }
        return false;
    }

    /**
     * Get all grouped arguments from options
     *
     * @return Argument[]
     */
    protected function getOptionArguments()
    {
        return $this->getOptions();
    }

}