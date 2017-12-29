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
 * Copyright (c) 2002-2008 (original work) Public Research Centre Henri Tudor & University of Luxembourg (under the project TAO & TAO2);
 *			   2008-2010 (update and modification) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);\n *			   2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 *             2013 (update and modification) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */
use oat\tao\model\oauth\OauthService;
use oat\oatbox\service\ServiceManager;
/**
 * @author Joel Bout, <joel@taotesting.com>
 * @deprecated please use OauthService::getDataStore()
 */
class tao_models_classes_oauth_DataStore
{

	/**
	 * deprecated helper function to find the OauthConsumer RDF Resource
	 *
	 * @access public
	 * @author Joel Bout, <joel@taotesting.com>
	 * @param  string consumer_key
	 * @return core_kernel_classes_Resource
	 */
	public function findOauthConsumerResource($consumer_key)
	{
	    return $this->getService()->getDataStore()->findOauthConsumerResource($consumer_key);
	}

    /**
     * @return OauthService
     */
    private function getService() {
        return ServiceManager::getServiceManager()->get(OauthService::SERVICE_ID);
    }
}
