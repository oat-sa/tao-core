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
namespace oat\tao\model\notification\implementation;

use oat\tao\model\notification\AbstractNotificationService;
use oat\tao\model\notification\NotificationInterface;

class RdsNotification
    extends AbstractNotificationService

{

    const NOTIF_TABLE = 'notifications';

    const NOTIF_FIELD_ID           = 'id';
    const NOTIF_FIELD_RECIPIENT    = 'recipient';
    const NOTIF_FIELD_TITLE        = 'title';
    const NOTIF_FIELD_STATUS       = 'status';
    const NOTIF_FIELD_SENDER       = 'sender_id';
    const NOTIF_FIELD_SENDER_NANE  = 'sender_name';
    const NOTIF_FIELD_MESSAGE      = 'message';
    const NOTIF_FIELD_CREATION     = 'created_at';
    const NOTIF_FIELD_UPDATED      = 'updated_at';

    const OPTION_PERSISTENCE       = 'persistence';

    const DEFAULT_PERSISTENCE      = 'default';
    /**
     * @var \common_persistence_Manager
     */
    protected $persistence;

    /**
     * @return \common_persistence_SqlPersistence
     */
    protected function getPersistence()
    {
        if(is_null($this->persistence)) {

            $persistence = self::DEFAULT_PERSISTENCE;

            if($this->hasOption(self::OPTION_PERSISTENCE)) {
                $persistence = $this->getOption(self::OPTION_PERSISTENCE);
            }

            $persistenceManager = $this->getServiceManager()->get(\common_persistence_Manager::SERVICE_ID);
            $this->persistence  = $persistenceManager->getPersistenceById($persistence);
        }
        return $this->persistence;
    }

    protected function getAllFieldString() {
        return self::NOTIF_FIELD_RECIPIENT . ' , ' . self::NOTIF_FIELD_STATUS . ' , ' . self::NOTIF_FIELD_SENDER . ' , ' . self::NOTIF_FIELD_SENDER_NANE
            . ' , ' . self::NOTIF_FIELD_TITLE . ' , ' .  self::NOTIF_FIELD_MESSAGE . ' , ' . self::NOTIF_FIELD_CREATION . ' , ' . self::NOTIF_FIELD_UPDATED ;
    }

    public function sendNotification(NotificationInterface $notification)
    {
        $persistence = $this->getPersistence();

        $platform = $this->getPersistence()->getPlatForm();

        $sqlQuery    = 'INSERT INTO ' . self::NOTIF_TABLE .
                        ' (' . $this->getAllFieldString() . ') 
                            VALUES ( ? , ? , ? , ? , ? , ? , ? , ? )';

        $data = [
            $notification->getRecipient(),
            $notification->getStatus(),
            $notification->getSenderId(),
            $notification->getSenderName(),
            $notification->getTitle(),
            $notification->getMessage(),
            $platform->getNowExpression(),
            $platform->getNowExpression()
        ];

        $persistence->exec($sqlQuery , $data);

        return $notification;
    }

    public function getNotifications($userId)
    {
        $notification = [];
        $persistence = $this->getPersistence();

        $selectQuery = 'SELECT ' . self::NOTIF_FIELD_ID . ' , ' . $this->getAllFieldString() .
                       ' FROM ' . self::NOTIF_TABLE . ' WHERE ' .
                        self::NOTIF_FIELD_RECIPIENT . ' = ? ' .
                        'ORDER BY ' . self::NOTIF_FIELD_CREATION . ' DESC ' .
                        'LIMIT 20';

        $params      = [
            $userId
        ];

        $stmt   = $persistence->query($selectQuery , $params);
        $result = $stmt->fetchAll();

        foreach($result as $notificationDetail) {

            $userId     = $notificationDetail[self::NOTIF_FIELD_RECIPIENT];
            $title      = $notificationDetail[self::NOTIF_FIELD_TITLE];
            $message    = $notificationDetail[self::NOTIF_FIELD_MESSAGE];
            $senderId   = $notificationDetail[self::NOTIF_FIELD_SENDER];
            $senderName = $notificationDetail[self::NOTIF_FIELD_SENDER_NANE];
            $id         = $notificationDetail[self::NOTIF_FIELD_ID];
            $createdAt  = $notificationDetail[self::NOTIF_FIELD_CREATION];
            $updatedAt  = $notificationDetail[self::NOTIF_FIELD_UPDATED];
            $status     = $notificationDetail[self::NOTIF_FIELD_STATUS];
            $notification[] = new Notification($userId , $title , $message , $senderId , $senderName , $id , $createdAt , $updatedAt ,  $status);
        }

        return $notification;
    }

    public function getNotification($id)
    {
        $persistence = $this->getPersistence();

        $selectQuery = 'SELECT ' . self::NOTIF_FIELD_ID . ' , ' . $this->getAllFieldString() .
            ' FROM ' . self::NOTIF_TABLE . ' WHERE ' .
            self::NOTIF_FIELD_ID . ' = ? ';

        $params      = [
            $id
        ];

        $stmt               = $persistence->query($selectQuery , $params);
        $notificationDetail = $stmt->fetch();

        if($notificationDetail) {
            $userId    = $notificationDetail[self::NOTIF_FIELD_RECIPIENT];
            $title     = $notificationDetail[self::NOTIF_FIELD_TITLE];
            $message   = $notificationDetail[self::NOTIF_FIELD_MESSAGE];
            $senderId  = $notificationDetail[self::NOTIF_FIELD_SENDER];
            $id        = $notificationDetail[self::NOTIF_FIELD_ID];
            $createdAt = $notificationDetail[self::NOTIF_FIELD_CREATION];
            $updatedAt = $notificationDetail[self::NOTIF_FIELD_UPDATED];
            $status    = $notificationDetail[self::NOTIF_FIELD_STATUS];

            $user      = new \core_kernel_classes_Resource($userId);
            $sender    = new \core_kernel_classes_Resource($senderId);

            return new Notification($user , $title , $message , $sender , $id , $createdAt , $updatedAt ,  $status);
        }

        throw new \common_exception_NotFound('unknown notification id '. $id );
    }

    public function changeStatus(NotificationInterface $notification)
    {
        $updateQuery = 'UPDATE ' . self::NOTIF_TABLE . ' SET ' .
                            self::NOTIF_FIELD_UPDATED . ' = ? ,' .
                            self::NOTIF_FIELD_STATUS . ' = ? ' .
                            ' WHERE ' . self::NOTIF_FIELD_ID . ' = ? ';

        $persistence = $this->getPersistence();
        $platform = $this->getPersistence()->getPlatForm();

        $data =
            [
                $platform->getNowExpression(),
                $notification->getStatus(),
                $notification->getId(),
            ];

        return $persistence->exec($updateQuery , $data);

    }

    public function notificationCount($userId)
    {

        $persistence = $this->getPersistence();
        $count = [ NotificationInterface::CREATED_STATUS => 0 ];

        $selectQuery = 'SELECT ' . self::NOTIF_FIELD_STATUS . ' , COUNT(' . self::NOTIF_FIELD_ID . ') as cpt' .
            ' FROM ' . self::NOTIF_TABLE . ' WHERE ' .
            self::NOTIF_FIELD_RECIPIENT . ' = ? ' .
            '  GROUP BY ' . self::NOTIF_FIELD_STATUS ;

        $params      = [
            $userId
        ];

        $stmt   = $persistence->query($selectQuery , $params);

        if (($result = $stmt->fetchAll()) !== false) {

            foreach ($result as $statusCount) {
                $count[$statusCount[self::NOTIF_FIELD_STATUS]] = $statusCount['cpt'];
            }
        }

        return $count;

    }



}