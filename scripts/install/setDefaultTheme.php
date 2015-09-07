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
 * @author lionel
 * @license GPLv2
 * @package package_name
 * @subpackage
 *
 */
use oat\tao\model\ThemeRegistry;
use oat\tao\model\websource\TokenWebSource;

$itemThemesDataPath   = FILES_PATH.'tao'.DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR;
$itemThemesDataPathFs = \tao_models_classes_FileSourceService::singleton()->addLocalSource('Theme FileSource', $itemThemesDataPath);

$websource = TokenWebSource::spawnWebsource($itemThemesDataPathFs);
ThemeRegistry::getRegistry()->setWebSource($websource->getId());

$s = DIRECTORY_SEPARATOR;
ThemeRegistry::getRegistry()->createTarget('frontOffice', array(
    'css' => 'tao'.$s.'views'.$s.'css'.$s.'tao-3.css',
    'templates' => array(
        'header-logo' => 'taoDelivery'.$s.'views'.$s.'templates'.$s.'DeliveryServer'.$s.'blocks'.$s.'header-logo.tpl',
        'footer' => 'taoDelivery'.$s.'views'.$s.'templates'.$s.'DeliveryServer'.$s.'blocks'.$s.'footer.tpl'
    )
));
ThemeRegistry::getRegistry()->createTarget('backOffice', array(
    'css' => 'tao'.$s.'views'.$s.'css'.$s.'tao-3.css',
    'templates' => array(
        'header-logo' => 'tao'.$s.'views'.$s.'templates'.$s.'blocks'.$s.'header-logo.tpl',
        'footer' => 'tao'.$s.'views'.$s.'templates'.$s.'blocks'.$s.'footer.tpl'
    )
));