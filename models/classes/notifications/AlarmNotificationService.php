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

use oat\oatbox\reporting\Report;
use oat\tao\model\event\TaoUpdateEvent;

/**
 * Notifies when an update
 *
 * Class UpdatingNotificationService
 * @author Andrey Niahrou <Andrei.Niahrou@1pt.com>
 * @package oat\tao\model\extension
 */
class AlarmNotificationService extends AbstractNotificationService
{
    public const SERVICE_ID = 'tao/AlarmNotificationService';

    /**
     * @param TaoUpdateEvent $event
     */
    public function listenTaoUpdateEvent(TaoUpdateEvent $event)
    {
        $reportMessages = $event->getReport()->filterChildrenByTypes([Report::TYPE_ERROR]);

        if (count($reportMessages) === 0) return;

        $message = '';
        /**@var $dispatchReport Report */
        foreach ($reportMessages as $dispatchReport) {
            $message .= $dispatchReport->getMessage() . PHP_EOL;
        }

        $alert = new Alert('Tao Update notifications: ' . ROOT_URL, $message);
        $this->sendNotifications($alert);
    }
}
