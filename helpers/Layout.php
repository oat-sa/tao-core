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
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA;
 *
 *
 */

namespace oat\tao\helpers;

use oat\tao\model\menu\Icon;
class Layout{


    /**
     * Compute the parameters for the release message
     *
     * @param $taoReleaseStatus
     * @return array
     */
    public static function getReleaseMsgData($taoReleaseStatus){

        $params = array(
            'versionType' => '',
            'isUnstable'  => true,
            'isSandbox'   => false,
            'logo'        => 'tao-logo.png',
            'logo-link'   => 'http://taotesting.com',
            'logo-title'  => 'Tao Home'
        );

        switch($taoReleaseStatus){
            case 'alpha':
            case 'demoA':
                $params['versionType'] = __('Alpha Version');
                $params['logo']        = 'tao-logo-alpha.png';
                $params['logo-link']   = 'http://forge.taotesting.com/projects/tao';
                $params['logo-title']  = 'Tao Forge';
                break;

            case 'beta':
            case 'demoB':
                $params['versionType'] = __('Beta Version');
                $params['logo']        = 'tao-logo-beta.png';
                $params['logo-link']   = 'http://forge.taotesting.com/projects/tao';
                $params['logo-title']  = 'Tao Forge';
                break;

            case 'demoS':
                $params['versionType'] = __('Demo Sandbox');
                $params['isUnstable']  = false;
                $params['isSandbox']   = true;
                break;

            default:
                $params['isUnstable'] = false;
        }

        return $params;
    }


    /**
     * Compute the expiration time for the sandbox version
     *
     * @return string
     */
    public static function getSandboxExpiration(){
        $d          = new \DateTime();
        $weekday    = $d->format('w');
        $weekNumber = $d->format('W');
        $diff       = $weekNumber % 2 ? 7 : 6 - $weekday;
        $d->modify(sprintf('+ %d day', $diff));
        $date      = $d->format('Y-m-d');
        $remainder = strtotime($date) - time();
        $days      = floor($remainder / 86400);
        $hours     = floor(($remainder % 86400) / 3600);
        $minutes   = floor(($remainder % 3600) / 60);

        return $days . ' ' . (($days > 1) ? __('days') : __('day')) . ' '
        . $hours . ' ' . (($hours > 1) ? __('hours') : __('hour')) . ' '
        . __('and') . ' '
        . $minutes . ' ' . (($minutes > 1) ? __('minutes') : __('minute')) . '.';
    }

    /**
     * $iconArray defined in oat\tao\model\menu\Perspective::fromSimpleXMLElement
     *
     * @todo This function is a stub that assumes that all icons are in the TAO font.
     * One possible way to make this independent from the font would be to use $extensionId as a prefix
     * and also to load a custom style-sheet
     *
     * @param Icon $icon
     //* @param $extensionId // could be used as a prefix
     * @return string icon as html
     */
    public static function renderMenuIcon($icon = null) {
        $iconId = !is_null($icon)
            ? $icon->getId()
            : 'icon-extension';
        
        return sprintf('<span class="%s"></span>', $iconId);
        
    }

}
