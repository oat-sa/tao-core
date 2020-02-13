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

/* Do not edit */

use oat\tao\model\iconBuilder\IconBuilderTrait;

/**
 * Icon helper for tao - helpers/class.Icon.php
 *
 * Icons
 *
 * @access     public
 * @author     Dieter Raber, <dieter@taotesting.com>
 * @date       2020-02-04 12:46:07
 * @package    tao
 * @subpackage helpers
 */
class tao_helpers_Icon
{

    use IconBuilderTrait;

    /**
     * List of all icons as constant
     */
    const CLASS_OFFLINE = 'icon-offline';
    const CLASS_ONLINE = 'icon-online';
    const CLASS_TAB = 'icon-tab';
    const CLASS_UNTAB = 'icon-untab';
    const CLASS_MULTI_SELECT = 'icon-multi-select';
    const CLASS_CLIPBOARD = 'icon-clipboard';
    const CLASS_FILEBOX = 'icon-filebox';
    const CLASS_CLICK_TO_SPEAK = 'icon-click-to-speak';
    const CLASS_SPEECH_BUBBLE = 'icon-speech-bubble';
    const CLASS_MICROPHONE = 'icon-microphone';
    const CLASS_MICROPHONE_OFF = 'icon-microphone-off';
    const CLASS_DISCONNECT = 'icon-disconnect';
    const CLASS_CONNECT = 'icon-connect';
    const CLASS_ELIMINATE = 'icon-eliminate';
    const CLASS_WHEELCHAIR = 'icon-wheelchair';
    const CLASS_TEXT_MARKER = 'icon-text-marker';
    const CLASS_UNSHIELD = 'icon-unshield';
    const CLASS_SHIELD = 'icon-shield';
    const CLASS_TREE = 'icon-tree';
    const CLASS_HOME = 'icon-home';
    const CLASS_SHARED_FILE = 'icon-shared-file';
    const CLASS_END_ATTEMPT = 'icon-end-attempt';
    const CLASS_ICON = 'icon-icon';
    const CLASS_RADIO_BG = 'icon-radio-bg';
    const CLASS_CHECKBOX_BG = 'icon-checkbox-bg';
    const CLASS_TAG = 'icon-tag';
    const CLASS_STYLE = 'icon-style';
    const CLASS_OWNERSHIP_TRANSFER = 'icon-ownership-transfer';
    const CLASS_PROPERTY_ADVANCED = 'icon-property-advanced';
    const CLASS_PROPERTY_ADD = 'icon-property-add';
    const CLASS_REPOSITORY_ADD = 'icon-repository-add';
    const CLASS_REPOSITORY_REMOVE = 'icon-repository-remove';
    const CLASS_REPOSITORY = 'icon-repository';
    const CLASS_RESULT_SERVER = 'icon-result-server';
    const CLASS_FOLDER = 'icon-folder';
    const CLASS_FOLDER_OPEN = 'icon-folder-open';
    const CLASS_LEFT = 'icon-left';
    const CLASS_RIGHT = 'icon-right';
    const CLASS_UP = 'icon-up';
    const CLASS_DOWN = 'icon-down';
    const CLASS_UNDO = 'icon-undo';
    const CLASS_REDO = 'icon-redo';
    const CLASS_SCREEN = 'icon-screen';
    const CLASS_LAPTOP = 'icon-laptop';
    const CLASS_TABLET = 'icon-tablet';
    const CLASS_PHONE = 'icon-phone';
    const CLASS_MOVE = 'icon-move';
    const CLASS_BIN = 'icon-bin';
    const CLASS_SHUFFLE = 'icon-shuffle';
    const CLASS_PRINT = 'icon-print';
    const CLASS_TOOLS = 'icon-tools';
    const CLASS_SETTINGS = 'icon-settings';
    const CLASS_VIDEO = 'icon-video';
    const CLASS_FIND = 'icon-find';
    const CLASS_IMAGE = 'icon-image';
    const CLASS_EDIT = 'icon-edit';
    const CLASS_DOCUMENT = 'icon-document';
    const CLASS_RESIZE_GRID = 'icon-resize-grid';
    const CLASS_RESIZE = 'icon-resize';
    const CLASS_HELP = 'icon-help';
    const CLASS_MOBILE_MENU = 'icon-mobile-menu';
    const CLASS_FIX = 'icon-fix';
    const CLASS_UNLOCK = 'icon-unlock';
    const CLASS_LOCK = 'icon-lock';
    const CLASS_UL = 'icon-ul';
    const CLASS_OL = 'icon-ol';
    const CLASS_EMAIL = 'icon-email';
    const CLASS_DOWNLOAD = 'icon-download';
    const CLASS_LOGOUT = 'icon-logout';
    const CLASS_LOGIN = 'icon-login';
    const CLASS_SPINNER = 'icon-spinner';
    const CLASS_PREVIEW = 'icon-preview';
    const CLASS_EXTERNAL = 'icon-external';
    const CLASS_TIME = 'icon-time';
    const CLASS_SAVE = 'icon-save';
    const CLASS_WARNING = 'icon-warning';
    const CLASS_ADD = 'icon-add';
    const CLASS_ERROR = 'icon-error';
    const CLASS_CLOSE = 'icon-close';
    const CLASS_SUCCESS = 'icon-success';
    const CLASS_REMOVE = 'icon-remove';
    const CLASS_INFO = 'icon-info';
    const CLASS_DANGER = 'icon-danger';
    const CLASS_USERS = 'icon-users';
    const CLASS_USER = 'icon-user';
    const CLASS_TEST_TAKER = 'icon-test-taker';
    const CLASS_TEST_TAKERS = 'icon-test-takers';
    const CLASS_ITEM = 'icon-item';
    const CLASS_TEST = 'icon-test';
    const CLASS_DELIVERY = 'icon-delivery';
    const CLASS_EYE_SLASH = 'icon-eye-slash';
    const CLASS_RESULT = 'icon-result';
    const CLASS_DELIVERY_SMALL = 'icon-delivery-small';
    const CLASS_UPLOAD = 'icon-upload';
    const CLASS_RESULT_SMALL = 'icon-result-small';
    const CLASS_MOBILE_PREVIEW = 'icon-mobile-preview';
    const CLASS_EXTENSION = 'icon-extension';
    const CLASS_DESKTOP_PREVIEW = 'icon-desktop-preview';
    const CLASS_TABLET_PREVIEW = 'icon-tablet-preview';
    const CLASS_INSERT_HORIZONTAL_LINE = 'icon-insert-horizontal-line';
    const CLASS_TABLE = 'icon-table';
    const CLASS_ANCHOR = 'icon-anchor';
    const CLASS_UNLINK = 'icon-unlink';
    const CLASS_LINK = 'icon-link';
    const CLASS_RIGHT_LEFT = 'icon-right-left';
    const CLASS_LEFT_RIGHT = 'icon-left-right';
    const CLASS_SPECIAL_CHARACTER = 'icon-special-character';
    const CLASS_SOURCE = 'icon-source';
    const CLASS_NEW_PAGE = 'icon-new-page';
    const CLASS_TEMPLATES = 'icon-templates';
    const CLASS_CUT = 'icon-cut';
    const CLASS_REPLACE = 'icon-replace';
    const CLASS_COPY = 'icon-copy';
    const CLASS_PASTE = 'icon-paste';
    const CLASS_SELECT_ALL = 'icon-select-all';
    const CLASS_PASTE_TEXT = 'icon-paste-text';
    const CLASS_PASTE_WORD = 'icon-paste-word';
    const CLASS_BOLD = 'icon-bold';
    const CLASS_ITALIC = 'icon-italic';
    const CLASS_UNDERLINE = 'icon-underline';
    const CLASS_SUBSCRIPT = 'icon-subscript';
    const CLASS_SUPERSCRIPT = 'icon-superscript';
    const CLASS_STRIKE_THROUGH = 'icon-strike-through';
    const CLASS_DECREASE_INDENT = 'icon-decrease-indent';
    const CLASS_INCREASE_INDENT = 'icon-increase-indent';
    const CLASS_BLOCK_QUOTE = 'icon-block-quote';
    const CLASS_DIV_CONTAINER = 'icon-div-container';
    const CLASS_ALIGN_LEFT = 'icon-align-left';
    const CLASS_CENTER = 'icon-center';
    const CLASS_ALIGN_RIGHT = 'icon-align-right';
    const CLASS_JUSTIFY = 'icon-justify';
    const CLASS_CHOICE = 'icon-choice';
    const CLASS_INLINE_CHOICE = 'icon-inline-choice';
    const CLASS_MATCH = 'icon-match';
    const CLASS_ASSOCIATE = 'icon-associate';
    const CLASS_MEDIA = 'icon-media';
    const CLASS_GRAPHIC_ORDER = 'icon-graphic-order';
    const CLASS_HOTSPOT = 'icon-hotspot';
    const CLASS_GRAPHIC_GAP = 'icon-graphic-gap';
    const CLASS_GRAPHIC_ASSOCIATE = 'icon-graphic-associate';
    const CLASS_SELECT_POINT = 'icon-select-point';
    const CLASS_PIN = 'icon-pin';
    const CLASS_IMPORT = 'icon-import';
    const CLASS_EXPORT = 'icon-export';
    const CLASS_MOVE_ITEM = 'icon-move-item';
    const CLASS_META_DATA = 'icon-meta-data';
    const CLASS_SLIDER = 'icon-slider';
    const CLASS_SUMMARY_REPORT = 'icon-summary-report';
    const CLASS_TEXT_ENTRY = 'icon-text-entry';
    const CLASS_EXTENDED_TEXT = 'icon-extended-text';
    const CLASS_ERASER = 'icon-eraser';
    const CLASS_ROW = 'icon-row';
    const CLASS_COLUMN = 'icon-column';
    const CLASS_TEXT_COLOR = 'icon-text-color';
    const CLASS_BACKGROUND_COLOR = 'icon-background-color';
    const CLASS_SPELL_CHECK = 'icon-spell-check';
    const CLASS_POLYGON = 'icon-polygon';
    const CLASS_RECTANGLE = 'icon-rectangle';
    const CLASS_GAP_MATCH = 'icon-gap-match';
    const CLASS_ORDER = 'icon-order';
    const CLASS_HOTTEXT = 'icon-hottext';
    const CLASS_FREE_FORM = 'icon-free-form';
    const CLASS_STEP_BACKWARD = 'icon-step-backward';
    const CLASS_FAST_BACKWARD = 'icon-fast-backward';
    const CLASS_BACKWARD = 'icon-backward';
    const CLASS_PLAY = 'icon-play';
    const CLASS_PAUSE = 'icon-pause';
    const CLASS_STOP = 'icon-stop';
    const CLASS_FORWARD = 'icon-forward';
    const CLASS_FAST_FORWARD = 'icon-fast-forward';
    const CLASS_STEP_FORWARD = 'icon-step-forward';
    const CLASS_ELLIPSIS = 'icon-ellipsis';
    const CLASS_CIRCLE = 'icon-circle';
    const CLASS_TARGET = 'icon-target';
    const CLASS_GUIDE_ARROW = 'icon-guide-arrow';
    const CLASS_RANGE_SLIDER_RIGHT = 'icon-range-slider-right';
    const CLASS_RANGE_SLIDER_LEFT = 'icon-range-slider-left';
    const CLASS_RADIO_CHECKED = 'icon-radio-checked';
    const CLASS_CHECKBOX_INDETERMINATE = 'icon-checkbox-indeterminate';
    const CLASS_CHECKBOX = 'icon-checkbox';
    const CLASS_CHECKBOX_CROSSED = 'icon-checkbox-crossed';
    const CLASS_CHECKBOX_CHECKED = 'icon-checkbox-checked';
    const CLASS_RESULT_NOK = 'icon-result-nok';
    const CLASS_RESULT_OK = 'icon-result-ok';
    const CLASS_NOT_EVALUATED = 'icon-not-evaluated';
    const CLASS_FILTER = 'icon-filter';
    const CLASS_TRANSLATE = 'icon-translate';
    const CLASS_EJECT = 'icon-eject';
    const CLASS_CONTINUE = 'icon-continue';
    const CLASS_RADIO = 'icon-radio';
    const CLASS_SPHERE = 'icon-sphere';
    const CLASS_RESET = 'icon-reset';
    const CLASS_SMALLER = 'icon-smaller';
    const CLASS_LARGER = 'icon-larger';
    const CLASS_CLOCK = 'icon-clock';
    const CLASS_FONT = 'icon-font';
    const CLASS_MATHS = 'icon-maths';
    const CLASS_GRIP = 'icon-grip';
    const CLASS_RUBRIC = 'icon-rubric';
    const CLASS_AUDIO = 'icon-audio';
    const CLASS_GRIP_H = 'icon-grip-h';
    const CLASS_MAGICWAND = 'icon-magicwand';
    const CLASS_LOOP = 'icon-loop';
    const CLASS_CALENDAR = 'icon-calendar';
    const CLASS_RELOAD = 'icon-reload';
    const CLASS_SPEED = 'icon-speed';
    const CLASS_VOLUME = 'icon-volume';
    const CLASS_CONTRAST = 'icon-contrast';
    const CLASS_HEADPHONES = 'icon-headphones';
    const CLASS_COMPRESS = 'icon-compress';
    const CLASS_MAP_O = 'icon-map-o';
    const CLASS_VARIABLE = 'icon-variable';
    const CLASS_TOOLTIP = 'icon-tooltip';
    const CLASS_GLOBE = 'icon-globe';
    const CLASS_HIGHLIGHTER = 'icon-highlighter';
    const CLASS_ELIMINATE_CROSSED = 'icon-eliminate-crossed';
    const CLASS_PLAY_FROM_HERE = 'icon-play-from-here';

