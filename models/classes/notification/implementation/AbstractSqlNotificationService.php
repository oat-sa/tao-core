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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\model\notification\implementation;

use common_exception_NotFound;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use oat\generis\model\OntologyAwareTrait;
use oat\generis\persistence\PersistenceManager;
use oat\generis\persistence\sql\SchemaProviderInterface;
use oat\tao\model\notification\AbstractNotificationService;
use oat\tao\model\notification\Notification;

/**
 * Class AbstractSqlNotificationService
 *
 * @deprecated This class is used by client only. It will be moved to client specific extension
 */
abstract class AbstractSqlNotificationService extends AbstractNotificationService implements SchemaProviderInterface
{
    use OntologyAwareTrait;

    public const NOTIFICATION_TABLE = 'notifications';

    public const NOTIFICATION_FIELD_ID = 'id';
    public const NOTIFICATION_FIELD_RECIPIENT = 'recipient';
    public const NOTIFICATION_FIELD_TITLE = 'title';
    public const NOTIFICATION_FIELD_STATUS = 'status';
    public const NOTIFICATION_FIELD_SENDER = 'sender_id';
    public const NOTIFICATION_FIELD_SENDER_NAME = 'sender_name';
    public const NOTIFICATION_FIELD_MESSAGE = 'message';
    public const NOTIFICATION_FIELD_CREATION = 'created_at';
    public const NOTIFICATION_FIELD_UPDATED = 'updated_at';

    public const OPTION_PERSISTENCE = 'persistence';

    public const DEFAULT_PERSISTENCE = 'default';

    /**
     * @var PersistenceManager
     */
    protected $persistence;

    /**
     * @param Notification $notification
     *
     * @return Notification
     */
    public function sendNotification(Notification $notification)
    {
        $this->getPersistence()->insert(self::NOTIFICATION_TABLE, $this->prepareNotification($notification));
        return $notification;
    }

    public function getNotifications($userId)
    {
        $notification = [];
        $persistence = $this->getPersistence();

        $selectQuery = 'SELECT ' . self::NOTIFICATION_FIELD_ID . ' , ' . $this->getAllFieldString() .
            ' FROM ' . self::NOTIFICATION_TABLE . ' WHERE ' .
            self::NOTIFICATION_FIELD_RECIPIENT . ' = ? ' .
            'ORDER BY ' . self::NOTIFICATION_FIELD_CREATION . ' DESC ' .
            'LIMIT 20';

        $params      = [
            $userId
        ];

        $stmt   = $persistence->query($selectQuery, $params);
        $result = $stmt->fetchAll();

        foreach ($result as $notificationDetail) {
            $userId     = $notificationDetail[self::NOTIFICATION_FIELD_RECIPIENT];
            $title      = $notificationDetail[self::NOTIFICATION_FIELD_TITLE];
            $message    = $notificationDetail[self::NOTIFICATION_FIELD_MESSAGE];
            $senderId   = $notificationDetail[self::NOTIFICATION_FIELD_SENDER];
            $senderName = $notificationDetail[self::NOTIFICATION_FIELD_SENDER_NAME];
            $id         = $notificationDetail[self::NOTIFICATION_FIELD_ID];
            $createdAt  = $notificationDetail[self::NOTIFICATION_FIELD_CREATION];
            $updatedAt  = $notificationDetail[self::NOTIFICATION_FIELD_UPDATED];
            $status     = $notificationDetail[self::NOTIFICATION_FIELD_STATUS];
            $notification[] = new Notification($userId, $title, $message, $senderId, $senderName, $id, $createdAt, $updatedAt, $status);
        }

        return $notification;
    }

    /**
     * @return PersistenceManager
     */
    protected function getPersistence()
    {
        if ($this->persistence === null) {
            $persistence = self::DEFAULT_PERSISTENCE;

            if ($this->hasOption(self::OPTION_PERSISTENCE)) {
                $persistence = $this->getOption(self::OPTION_PERSISTENCE);
            }

            $persistenceManager = $this->getServiceLocator()->get(PersistenceManager::SERVICE_ID);
            $this->persistence  = $persistenceManager->getPersistenceById($persistence);
        }
        return $this->persistence;
    }

