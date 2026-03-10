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

declare(strict_types=1);

namespace oat\tao\model\notification\implementation;

use common_exception_NotFound;
use common_persistence_Persistence as Persistence;
use common_persistence_SqlPersistence;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use oat\generis\model\OntologyAwareTrait;
use oat\generis\persistence\PersistenceManager;
use oat\generis\persistence\sql\SchemaProviderInterface;
use oat\tao\model\notification\AbstractNotificationService;
use oat\tao\model\notification\Notification;

/**
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
     * @var Persistence
     */
    protected $persistence;

    public function sendNotification(Notification $notification): Notification
    {
        $this->getPersistence()->insert(self::NOTIFICATION_TABLE, $this->prepareNotification($notification));
        return $notification;
    }

    /**
     * @return Notification[]
     */
    public function getNotifications(string $userId): array
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

        $result = $persistence->query($selectQuery, $params)->fetchAllAssociative();

        foreach ($result as $notificationDetail) {
            $notification[] = $this->createNotification($notificationDetail);
        }

        return $notification;
    }

    protected function getPersistence(): Persistence
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
     * @throws common_exception_NotFound
     */
    public function getNotification(string $id): Notification
    {
        /** @var common_persistence_SqlPersistence $persistence */
        $persistence = $this->getPersistence();

        $selectQuery = 'SELECT ' . self::NOTIFICATION_FIELD_ID . ' , ' . $this->getAllFieldString() .
            ' FROM ' . self::NOTIFICATION_TABLE . ' WHERE ' .
            self::NOTIFICATION_FIELD_ID . ' = ? ';

        $params = [
            $id,
        ];

        $notificationDetail = $persistence->query($selectQuery, $params)->fetchAssociative();

        if ($notificationDetail) {
            return $this->createNotification($notificationDetail);
        }

        throw new common_exception_NotFound('Error notification not found ,  requested id: ' . $id);
    }

    protected function getAllFieldString(): string
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

    public function notificationCount(string $userId): array
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

        $resultSet = $persistence->query($selectQuery, $params);
        $result = $resultSet->fetchAllAssociative();

        if ($result !== []) {
            foreach ($result as $statusCount) {
                $count[$statusCount[self::NOTIFICATION_FIELD_STATUS]] = $statusCount['cpt'];
            }
        }

        return $count;
    }

    public function changeStatus(Notification $notification): bool
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

        return (bool)$persistence->exec($updateQuery, $data);
    }

    private function createNotification(array $notificationDetail): Notification
    {
        return new Notification(
            $notificationDetail[self::NOTIFICATION_FIELD_RECIPIENT],
            $notificationDetail[self::NOTIFICATION_FIELD_TITLE],
            $notificationDetail[self::NOTIFICATION_FIELD_MESSAGE],
            $notificationDetail[self::NOTIFICATION_FIELD_SENDER],
            $notificationDetail[self::NOTIFICATION_FIELD_SENDER_NAME],
            $notificationDetail[self::NOTIFICATION_FIELD_ID],
            $notificationDetail[self::NOTIFICATION_FIELD_CREATION],
            $notificationDetail[self::NOTIFICATION_FIELD_UPDATED],
            $notificationDetail[self::NOTIFICATION_FIELD_STATUS]
        );
    }

    abstract protected function prepareNotification(Notification $notification): array;
}