    /**
     * List of all icons as function
     */

    public static function iconOffline($options = [])
    {
        return self::buildIcon(self::CLASS_OFFLINE, $options);
    }

    public static function iconOnline($options = [])
    {
        return self::buildIcon(self::CLASS_ONLINE, $options);
    }

    public static function iconTab($options = [])
    {
        return self::buildIcon(self::CLASS_TAB, $options);
    }

    public static function iconUntab($options = [])
    {
        return self::buildIcon(self::CLASS_UNTAB, $options);
    }

    public static function iconMultiSelect($options = [])
    {
        return self::buildIcon(self::CLASS_MULTI_SELECT, $options);
    }

    public static function iconClipboard($options = [])
    {
        return self::buildIcon(self::CLASS_CLIPBOARD, $options);
    }

    public static function iconFilebox($options = [])
    {
        return self::buildIcon(self::CLASS_FILEBOX, $options);
    }

    public static function iconClickToSpeak($options = [])
    {
        return self::buildIcon(self::CLASS_CLICK_TO_SPEAK, $options);
    }

    public static function iconSpeechBubble($options = [])
    {
        return self::buildIcon(self::CLASS_SPEECH_BUBBLE, $options);
    }

    public static function iconMicrophone($options = [])
    {
        return self::buildIcon(self::CLASS_MICROPHONE, $options);
    }

