<?php
/*  
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
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 * 
 */

/**
 * Utility to display error messages and such
 *
 * @author Dieter Raber, <dieter@taotesting.com>
 * @package tao
 * @subpackage helpers
 */
class tao_helpers_Feedback
{

  /**
   * @param $type
   * @param $msg
   * @param bool $closer
   * @return string
   */
  protected static function dispatch($type, $msg, $closer=true){
    $closer = $closer ? '<span class="icon-close close-trigger" title="' . __('Remove Message') . '"></span>' : '';
    return sprintf('<div class="msg-%s"><span class="icon-%s"></span>%s%s</div>', $type, $type, $msg, $closer);
  }

  /**
   * @param $msg
   * @param bool $closer
   * @return string
   */
  public static function error($msg, $closer=true) {
    return self::dispatch('error', $msg, $closer);
  }

  /**
   * @param $msg
   * @param bool $closer
   * @return string
   */
  public static function info($msg, $closer=true) {
    return self::dispatch('info', $msg, $closer);
  }

  /**
   * @param $msg
   * @param bool $closer
   * @return string
   */
  public static function success($msg, $closer=true) {
    return self::dispatch('success', $msg, $closer);
  }

  /**
   * @param $msg
   * @param bool $closer
   * @return string
   */
  public static function warning($msg, $closer=true) {
    return self::dispatch('warning', $msg, $closer);
  }

}