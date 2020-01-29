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

namespace oat\tao\model\user;

use oat\oatbox\user\UserService;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\search\Search;
use oat\tao\model\TaoOntology;
use oat\generis\model\OntologyAwareTrait;

class GenerisUserService extends ConfigurableService implements UserService
{
    use OntologyAwareTrait;

    /**
     * {@inheritDoc}
     * @see \oat\oatbox\user\UserService::findUser()
     */
    public function findUser($searchString)
    {
        $searchService = $this->getServiceLocator()->get(Search::SERVICE_ID);
        $result = $searchService->query($searchString, TaoOntology::CLASS_URI_TAO_USER);
        return $this->getUsers($result);
    }

    /**
     * {@inheritDoc}
     * @see \oat\oatbox\user\UserService::getUser()
     */
    public function getUser($userId)
    {
        return new \core_kernel_users_GenerisUser($this->getResource($userId));
    }

    /**
     * {@inheritDoc}
     * @see \oat\oatbox\user\UserService::getUsers()
     */
    public function getUsers($userIds)
    {
        $users = [];
        foreach ($userIds as $id) {
            $users[$id] = $this->getUser($id); 
        }
        return $users;
    }
}
