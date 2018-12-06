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
 * Copyright (c) 2017-2018 (original work) Open Assessment Technologies SA;
 *
 */

use oat\tao\model\notification\NotificationServiceInterface;
use oat\tao\model\notification\NotificationInterface;
use oat\tao\model\notification\exception\NotListedNotification;

class tao_actions_Notification extends \tao_actions_CommonModule
{

    public function getCount()
    {
        $user = $this->getUserService()->getCurrentUser();

        /**
         * @var oat\tao\model\notification\NotificationServiceInterface $notificationService
         */
        $notificationService = $this->getServiceLocator()->get(NotificationServiceInterface::SERVICE_ID);
        try {
            $count = $notificationService->notificationCount($user->getUri());
        } catch (NotListedNotification $e) {
            return $this->returnError($e->getUserMessage());
        }
        return $this->returnJson($count);

    }

    public function getList()
    {
        $user = $this->getUserService()->getCurrentUser();

        /**
         * @var oat\tao\model\notification\NotificationServiceInterface $notificationService
         */
        $notificationService = $this->getServiceLocator()->get(NotificationServiceInterface::SERVICE_ID);
        try {
            $list = $notificationService->getNotifications($user->getUri());
        } catch (NotListedNotification $e) {
            return $this->returnError($e->getUserMessage());
        }
        return $this->returnJson($list);
    }

    public function getDetail()
    {
        if( $this->hasRequestParameter('id')) {
            $id = $this->getRequestParameter('id');
            /**
             * @var oat\tao\model\notification\NotificationServiceInterface $notificationService
             */
            $notificationService = $this->getServiceLocator()->get(NotificationServiceInterface::SERVICE_ID);
            try {
                $list = $notificationService->getNotification($id);
            } catch (NotListedNotification $e) {
                return $this->returnError($e->getUserMessage());
            }
            return $this->returnJson($list);
        }
        return $this->returnError(__('require notification ID'));
    }

    public function getUiList()
    {
        $user = $this->getUserService()->getCurrentUser();

        /**
         * @var oat\tao\model\notification\NotificationServiceInterface $notificationService
         */
        $notificationService = $this->getServiceLocator()->get(NotificationServiceInterface::SERVICE_ID);
        try {
            $list = $notificationService->getNotifications($user->getUri());
        } catch (NotListedNotification $e) {
            return $this->returnError($e->getUserMessage());
        }
        /**
         * @var NotificationInterface $notif
         */
        foreach ($list as $notif) {
            if($notif->getStatus() === NotificationInterface::CREATED_STATUS) {
                $notif->setStatus(NotificationInterface::READ_STATUS);
                $notificationService->changeStatus($notif);
                $notif->setStatus(NotificationInterface::CREATED_STATUS);
            }
        }

        $this->setData('notif-list' , $list);
        $this->setView('notification/list.tpl');
    }

    /**
     * @return tao_models_classes_UserService
     */
    protected function getUserService()
    {
        return $this->getServiceLocator()->get(tao_models_classes_UserService::SERVICE_ID);
    }
}