    public static function iconMicrophoneOff($options = [])
    {
        return self::buildIcon(self::CLASS_MICROPHONE_OFF, $options);
    }

    public static function iconDisconnect($options = [])
    {
        return self::buildIcon(self::CLASS_DISCONNECT, $options);
    }

    public static function iconConnect($options = [])
    {
        return self::buildIcon(self::CLASS_CONNECT, $options);
    }

    public static function iconEliminate($options = [])
    {
        return self::buildIcon(self::CLASS_ELIMINATE, $options);
    }

    public static function iconWheelchair($options = [])
    {
        return self::buildIcon(self::CLASS_WHEELCHAIR, $options);
    }

    public static function iconTextMarker($options = [])
    {
        return self::buildIcon(self::CLASS_TEXT_MARKER, $options);
    }

    public static function iconUnshield($options = [])
    {
        return self::buildIcon(self::CLASS_UNSHIELD, $options);
    }

    public static function iconShield($options = [])
    {
        return self::buildIcon(self::CLASS_SHIELD, $options);
    }

    public static function iconTree($options = [])
    {
        return self::buildIcon(self::CLASS_TREE, $options);
    }

    public static function iconHome($options = [])
    {
        return self::buildIcon(self::CLASS_HOME, $options);
    }

    public static function iconSharedFile($options = [])
    {
        return self::buildIcon(self::CLASS_SHARED_FILE, $options);
    }

