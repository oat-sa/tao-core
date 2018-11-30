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
 * Copyright (c) 2013-2018 Open Assessment Technologies S.A.
 *
 */

use oat\tao\model\lock\LockManager;
use oat\tao\helpers\UserHelper;
use oat\tao\model\accessControl\AclProxy;
use oat\generis\model\OntologyAwareTrait;

/**
 * control the lock on a given resource
 *
 * @author plichart
 * @package taoGroups
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 */
class tao_actions_Lock extends tao_actions_CommonModule
{
    use OntologyAwareTrait;

	/**
	 * actions that get prevented by a lock are forwareded to this action
	 * parameter view is currently ignored
	 */
	public function locked()
    {
        $this->defaultData();
	    $resource = $this->getResource($this->getRequestParameter('id'));
	    $lockData = LockManager::getImplementation()->getLockData($resource);

	    $this->setData('topclass-label',
	        $this->hasRequestParameter('topclass-label') ? $this->getRequestParameter('topclass-label') : __('Resource')
        );

	    if (AclProxy::hasAccess($this->getSession()->getUser(), __CLASS__, 'forceRelease', array('uri' => $resource->getUri()))) {
	        $this->setData('id', $resource->getUri());
            $this->setData('forceRelease', true);
	    }

	    $this->setData('lockDate', $lockData->getCreationTime());
	    $this->setData('ownerHtml', UserHelper::renderHtmlUser($lockData->getOwnerId()));

	    if ($this->hasRequestParameter('view') && $this->hasRequestParameter('ext')) {
	        $this->setView($this->getRequestParameter('view'), $this->getRequestParameter('ext'));
	    } else {
	        $this->setView('Lock/locked.tpl', 'tao');
	    }
	}

	public function release($uri)
	{
	    $resource = $this->getResource($uri);
        try {
            $userId = $this->getSession()->getUser()->getIdentifier();
            $success = LockManager::getImplementation()->releaseLock($resource, $userId);
            return $this->returnJson(array(
                'success' => $success,
                'message' => $success
                    ? __('%s has been released', $resource->getLabel())
                    : __('%s could not be released', $resource->getLabel())
            ));

        //the connected user is not the owner of the lock
        } catch (common_exception_Unauthorized $e) {

            return $this->returnJson(array(
            	'success' => false,
                'message' => __('You are not authorised to remove this lock')
            ));
        }
    }

    public function forceRelease($uri)
    {
        $success = LockManager::getImplementation()->forceReleaseLock(
            $this->getResource($uri)
        );
        return $this->returnJson(array(
            'success' => $success
        ));
    }
}
