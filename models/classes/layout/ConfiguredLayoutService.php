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
 * Copyright (c) 2020  (original work) Open Assessment Technologies SA;
 *
 * @author Oleksandr Zagovorychev <zagovorichev@gmail.com>
 */

declare(strict_types=1);

namespace oat\tao\model\layout;

use oat\oatbox\service\ConfigurableService;
use Context;
use Request;

/**
 * Make Layout data configurable
 *
 * Class ConfiguredLayoutService
 * @package oat\tao\model\layout
 */
class ConfiguredLayoutService extends ConfigurableService
{
    public const SERVICE_ID = 'tao/ConfiguredLayoutService';

    public const OPTION_PAGE_TITLE_SERVICE = 'pageTitleService';

    /**
     * Configured Title
     * @return string
     */
    public function getPageTitle(): string
    {
        return $this->getServiceLocator()
            ->get($this->getOption(self::OPTION_PAGE_TITLE_SERVICE))
            ->getTitle($this->getController(), $this->getAction(), $this->getRequest());
    }

    private function getController(): string
    {
        return $this->getContext()->getModuleName();
    }

    private function getAction(): string
    {
        return $this->getContext()->getActionName();
    }

    private function getRequest(): Request
    {
        return $this->getContext()->getRequest();
    }

    private function getContext(): Context
    {
        return Context::getInstance();
    }
}