    public static function iconEndAttempt($options = [])
    {
        return self::buildIcon(self::CLASS_END_ATTEMPT, $options);
    }

    public static function iconIcon($options = [])
    {
        return self::buildIcon(self::CLASS_ICON, $options);
    }

    public static function iconRadioBg($options = [])
    {
        return self::buildIcon(self::CLASS_RADIO_BG, $options);
    }

    public static function iconCheckboxBg($options = [])
    {
        return self::buildIcon(self::CLASS_CHECKBOX_BG, $options);
    }

    public static function iconTag($options = [])
    {
        return self::buildIcon(self::CLASS_TAG, $options);
    }

    public static function iconStyle($options = [])
    {
        return self::buildIcon(self::CLASS_STYLE, $options);
    }

    public static function iconOwnershipTransfer($options = [])
    {
        return self::buildIcon(self::CLASS_OWNERSHIP_TRANSFER, $options);
    }

    public static function iconPropertyAdvanced($options = [])
    {
        return self::buildIcon(self::CLASS_PROPERTY_ADVANCED, $options);
    }

    public static function iconPropertyAdd($options = [])
    {
        return self::buildIcon(self::CLASS_PROPERTY_ADD, $options);
    }

    public static function iconRepositoryAdd($options = [])
    {
        return self::buildIcon(self::CLASS_REPOSITORY_ADD, $options);
    }

    public static function iconRepositoryRemove($options = [])
    {
        return self::buildIcon(self::CLASS_REPOSITORY_REMOVE, $options);
    }

    public static function iconRepository($options = [])
    {
        return self::buildIcon(self::CLASS_REPOSITORY, $options);
    }

    public static function iconResultServer($options = [])
    {
        return self::buildIcon(self::CLASS_RESULT_SERVER, $options);
    }

    public static function iconFolder($options = [])
    {
        return self::buildIcon(self::CLASS_FOLDER, $options);
    }

    public static function iconFolderOpen($options = [])
    {
        return self::buildIcon(self::CLASS_FOLDER_OPEN, $options);
    }

    public static function iconLeft($options = [])
    {
        return self::buildIcon(self::CLASS_LEFT, $options);
    }

    public static function iconRight($options = [])
    {
        return self::buildIcon(self::CLASS_RIGHT, $options);
    }

    public static function iconUp($options = [])
    {
        return self::buildIcon(self::CLASS_UP, $options);
    }

    public static function iconDown($options = [])
    {
        return self::buildIcon(self::CLASS_DOWN, $options);
    }

    public static function iconUndo($options = [])
    {
        return self::buildIcon(self::CLASS_UNDO, $options);
    }

    public static function iconRedo($options = [])
    {
        return self::buildIcon(self::CLASS_REDO, $options);
    }

    public static function iconScreen($options = [])
    {
        return self::buildIcon(self::CLASS_SCREEN, $options);
    }

    public static function iconLaptop($options = [])
    {
        return self::buildIcon(self::CLASS_LAPTOP, $options);
    }

    public static function iconTablet($options = [])
    {
        return self::buildIcon(self::CLASS_TABLET, $options);
    }

    public static function iconPhone($options = [])
    {
        return self::buildIcon(self::CLASS_PHONE, $options);
    }

    public static function iconMove($options = [])
    {
        return self::buildIcon(self::CLASS_MOVE, $options);
    }

    public static function iconBin($options = [])
    {
        return self::buildIcon(self::CLASS_BIN, $options);
    }

    public static function iconShuffle($options = [])
    {
        return self::buildIcon(self::CLASS_SHUFFLE, $options);
    }

    public static function iconPrint($options = [])
    {
        return self::buildIcon(self::CLASS_PRINT, $options);
    }

    public static function iconTools($options = [])
    {
        return self::buildIcon(self::CLASS_TOOLS, $options);
    }

    public static function iconSettings($options = [])
    {
        return self::buildIcon(self::CLASS_SETTINGS, $options);
    }

    public static function iconVideo($options = [])
    {
        return self::buildIcon(self::CLASS_VIDEO, $options);
    }

    public static function iconFind($options = [])
    {
        return self::buildIcon(self::CLASS_FIND, $options);
    }

