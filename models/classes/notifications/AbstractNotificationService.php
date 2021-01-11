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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\model\notifications;

use oat\oatbox\service\ConfigurableService;
use oat\tao\model\notifiers\NotifierInterface;

/**
 * Notifies when an update
 *
 * Class AbstractNotificationService
 * @author Andrey Niahrou <Andrei.Niahrou@1pt.com>
 * @package oat\tao\model\extension
 */
abstract class AbstractNotificationService extends ConfigurableService
{
    public const OPTION_NOTIFIERS = 'notifiers';

    /**
     * @param Notification $notification
     */
    public function sendNotifications(Notification $notification): void
    {
        if (!$this->hasOption(self::OPTION_NOTIFIERS) || !count($this->getOption(self::OPTION_NOTIFIERS))) {
            return;
        }

        $notifiers = $this->getOption(self::OPTION_NOTIFIERS);
        $this->notify($notification, $notifiers);
    }

    /**
     * @param Notification $notification
     * @param array $notifiers
     */
    private function notify(Notification $notification, array $notifiers): void
    {
        foreach ($notifiers as $notifierConfig) {
            /**@var $notifier NotifierInterface */

            $notifier = new $notifierConfig['class'](...$notifierConfig['params']);

            $notifier->notify($notification->getMessage(), $notification->getDescription());
        }
    }
}
