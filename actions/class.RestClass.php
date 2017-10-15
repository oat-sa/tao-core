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

use \oat\generis\model\OntologyAwareTrait;

/**
 * Class tao_actions_RestResourceController
 *
 * The rest controller to manage resource APIs
 */
class tao_actions_RestClass extends tao_actions_RestResource
{
    use OntologyAwareTrait;

    const CLASS_PARAMETER = 'classUri';

    /**
     *
     * @requiresRight classUri READ
     */
    public function getAll()
    {
        if ($this->isRequestGet()) {
            try {
                $class = $this->getClassParameter();
                $classes = $this->getResourceService()->getClasses($class);
                $this->returnSuccess([$classes]);
            } catch (common_Exception $e) {
                $this->returnFailure($e);
            }
        }

        $this->returnFailure(new common_exception_MethodNotAllowed(__METHOD__ . ' only accepts GET method'));
    }

}
