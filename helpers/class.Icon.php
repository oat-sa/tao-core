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
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 * 
 */

/**
 * Icon helper for tao - helpers/class.Icon.php
 *
 * Icons
 *
 * @access public
 * @author Dieter Raber, <dieter@taotesting.com>
 * @package tao
 * @subpackage helpers
 */
class tao_helpers_Icon {
	
    const ICON_CHECKBOX_CHECKED = 'icon-checkbox-checked';
    const ICON_CHECKBOX = 'icon-checkbox';
    const ICON_LEFT = 'icon-left';
    const ICON_RIGHT = 'icon-right';
    const ICON_UP = 'icon-up';
    const ICON_DOWN = 'icon-down';
    const ICON_UNDO = 'icon-undo';
    const ICON_REDO = 'icon-redo';
    const ICON_SCREEN = 'icon-screen';
    const ICON_LAPTOP = 'icon-laptop';
    const ICON_TABLET = 'icon-tablet';
    const ICON_PHONE = 'icon-phone';
    const ICON_FOLDER = 'icon-folder';
    const ICON_FOLDER_OPEN = 'icon-folder-open';
    const ICON_MOVE = 'icon-move';
    const ICON_BIN = 'icon-bin';
    const ICON_SHUFFLE = 'icon-shuffle';
    const ICON_PRINT = 'icon-print';
    const ICON_TOOLS = 'icon-tools';
    const ICON_SETTINGS = 'icon-settings';
    const ICON_VIDEO = 'icon-video';
    const ICON_FIND = 'icon-find';
    const ICON_IMAGE = 'icon-image';
    const ICON_EDIT = 'icon-edit';
    const ICON_DOCUMENT = 'icon-document';
    const ICON_RESIZE_GRID = 'icon-resize-grid';
    const ICON_RESIZE = 'icon-resize';
    const ICON_HELP = 'icon-help';
    const ICON_MOBILE_MENU = 'icon-mobile-menu';
    const ICON_FIX = 'icon-fix';
    const ICON_UNLOCK = 'icon-unlock';
    const ICON_LOCK = 'icon-lock';
    const ICON_UL = 'icon-ul';
    const ICON_OL = 'icon-ol';
    const ICON_EMAIL = 'icon-email';
    const ICON_RADIO_CHECKED = 'icon-radio-checked';
    const ICON_DOWNLOAD = 'icon-download';
    const ICON_LOGOUT = 'icon-logout';
    const ICON_LOGIN = 'icon-login';
    const ICON_CHECKBOX_CROSSED = 'icon-checkbox-crossed';
    const ICON_SPINNER = 'icon-spinner';
    const ICON_PREVIEW = 'icon-preview';
    const ICON_EXTERNAL = 'icon-external';
    const ICON_TIME = 'icon-time';
    const ICON_RADIO = 'icon-radio';
    const ICON_SAVE = 'icon-save';
    const ICON_WARNING = 'icon-warning';
    const ICON_ADD = 'icon-add';
    const ICON_CLOSE = 'icon-close';
    const ICON_SUCCESS = 'icon-success';
    const ICON_REMOVE = 'icon-remove';
    const ICON_INFO = 'icon-info';
    const ICON_ERROR = 'icon-error';
    const ICON_USERS = 'icon-users';
    const ICON_USER = 'icon-user';
    const ICON_TEST_TAKER = 'icon-test-taker';
    const ICON_TEST_TAKERS = 'icon-test-takers';
    const ICON_ITEM = 'icon-item';
    const ICON_TEST = 'icon-test';
    const ICON_DELIVERY = 'icon-delivery';
    const ICON_RESULT = 'icon-result';
    const ICON_DELIVERY_SMALL = 'icon-delivery-small';
    const ICON_RESULT_SMALL = 'icon-result-small';
    const ICON_PREVIEW3 = 'icon-preview3';
    const ICON_MOBILE_PREVIEW = 'icon-mobile-preview';
    const ICON_EXTENSION = 'icon-extension';
    const ICON_DESKTOP_PREVIEW = 'icon-desktop-preview';
    const ICON_TABLET_PREVIEW = 'icon-tablet-preview';
    const ICON_INSERT_HORIZONTAL_LINE = 'icon-insert-horizontal-line';
    const ICON_TABLE = 'icon-table';
    const ICON_ANCHOR = 'icon-anchor';
    const ICON_UNLINK = 'icon-unlink';
    const ICON_LINK = 'icon-link';
    const ICON_RIGHT_LEFT = 'icon-right-left';
    const ICON_LEFT_RIGHT = 'icon-left-right';
    const ICON_SPECIAL_CHARACTER = 'icon-special-character';
    const ICON_SOURCE = 'icon-source';
    const ICON_NEW_PAGE = 'icon-new-page';
    const ICON_TEXT_COLOR = 'icon-text-color';
    const ICON_BACKGROUND_COLOR = 'icon-background-color';
    const ICON_TEMPLATES = 'icon-templates';
    const ICON_CUT = 'icon-cut';
    const ICON_SPELL_CHECK = 'icon-spell-check';
    const ICON_REPLACE = 'icon-replace';
    const ICON_COPY = 'icon-copy';
    const ICON_PASTE = 'icon-paste';
    const ICON_SELECT_ALL = 'icon-select-all';
    const ICON_PASTE_TEXT = 'icon-paste-text';
    const ICON_PASTE_WORD = 'icon-paste-word';
    const ICON_BOLD = 'icon-bold';
    const ICON_ITALIC = 'icon-italic';
    const ICON_UNDERLINE = 'icon-underline';
    const ICON_SUBSCRIPT = 'icon-subscript';
    const ICON_SUPERSCRIPT = 'icon-superscript';
    const ICON_STRIKE_THROUGH = 'icon-strike-through';
    const ICON_DECREASE_INDENT = 'icon-decrease-indent';
    const ICON_INCREASE_INDENT = 'icon-increase-indent';
    const ICON_BLOCK_QUOTE = 'icon-block-quote';
    const ICON_DIV_CONTAINER = 'icon-div-container';
    const ICON_ALIGN_LEFT = 'icon-align-left';
    const ICON_CENTER = 'icon-center';
    const ICON_ALIGN_RIGHT = 'icon-align-right';
    const ICON_JUSTIFY = 'icon-justify';
    const ICON_CHOICE_IA = 'icon-choice-ia';
    const ICON_INLINE_CHOICE_IA = 'icon-inline-choice-ia';
    const ICON_MATCH_IA = 'icon-match-ia';
    const ICON_ASSOCIATE_IA = 'icon-associate-ia';
    const ICON_MEDIA_IA = 'icon-media-ia';
    const ICON_GRAPHIC_ORDER_IA = 'icon-graphic-order-ia';
    const ICON_HOTSPOT_IA = 'icon-hotspot-ia';
    const ICON_GRAPHIC_GAP_IA = 'icon-graphic-gap-ia';
    const ICON_GRAPHIC_ASSOCIATE_IA = 'icon-graphic-associate-ia';
    const ICON_SELECT_POINT_IA = 'icon-select-point-ia';
    const ICON_ADD_COLUMN = 'icon-add-column';
    const ICON_ADD_ROW = 'icon-add-row';
    const ICON_PIN = 'icon-pin';
    const ICON_IMPORT = 'icon-import';
    const ICON_EXPORT = 'icon-export';
    const ICON_MOVE_ITEM = 'icon-move-item';
    const ICON_META_DATA = 'icon-meta-data';
    const ICON_SLIDER_IA = 'icon-slider-ia';
    const ICON_HOTTEXT_IA = 'icon-hottext-ia';
    const ICON_SUMMARY_REPORT = 'icon-summary-report';

	

    /**
     * @param $name of the icon as camelCase without prefix 'icon-'
     * @param $arguments currently unused
     * @return string span element with icon
     */
    public static function __callStatic($name, $arguments){
        $constant = strtoupper(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $name));
        $icon = constant('self::' . $constant);
        return '<span class="' . $icon . '"></span>';
    }
}