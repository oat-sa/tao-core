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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\tao\model\session\restSessionFactory;

use oat\oatbox\service\ConfigurableService;
use oat\oatbox\user\LoginFailedException;

/**
 * Class RestSessionFactory
 *
 * A factory to build rest session based on configured adapters
 *
 * @package oat\tao\model\session\restSessionFactory
 */
class RestSessionFactory extends ConfigurableService
{
    const SERVICE_ID = 'tao/restSessionFactory';

    const OPTION_BUILDER = 'builders';

    /**
     * Create a rest session based on builders.
     *
     * Give the request and resolver to builder to know if it is applicable
     * If yes, create and start the session from it
     *
     * @param $request
     * @param $resolver
     * @return bool
     * @throws LoginFailedException
     */
    public function createSessionFromRequest($request, $resolver)
    {
        /** @var SessionBuilder $builder */
        foreach ($this->getSessionBuilders() as $builder) {
            if ($builder->isApplicable($request, $resolver)) {
                return \common_session_SessionManager::startSession($builder->getSession($request));
            }
        }
        return false;
    }

    /**
     * Fetch rest session builder from the config
     *
     * @return SessionBuilder[]
     */
    protected function getSessionBuilders()
    {
        $adapters = is_array($this->getOption(self::OPTION_BUILDER)) ? $this->getOption(self::OPTION_BUILDER) : [];
        foreach ($adapters as $key => $adapter) {
            if (!is_a($adapter, SessionBuilder::class, true)) {
                throw new \LogicException('Session adapter must implement interface "SessionBuilder".');
            }
            $adapters[$key] = $this->propagate(new $adapter());
        }
        return $adapters;
    }
}