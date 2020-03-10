<?php declare(strict_types=1);

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

namespace oat\tao\model\notification\persistence;

use common_persistence_SqlPersistence as Persistence;
use oat\generis\persistence\PersistenceManager;
use oat\oatbox\service\ConfigurableService;

class AbstractNotificationPersistence extends ConfigurableService
{
    public const NOTIFICATION_TABLE = 'notifications';

    public const NOTIFICATION_FIELD_ID = 'id';
    public const NOTIFICATION_FIELD_RECIPIENT = 'recipient';
    public const NOTIFICATION_FIELD_TITLE = 'title';
    public const NOTIFICATION_FIELD_STATUS = 'status';
    public const NOTIFICATION_FIELD_SENDER = 'sender_id';
    public const NOTIFICATION_FIELD_SENDER_NANE = 'sender_name';
    public const NOTIFICATION_FIELD_MESSAGE = 'message';
    public const NOTIFICATION_FIELD_CREATION = 'created_at';
    public const NOTIFICATION_FIELD_UPDATED = 'updated_at';

    public const OPTION_PERSISTENCE = 'persistence';

    public const DEFAULT_PERSISTENCE = 'default';

    /** @var Persistence */
    private $persistence;

    /**
     * @return Persistence
     */
    public function getPersistence()
    {
        if ($this->persistence === null) {
            $persistenceId = $this->hasOption(self::OPTION_PERSISTENCE)
                ? $this->getOption(self::OPTION_PERSISTENCE)
                : self::DEFAULT_PERSISTENCE;

            $this->persistence = $this->getPersistenceManager()->getPersistenceById($persistenceId);
        }

        return $this->persistence;
    }

    /**
     * @return PersistenceManager
     */
    private function getPersistenceManager()
    {
        return $this->getServiceLocator()->get(PersistenceManager::SERVICE_ID);
    }
}