    public static function iconImage($options = [])
    {
        return self::buildIcon(self::CLASS_IMAGE, $options);
    }

    public static function iconEdit($options = [])
    {
        return self::buildIcon(self::CLASS_EDIT, $options);
    }

    public static function iconDocument($options = [])
    {
        return self::buildIcon(self::CLASS_DOCUMENT, $options);
    }

    public static function iconResizeGrid($options = [])
    {
        return self::buildIcon(self::CLASS_RESIZE_GRID, $options);
    }

    public static function iconResize($options = [])
    {
        return self::buildIcon(self::CLASS_RESIZE, $options);
    }

    public static function iconHelp($options = [])
    {
        return self::buildIcon(self::CLASS_HELP, $options);
    }

    public static function iconMobileMenu($options = [])
    {
        return self::buildIcon(self::CLASS_MOBILE_MENU, $options);
    }

    public static function iconFix($options = [])
    {
        return self::buildIcon(self::CLASS_FIX, $options);
    }

    public static function iconUnlock($options = [])
    {
        return self::buildIcon(self::CLASS_UNLOCK, $options);
    }

    public static function iconLock($options = [])
    {
        return self::buildIcon(self::CLASS_LOCK, $options);
    }

    public static function iconUl($options = [])
    {
        return self::buildIcon(self::CLASS_UL, $options);
    }

    public static function iconOl($options = [])
    {
        return self::buildIcon(self::CLASS_OL, $options);
    }

    public static function iconEmail($options = [])
    {
        return self::buildIcon(self::CLASS_EMAIL, $options);
    }

    public static function iconDownload($options = [])
    {
        return self::buildIcon(self::CLASS_DOWNLOAD, $options);
    }

    public static function iconLogout($options = [])
    {
        return self::buildIcon(self::CLASS_LOGOUT, $options);
    }

    public static function iconLogin($options = [])
    {
        return self::buildIcon(self::CLASS_LOGIN, $options);
    }

    public static function iconSpinner($options = [])
    {
        return self::buildIcon(self::CLASS_SPINNER, $options);
    }

    public static function iconPreview($options = [])
    {
        return self::buildIcon(self::CLASS_PREVIEW, $options);
    }

    public static function iconExternal($options = [])
    {
        return self::buildIcon(self::CLASS_EXTERNAL, $options);
    }

    public static function iconTime($options = [])
    {
        return self::buildIcon(self::CLASS_TIME, $options);
    }

    public static function iconSave($options = [])
    {
        return self::buildIcon(self::CLASS_SAVE, $options);
    }

    public static function iconWarning($options = [])
    {
        return self::buildIcon(self::CLASS_WARNING, $options);
    }

    public static function iconAdd($options = [])
    {
        return self::buildIcon(self::CLASS_ADD, $options);
    }

    public static function iconError($options = [])
    {
        return self::buildIcon(self::CLASS_ERROR, $options);
    }

    public static function iconClose($options = [])
    {
        return self::buildIcon(self::CLASS_CLOSE, $options);
    }

    public static function iconSuccess($options = [])
    {
        return self::buildIcon(self::CLASS_SUCCESS, $options);
    }

    public static function iconRemove($options = [])
    {
        return self::buildIcon(self::CLASS_REMOVE, $options);
    }

    public static function iconInfo($options = [])
    {
        return self::buildIcon(self::CLASS_INFO, $options);
    }

    public static function iconDanger($options = [])
    {
        return self::buildIcon(self::CLASS_DANGER, $options);
    }

    public static function iconUsers($options = [])
    {
        return self::buildIcon(self::CLASS_USERS, $options);
    }

    public static function iconUser($options = [])
    {
        return self::buildIcon(self::CLASS_USER, $options);
    }

    public static function iconTestTaker($options = [])
    {
        return self::buildIcon(self::CLASS_TEST_TAKER, $options);
    }

    public static function iconTestTakers($options = [])
    {
        return self::buildIcon(self::CLASS_TEST_TAKERS, $options);
    }

    public static function iconItem($options = [])
    {
        return self::buildIcon(self::CLASS_ITEM, $options);
    }

    public static function iconTest($options = [])
    {
        return self::buildIcon(self::CLASS_TEST, $options);
    }

    public static function iconDelivery($options = [])
    {
        return self::buildIcon(self::CLASS_DELIVERY, $options);
    }

    public static function iconEyeSlash($options = [])
    {
        return self::buildIcon(self::CLASS_EYE_SLASH, $options);
    }

    public static function iconResult($options = [])
    {
        return self::buildIcon(self::CLASS_RESULT, $options);
    }

    public static function iconDeliverySmall($options = [])
    {
        return self::buildIcon(self::CLASS_DELIVERY_SMALL, $options);
    }

    public static function iconUpload($options = [])
    {
        return self::buildIcon(self::CLASS_UPLOAD, $options);
    }

    public static function iconResultSmall($options = [])
    {
        return self::buildIcon(self::CLASS_RESULT_SMALL, $options);
    }

    public static function iconMobilePreview($options = [])
    {
        return self::buildIcon(self::CLASS_MOBILE_PREVIEW, $options);
    }

