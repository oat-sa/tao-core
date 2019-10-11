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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\model\notification\implementation;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\model\notification\NotificationInterface;

class RdsNotification extends AbstractRdsNotification
{
    /**
     * @inheritdoc
     */
    public function prepareNotification(NotificationInterface $notification)
    {
        $platform = $this->getPersistence()->getPlatForm();

        return [
            self::NOTIF_FIELD_RECIPIENT => $notification->getRecipient(),
            self::NOTIF_FIELD_STATUS => $notification->getStatus(),
            self::NOTIF_FIELD_SENDER => $notification->getSenderId(),
            self::NOTIF_FIELD_SENDER_NANE => $notification->getSenderName(),
            self::NOTIF_FIELD_TITLE => $notification->getTitle(),
            self::NOTIF_FIELD_MESSAGE => $notification->getMessage(),
            self::NOTIF_FIELD_CREATION => $platform->getNowExpression(),
            self::NOTIF_FIELD_UPDATED => $platform->getNowExpression(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function createNotificationTable(Schema $schema)
    {
        $table = $schema->createtable(AbstractRdsNotification::NOTIF_TABLE);
        $table->addColumn(RdsNotification::NOTIF_FIELD_ID, 'integer', ['notnull' => true, 'autoincrement' => true]);
        $table->addColumn(RdsNotification::NOTIF_FIELD_RECIPIENT, 'string', ['notnull' => true, 'length' => 255]);
        $table->addColumn(RdsNotification::NOTIF_FIELD_STATUS, 'integer', ['default' => 0, 'notnull' => false, 'length' => 255]);
        $table->addColumn(RdsNotification::NOTIF_FIELD_TITLE, 'string', ['length' => 255]);
        $table->addColumn(RdsNotification::NOTIF_FIELD_MESSAGE, 'text', ['default' => null]);
        $table->addColumn(RdsNotification::NOTIF_FIELD_SENDER, 'string', ['default' => null, 'notnull' => false, 'length' => 255]);
        $table->addColumn(RdsNotification::NOTIF_FIELD_SENDER_NANE, 'string', ['default' => null, 'notnull' => false, 'length' => 255]);
        $table->addColumn(RdsNotification::NOTIF_FIELD_CREATION, 'datetime', ['notnull' => true]);
        $table->addColumn(RdsNotification::NOTIF_FIELD_UPDATED, 'datetime', ['notnull' => true]);
        
        return $table;
    }
}
