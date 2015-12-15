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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA;
 *
 *
 */

use oat\oatbox\service\ServiceManager;
use oat\tao\model\requiredAction\implementation\RequiredActionService;

/**
 * @author Aleh Hutnikau <hutniaku@1pt.com>
 * @package tao
 *
 */
class tao_actions_RequiredAction extends \tao_actions_Main
{

    const CONF_CODE_OF_CONDUCT = 'codeOfConduct';

    public function codeofconduct()
    {
        /** @var RequiredActionService $service */
        $service = ServiceManager::getServiceManager()->get(RequiredActionService::CONFIG_ID);

        if ($this->isRequestPost() && $this->getRequestParameter('accepted') ) {
            $action = $service->getRequiredAction('codeOfConduct');
            $action->completed();
        }

        $ext = common_ext_ExtensionsManager::singleton()->getExtensionById('tao');
        $codeOfConduct = $ext->getConfig(self::CONF_CODE_OF_CONDUCT);

        $this->setData('content-template', array('requiredAction/codeofconduct.tpl', 'tao'));
        $this->setData('code_of_conduct', $codeOfConduct);
        $this->setView('layout.tpl', 'tao');
    }
}
