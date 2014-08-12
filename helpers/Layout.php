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

use oat\tao\helpers\Template;
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
            'version-type' => '',
            'is-unstable'  => true,
            'is-sandbox'   => false,
            'logo'         => 'tao-logo.png',
            'link'         => 'http://taotesting.com',
            'msg'          => __('Tao Home')
        );

        switch($taoReleaseStatus){
            case 'alpha':
            case 'demoA':
                $params['version-type'] = __('Alpha Version');
                $params['logo']         = 'tao-logo-alpha.png';
                $params['link']         = 'http://forge.taotesting.com/projects/tao';
                $params['msg']          = __('Please report bugs, ideas, comments or feedback on the TAO Forge');
                break;

            case 'beta':
            case 'demoB':
                $params['version-type'] = __('Beta Version');
                $params['logo']         = 'tao-logo-beta.png';
                $params['link']         = 'http://forge.taotesting.com/projects/tao';
                $params['msg']          = __('Please report bugs, ideas, comments or feedback on the TAO Forge');
                break;

            case 'demoS':
                $params['version-type'] = __('Demo Sandbox');
                $params['is-unstable']   = false;
                $params['is-sandbox']    = true;
                break;

            default:
                $params['is-unstable'] = false;
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

    /**
     * Build script element for AMD loader
     *
     * @return string
     */
    public static function getAmdLoader(){
        if(\common_session_SessionManager::isAnonymous()) {
            $amdLoader = array(
                'src' => Template::js('lib/require.js', 'tao'),
                'data-main' => TAOBASE_WWW . 'js/main'
            );
        }
        else if(\tao_helpers_Mode::is('production')) {
            $amdLoader = array(
                'src' => Template::js('main.min.js', 'tao'),
                'data-config' => get_data('client_config_url')
            );
        }
        else {
            $amdLoader = array(
                'src' => Template::js('lib/require.js', 'tao'),
                'data-config' => get_data('client_config_url'),
                'data-main' => TAOBASE_WWW . 'js/main'
            );
        }

        $amdScript = '<script id="amd-loader" ';
        foreach($amdLoader as $attr => $value) {
            $amdScript .= $attr . '="' . $value . '" ';
        }
        return trim($amdScript) . '></script>';
    }

    /**
     * @return string
     */
    public static function getTitle() {
        $title = get_data('title');
        return $title ? $title : PRODUCT_NAME . ' ' .  TAO_VERSION;
    }


}