    public static function iconExtension($options = [])
    {
        return self::buildIcon(self::CLASS_EXTENSION, $options);
    }

    public static function iconDesktopPreview($options = [])
    {
        return self::buildIcon(self::CLASS_DESKTOP_PREVIEW, $options);
    }

    public static function iconTabletPreview($options = [])
    {
        return self::buildIcon(self::CLASS_TABLET_PREVIEW, $options);
    }

    public static function iconInsertHorizontalLine($options = [])
    {
        return self::buildIcon(self::CLASS_INSERT_HORIZONTAL_LINE, $options);
    }

    public static function iconTable($options = [])
    {
        return self::buildIcon(self::CLASS_TABLE, $options);
    }

    public static function iconAnchor($options = [])
    {
        return self::buildIcon(self::CLASS_ANCHOR, $options);
    }

    public static function iconUnlink($options = [])
    {
        return self::buildIcon(self::CLASS_UNLINK, $options);
    }

    public static function iconLink($options = [])
    {
        return self::buildIcon(self::CLASS_LINK, $options);
    }

    public static function iconRightLeft($options = [])
    {
        return self::buildIcon(self::CLASS_RIGHT_LEFT, $options);
    }

    public static function iconLeftRight($options = [])
    {
        return self::buildIcon(self::CLASS_LEFT_RIGHT, $options);
    }

    public static function iconSpecialCharacter($options = [])
    {
        return self::buildIcon(self::CLASS_SPECIAL_CHARACTER, $options);
    }

    public static function iconSource($options = [])
    {
        return self::buildIcon(self::CLASS_SOURCE, $options);
    }

    public static function iconNewPage($options = [])
    {
        return self::buildIcon(self::CLASS_NEW_PAGE, $options);
    }

    public static function iconTemplates($options = [])
    {
        return self::buildIcon(self::CLASS_TEMPLATES, $options);
    }

    public static function iconCut($options = [])
    {
        return self::buildIcon(self::CLASS_CUT, $options);
    }

    public static function iconReplace($options = [])
    {
        return self::buildIcon(self::CLASS_REPLACE, $options);
    }

    public static function iconCopy($options = [])
    {
        return self::buildIcon(self::CLASS_COPY, $options);
    }

    public static function iconPaste($options = [])
    {
        return self::buildIcon(self::CLASS_PASTE, $options);
    }

    public static function iconSelectAll($options = [])
    {
        return self::buildIcon(self::CLASS_SELECT_ALL, $options);
    }

    public static function iconPasteText($options = [])
    {
        return self::buildIcon(self::CLASS_PASTE_TEXT, $options);
    }

    public static function iconPasteWord($options = [])
    {
        return self::buildIcon(self::CLASS_PASTE_WORD, $options);
    }

    public static function iconBold($options = [])
    {
        return self::buildIcon(self::CLASS_BOLD, $options);
    }

    public static function iconItalic($options = [])
    {
        return self::buildIcon(self::CLASS_ITALIC, $options);
    }

    public static function iconUnderline($options = [])
    {
        return self::buildIcon(self::CLASS_UNDERLINE, $options);
    }

    public static function iconSubscript($options = [])
    {
        return self::buildIcon(self::CLASS_SUBSCRIPT, $options);
    }

    public static function iconSuperscript($options = [])
    {
        return self::buildIcon(self::CLASS_SUPERSCRIPT, $options);
    }

    public static function iconStrikeThrough($options = [])
    {
        return self::buildIcon(self::CLASS_STRIKE_THROUGH, $options);
    }

    public static function iconDecreaseIndent($options = [])
    {
        return self::buildIcon(self::CLASS_DECREASE_INDENT, $options);
    }

    public static function iconIncreaseIndent($options = [])
    {
        return self::buildIcon(self::CLASS_INCREASE_INDENT, $options);
    }

    public static function iconBlockQuote($options = [])
    {
        return self::buildIcon(self::CLASS_BLOCK_QUOTE, $options);
    }

    public static function iconDivContainer($options = [])
    {
        return self::buildIcon(self::CLASS_DIV_CONTAINER, $options);
    }

    public static function iconAlignLeft($options = [])
    {
        return self::buildIcon(self::CLASS_ALIGN_LEFT, $options);
    }

    public static function iconCenter($options = [])
    {
        return self::buildIcon(self::CLASS_CENTER, $options);
    }

    public static function iconAlignRight($options = [])
    {
        return self::buildIcon(self::CLASS_ALIGN_RIGHT, $options);
    }

    public static function iconJustify($options = [])
    {
        return self::buildIcon(self::CLASS_JUSTIFY, $options);
    }

    public static function iconChoice($options = [])
    {
        return self::buildIcon(self::CLASS_CHOICE, $options);
    }

    public static function iconInlineChoice($options = [])
    {
        return self::buildIcon(self::CLASS_INLINE_CHOICE, $options);
    }

    public static function iconMatch($options = [])
    {
        return self::buildIcon(self::CLASS_MATCH, $options);
    }

    public static function iconAssociate($options = [])
    {
        return self::buildIcon(self::CLASS_ASSOCIATE, $options);
    }

    public static function iconMedia($options = [])
    {
        return self::buildIcon(self::CLASS_MEDIA, $options);
    }

