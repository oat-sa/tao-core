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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\scripts\install;

use oat\generis\persistence\PersistenceManager;
use oat\oatbox\extension\InstallAction;
use oat\tao\model\accessControl\func\AccessRule;
use oat\tao\model\accessControl\func\AclProxy;
use oat\tao\model\Lists\Business\Contract\ValueCollectionRepositoryInterface;
use oat\tao\model\Lists\Business\Service\ValueCollectionService;
use oat\tao\model\Lists\DataAccess\Repository\RdfValueCollectionRepository;
use oat\tao\model\Lists\Presentation\Web\RequestHandler\ValueCollectionSearchRequestHandler;
use oat\tao\model\Lists\Presentation\Web\RequestValidator\ValueCollectionSearchRequestValidator;
use oat\tao\model\user\TaoRoles;

class RegisterValueCollectionServices extends InstallAction
{
    public function __invoke($params)
    {
        /** @var PersistenceManager $persistenceManager */
        $persistenceManager = $this->getServiceManager()->get(PersistenceManager::SERVICE_ID);

        $this->getServiceManager()->register(
            ValueCollectionSearchRequestHandler::SERVICE_ID,
            new ValueCollectionSearchRequestHandler(
                new ValueCollectionSearchRequestValidator()
            )
        );

        $valueCollectionRepository = new RdfValueCollectionRepository($persistenceManager, 'default');

        $this->getServiceManager()->register(
            ValueCollectionRepositoryInterface::SERVICE_ID,
            $valueCollectionRepository
        );

        $this->getServiceManager()->register(
            ValueCollectionService::SERVICE_ID,
            new ValueCollectionService($valueCollectionRepository)
        );

        AclProxy::applyRule(
            new AccessRule(AccessRule::GRANT, TaoRoles::BACK_OFFICE, ['ext' => 'tao', 'mod' => 'PropertyValues'])
        );
    }
}