    /**
     * @param string $id
     *
     * @return Notification
     * @throws common_exception_NotFound
     */
    public function getNotification($id)
    {
        $persistence = $this->getPersistence();

        $selectQuery = 'SELECT ' . self::NOTIFICATION_FIELD_ID . ' , ' . $this->getAllFieldString() .
            ' FROM ' . self::NOTIFICATION_TABLE . ' WHERE ' .
            self::NOTIFICATION_FIELD_ID . ' = ? ';

        $params      = [
            $id
        ];

        $stmt               = $persistence->query($selectQuery, $params);
        $notificationDetail = $stmt->fetch();

        if ($notificationDetail) {
            $userId    = $notificationDetail[self::NOTIFICATION_FIELD_RECIPIENT];
            $title     = $notificationDetail[self::NOTIFICATION_FIELD_TITLE];
            $message   = $notificationDetail[self::NOTIFICATION_FIELD_MESSAGE];
            $senderId  = $notificationDetail[self::NOTIFICATION_FIELD_SENDER];
            $id        = $notificationDetail[self::NOTIFICATION_FIELD_ID];
            $createdAt = $notificationDetail[self::NOTIFICATION_FIELD_CREATION];
            $updatedAt = $notificationDetail[self::NOTIFICATION_FIELD_UPDATED];
            $status    = $notificationDetail[self::NOTIFICATION_FIELD_STATUS];

            $user      = $this->getResource($userId);
            $sender    = $this->getResource($senderId);

            return new Notification($user, $title, $message, $sender, $id, $createdAt, $updatedAt, $status);
        }

        throw new \common_exception_NotFound('unknown notification id ' . $id);
    }

    /**
     * @return string
     */
    protected function getAllFieldString()
    {
        return self::NOTIFICATION_FIELD_RECIPIENT . ' , '
            . self::NOTIFICATION_FIELD_STATUS . ' , '
            . self::NOTIFICATION_FIELD_SENDER . ' , '
            . self::NOTIFICATION_FIELD_SENDER_NAME . ' , '
            . self::NOTIFICATION_FIELD_TITLE . ' , '
            . self::NOTIFICATION_FIELD_MESSAGE . ' , '
            . self::NOTIFICATION_FIELD_CREATION . ' , '
            . self::NOTIFICATION_FIELD_UPDATED;
    }

    /**
     * @param string $userId
     *
     * @return array
     */
    public function notificationCount($userId)
    {

        $persistence = $this->getPersistence();
        $count = [ Notification::CREATED_STATUS => 0 ];

        $selectQuery = 'SELECT '
            . self::NOTIFICATION_FIELD_STATUS
            . ' , COUNT('
            . self::NOTIFICATION_FIELD_ID
            . ') as cpt'
            . ' FROM '
            . self::NOTIFICATION_TABLE
            . ' WHERE '
            . self::NOTIFICATION_FIELD_RECIPIENT
            . ' = ? '
            . '  GROUP BY '
            . self::NOTIFICATION_FIELD_STATUS;

        $params = [
            $userId,
        ];

        /** @var Statement $stmt */
        $stmt = $persistence->query($selectQuery, $params);

        if (($result = $stmt->fetchAll()) !== false) {
            foreach ($result as $statusCount) {
                $count[$statusCount[self::NOTIFICATION_FIELD_STATUS]] = $statusCount['cpt'];
            }
        }

        return $count;
    }

    public function changeStatus(Notification $notification)
    {
        $updateQuery = 'UPDATE ' . self::NOTIFICATION_TABLE . ' SET ' .
            self::NOTIFICATION_FIELD_UPDATED . ' = ? ,' .
            self::NOTIFICATION_FIELD_STATUS . ' = ? ' .
            ' WHERE ' . self::NOTIFICATION_FIELD_ID . ' = ? ';

        $persistence = $this->getPersistence();
        /** @var AbstractPlatform $platform */
        $platform = $this->getPersistence()->getPlatForm();

        $data =
            [
                $platform->getNowExpression(),
                $notification->getStatus(),
                $notification->getId(),
            ];

        return $persistence->exec($updateQuery , $data);
    }

    /**
     * @param Notification $notification
     *
     * @return array
     */
    abstract protected function prepareNotification(Notification $notification);
}
