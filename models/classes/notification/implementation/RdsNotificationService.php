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
 *
 */

namespace oat\tao\model\notification\implementation;

use common_exception_InconsistentData;
use common_persistence_SqlPersistence;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use oat\generis\persistence\sql\SchemaCollection;
use oat\tao\model\notification\Notification;

/**
 * Class RdsNotificationService
 *
 * @deprecated This class is used by client only. It will be moved to client specific extension
 */
class RdsNotificationService extends AbstractSqlNotificationService
{
    /**
     * @param Notification $notification
     *
     * @return array
     */
    protected function prepareNotification(Notification $notification): array
    {
        /** @var AbstractPlatform $platform */
        $platform = $this->getPersistence()->getPlatForm();
        return [
            self::NOTIFICATION_FIELD_RECIPIENT => $notification->getRecipient(),
            self::NOTIFICATION_FIELD_STATUS => $notification->getStatus(),
            self::NOTIFICATION_FIELD_SENDER => $notification->getSenderId(),
            self::NOTIFICATION_FIELD_SENDER_NAME => $notification->getSenderName(),
            self::NOTIFICATION_FIELD_TITLE => $notification->getTitle(),
            self::NOTIFICATION_FIELD_MESSAGE => $notification->getMessage(),
            self::NOTIFICATION_FIELD_CREATION => $platform->getNowExpression(),
            self::NOTIFICATION_FIELD_UPDATED => $platform->getNowExpression(),
        ];
    }

    /**
     * Allows a class to adapt the schemas as required
     *
     * @param SchemaCollection $schemaCollection
     *
     * @throws common_exception_InconsistentData
     */
    public function provideSchema(SchemaCollection $schemaCollection): void
    {
        $schema = $schemaCollection->getSchema($this->getOption(self::OPTION_PERSISTENCE));
        $queueTable = $schema->createTable(self::NOTIFICATION_TABLE);

        $queueTable->addOption('engine', 'MyISAM');
        $queueTable->addColumn(self::NOTIFICATION_FIELD_ID, 'integer', ['notnull' => true, 'autoincrement' => true]);
        $queueTable->addColumn(self::NOTIFICATION_FIELD_RECIPIENT, 'string', ['notnull' => true, 'length' => 255]);
        $queueTable->addColumn(self::NOTIFICATION_FIELD_STATUS, 'integer', ['default' => 0, 'notnull' => false, 'length' => 255]);
        $queueTable->addColumn(self::NOTIFICATION_FIELD_TITLE, 'string', ['length' => 255]);
        $queueTable->addColumn(self::NOTIFICATION_FIELD_MESSAGE, 'text', ['default' => null]);
        $queueTable->addColumn(self::NOTIFICATION_FIELD_SENDER, 'string', ['default' => null, 'notnull' => false, 'length' => 255]);
        $queueTable->addColumn(self::NOTIFICATION_FIELD_SENDER_NAME, 'string', ['default' => null, 'notnull' => false, 'length' => 255]);
        $queueTable->addColumn(self::NOTIFICATION_FIELD_CREATION, 'datetime', ['notnull' => true]);
        $queueTable->addColumn(self::NOTIFICATION_FIELD_UPDATED, 'datetime', ['notnull' => true]);
        $queueTable->setPrimaryKey([self::NOTIFICATION_FIELD_ID]);
    }
}