    public static function iconGraphicOrder($options = [])
    {
        return self::buildIcon(self::CLASS_GRAPHIC_ORDER, $options);
    }

    public static function iconHotspot($options = [])
    {
        return self::buildIcon(self::CLASS_HOTSPOT, $options);
    }

    public static function iconGraphicGap($options = [])
    {
        return self::buildIcon(self::CLASS_GRAPHIC_GAP, $options);
    }

    public static function iconGraphicAssociate($options = [])
    {
        return self::buildIcon(self::CLASS_GRAPHIC_ASSOCIATE, $options);
    }

    public static function iconSelectPoint($options = [])
    {
        return self::buildIcon(self::CLASS_SELECT_POINT, $options);
    }

    public static function iconPin($options = [])
    {
        return self::buildIcon(self::CLASS_PIN, $options);
    }

    public static function iconImport($options = [])
    {
        return self::buildIcon(self::CLASS_IMPORT, $options);
    }

    public static function iconExport($options = [])
    {
        return self::buildIcon(self::CLASS_EXPORT, $options);
    }

    public static function iconMoveItem($options = [])
    {
        return self::buildIcon(self::CLASS_MOVE_ITEM, $options);
    }

    public static function iconMetaData($options = [])
    {
        return self::buildIcon(self::CLASS_META_DATA, $options);
    }

    public static function iconSlider($options = [])
    {
        return self::buildIcon(self::CLASS_SLIDER, $options);
    }

    public static function iconSummaryReport($options = [])
    {
        return self::buildIcon(self::CLASS_SUMMARY_REPORT, $options);
    }

    public static function iconTextEntry($options = [])
    {
        return self::buildIcon(self::CLASS_TEXT_ENTRY, $options);
    }

    public static function iconExtendedText($options = [])
    {
        return self::buildIcon(self::CLASS_EXTENDED_TEXT, $options);
    }

    public static function iconEraser($options = [])
    {
        return self::buildIcon(self::CLASS_ERASER, $options);
    }

    public static function iconRow($options = [])
    {
        return self::buildIcon(self::CLASS_ROW, $options);
    }

    public static function iconColumn($options = [])
    {
        return self::buildIcon(self::CLASS_COLUMN, $options);
    }

    public static function iconTextColor($options = [])
    {
        return self::buildIcon(self::CLASS_TEXT_COLOR, $options);
    }

    public static function iconBackgroundColor($options = [])
    {
        return self::buildIcon(self::CLASS_BACKGROUND_COLOR, $options);
    }

    public static function iconSpellCheck($options = [])
    {
        return self::buildIcon(self::CLASS_SPELL_CHECK, $options);
    }

    public static function iconPolygon($options = [])
    {
        return self::buildIcon(self::CLASS_POLYGON, $options);
    }

    public static function iconRectangle($options = [])
    {
        return self::buildIcon(self::CLASS_RECTANGLE, $options);
    }

    public static function iconGapMatch($options = [])
    {
        return self::buildIcon(self::CLASS_GAP_MATCH, $options);
    }

    public static function iconOrder($options = [])
    {
        return self::buildIcon(self::CLASS_ORDER, $options);
    }

    public static function iconHottext($options = [])
    {
        return self::buildIcon(self::CLASS_HOTTEXT, $options);
    }

    public static function iconFreeForm($options = [])
    {
        return self::buildIcon(self::CLASS_FREE_FORM, $options);
    }

    public static function iconStepBackward($options = [])
    {
        return self::buildIcon(self::CLASS_STEP_BACKWARD, $options);
    }

    public static function iconFastBackward($options = [])
    {
        return self::buildIcon(self::CLASS_FAST_BACKWARD, $options);
    }

    public static function iconBackward($options = [])
    {
        return self::buildIcon(self::CLASS_BACKWARD, $options);
    }

    public static function iconPlay($options = [])
    {
        return self::buildIcon(self::CLASS_PLAY, $options);
    }

    public static function iconPause($options = [])
    {
        return self::buildIcon(self::CLASS_PAUSE, $options);
    }

    public static function iconStop($options = [])
    {
        return self::buildIcon(self::CLASS_STOP, $options);
    }

    public static function iconForward($options = [])
    {
        return self::buildIcon(self::CLASS_FORWARD, $options);
    }

    public static function iconFastForward($options = [])
    {
        return self::buildIcon(self::CLASS_FAST_FORWARD, $options);
    }

    public static function iconStepForward($options = [])
    {
        return self::buildIcon(self::CLASS_STEP_FORWARD, $options);
    }

    public static function iconEllipsis($options = [])
    {
        return self::buildIcon(self::CLASS_ELLIPSIS, $options);
    }

    public static function iconCircle($options = [])
    {
        return self::buildIcon(self::CLASS_CIRCLE, $options);
    }

    public static function iconTarget($options = [])
    {
        return self::buildIcon(self::CLASS_TARGET, $options);
    }

    public static function iconGuideArrow($options = [])
    {
        return self::buildIcon(self::CLASS_GUIDE_ARROW, $options);
    }

    public static function iconRangeSliderRight($options = [])
    {
        return self::buildIcon(self::CLASS_RANGE_SLIDER_RIGHT, $options);
    }

