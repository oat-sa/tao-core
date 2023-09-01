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
 * Copyright (c) 2015-2020 (original work) Open Assessment Technologies SA
 *
 */

declare(strict_types=1);

namespace oat\tao\model\event;

use oat\oatbox\event\Event;
use oat\oatbox\reporting\ReportInterface;

class TaoUpdateEvent implements Event
{
    /**
     * @var ReportInterface
     */
    private $report;

    /**
     * @param ReportInterface $report
     */
    public function __construct(ReportInterface $report)
    {
        $this->report = $report;
    }

    /**
     * Return a unique name for this event
     * @see \oat\oatbox\event\Event::getName()
     */
    public function getName()
    {
        return get_class($this);
    }

    /**
     * @return ReportInterface
     */
    public function getReport()
    {
        return $this->report;
    }
}
