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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */
namespace oat\tao\model\theme;

use oat\tao\helpers\Template;
use oat\oatbox\Configurable;

/**
 * Backwards compatibilit Theme build
 * based on original array
 * 
 * @author bout
 */
class CompatibilityTheme extends DefaultTheme implements Theme
{
    const OPTION_OLD_ID = 'old';
    
    public function getId()
    {
        return $this->getOption('id');
    }
    
    public function getLabel()
    {
        return $this->getOption('name');
    }
    
    public function getTemplate($id)
    {
        $templates = $this->getOption('templates');
        if (isset($templates[$id])) {
            return ROOT_PATH.$templates[$id];
        } else {
            return parent::getTemplate($id);
        }
    }
    
    public function getStylesheet()
    {
        return ROOT_URL.$this->getOption('path');
    }
}