    public static function iconRangeSliderLeft($options = [])
    {
        return self::buildIcon(self::CLASS_RANGE_SLIDER_LEFT, $options);
    }

    public static function iconRadioChecked($options = [])
    {
        return self::buildIcon(self::CLASS_RADIO_CHECKED, $options);
    }

    public static function iconCheckboxIndeterminate($options = [])
    {
        return self::buildIcon(self::CLASS_CHECKBOX_INDETERMINATE, $options);
    }

    public static function iconCheckbox($options = [])
    {
        return self::buildIcon(self::CLASS_CHECKBOX, $options);
    }

    public static function iconCheckboxCrossed($options = [])
    {
        return self::buildIcon(self::CLASS_CHECKBOX_CROSSED, $options);
    }

    public static function iconCheckboxChecked($options = [])
    {
        return self::buildIcon(self::CLASS_CHECKBOX_CHECKED, $options);
    }

    public static function iconResultNok($options = [])
    {
        return self::buildIcon(self::CLASS_RESULT_NOK, $options);
    }

    public static function iconResultOk($options = [])
    {
        return self::buildIcon(self::CLASS_RESULT_OK, $options);
    }

    public static function iconNotEvaluated($options = [])
    {
        return self::buildIcon(self::CLASS_NOT_EVALUATED, $options);
    }

    public static function iconFilter($options = [])
    {
        return self::buildIcon(self::CLASS_FILTER, $options);
    }

    public static function iconTranslate($options = [])
    {
        return self::buildIcon(self::CLASS_TRANSLATE, $options);
    }

    public static function iconEject($options = [])
    {
        return self::buildIcon(self::CLASS_EJECT, $options);
    }

    public static function iconContinue($options = [])
    {
        return self::buildIcon(self::CLASS_CONTINUE, $options);
    }

    public static function iconRadio($options = [])
    {
        return self::buildIcon(self::CLASS_RADIO, $options);
    }

    public static function iconSphere($options = [])
    {
        return self::buildIcon(self::CLASS_SPHERE, $options);
    }

    public static function iconReset($options = [])
    {
        return self::buildIcon(self::CLASS_RESET, $options);
    }

    public static function iconSmaller($options = [])
    {
        return self::buildIcon(self::CLASS_SMALLER, $options);
    }

    public static function iconLarger($options = [])
    {
        return self::buildIcon(self::CLASS_LARGER, $options);
    }

    public static function iconClock($options = [])
    {
        return self::buildIcon(self::CLASS_CLOCK, $options);
    }

    public static function iconFont($options = [])
    {
        return self::buildIcon(self::CLASS_FONT, $options);
    }

    public static function iconMaths($options = [])
    {
        return self::buildIcon(self::CLASS_MATHS, $options);
    }

    public static function iconGrip($options = [])
    {
        return self::buildIcon(self::CLASS_GRIP, $options);
    }

    public static function iconRubric($options = [])
    {
        return self::buildIcon(self::CLASS_RUBRIC, $options);
    }

    public static function iconAudio($options = [])
    {
        return self::buildIcon(self::CLASS_AUDIO, $options);
    }

    public static function iconGripH($options = [])
    {
        return self::buildIcon(self::CLASS_GRIP_H, $options);
    }

    public static function iconMagicwand($options = [])
    {
        return self::buildIcon(self::CLASS_MAGICWAND, $options);
    }

    public static function iconLoop($options = [])
    {
        return self::buildIcon(self::CLASS_LOOP, $options);
    }

    public static function iconCalendar($options = [])
    {
        return self::buildIcon(self::CLASS_CALENDAR, $options);
    }

    public static function iconReload($options = [])
    {
        return self::buildIcon(self::CLASS_RELOAD, $options);
    }

    public static function iconSpeed($options = [])
    {
        return self::buildIcon(self::CLASS_SPEED, $options);
    }

    public static function iconVolume($options = [])
    {
        return self::buildIcon(self::CLASS_VOLUME, $options);
    }

    public static function iconContrast($options = [])
    {
        return self::buildIcon(self::CLASS_CONTRAST, $options);
    }

    public static function iconHeadphones($options = [])
    {
        return self::buildIcon(self::CLASS_HEADPHONES, $options);
    }

    public static function iconCompress($options = [])
    {
        return self::buildIcon(self::CLASS_COMPRESS, $options);
    }

    public static function iconMapO($options = [])
    {
        return self::buildIcon(self::CLASS_MAP_O, $options);
    }

    public static function iconVariable($options = [])
    {
        return self::buildIcon(self::CLASS_VARIABLE, $options);
    }

    public static function iconTooltip($options = [])
    {
        return self::buildIcon(self::CLASS_TOOLTIP, $options);
    }

    public static function iconGlobe($options = [])
    {
        return self::buildIcon(self::CLASS_GLOBE, $options);
    }

    public static function iconHighlighter($options = [])
    {
        return self::buildIcon(self::CLASS_HIGHLIGHTER, $options);
    }

    public static function iconEliminateCrossed($options = [])
    {
        return self::buildIcon(self::CLASS_ELIMINATE_CROSSED, $options);
    }

    public static function iconPlayFromHere($options = [])
    {
        return self::buildIcon(self::CLASS_PLAY_FROM_HERE, $options);
    }
}
