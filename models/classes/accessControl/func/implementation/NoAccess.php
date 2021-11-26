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
 * Copyright (c) 2013-2021 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\model\accessControl\func\implementation;

use oat\oatbox\user\User;
use Psr\Log\LoggerInterface;
use oat\oatbox\log\logger\AdvancedLogger;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\accessControl\func\AccessRule;
use oat\tao\model\accessControl\func\FuncAccessControl;

/**
 * Fallback functional Access Control denying all access
 *
 * @author Joel Bout, <joel@taotesting.com>
 */
class NoAccess extends ConfigurableService implements FuncAccessControl
{
    /**
     * {@inheritdoc}
     */
    public function accessPossible(User $user, $controller, $action)
    {
        $this->getAdvancedLogger()->info('Access denied.');

        return false;
    }

    public function applyRule(AccessRule $rule)
    {
        // nothing to do
    }

    public function revokeRule(AccessRule $rule)
    {
        // nothing to do
    }

    private function getAdvancedLogger(): LoggerInterface
    {
        return $this->getServiceManager()->getContainer()->get(AdvancedLogger::ACL_LOGGER);
    }
}
