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
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\theme;

use InvalidArgumentException;
use oat\tao\helpers\Template;

class PortalTheme extends ConfigurableTheme
{
    use PortalTemplateFinderTrait;

    public const THEME_ID = 'portal';

    public function getId()
    {
        return self::THEME_ID;
    }

    /**
     * (non-PHPdoc)
     * @see \oat\tao\model\theme\Theme::getLabel()
     */
    public function getLabel()
    {
        return __('Tao Default Theme');
    }

    /**
     * (non-PHPdoc)
     * @see \oat\tao\model\theme\Theme::getStylesheet()
     */
    public function getStylesheet($context = Theme::CONTEXT_BACKOFFICE)
    {
        return Template::css('tao-3.css', 'tao');
    }

    public function getTemplate($id, $context = Theme::CONTEXT_BACKOFFICE): ?string
    {
        try {
            return $this->findTemplateByIdOrFail($id);
        } catch (InvalidArgumentException $exception) {
            return parent::getTemplate($id, $context);
        }
    }
}
