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

/**
 * Icon helper for tao - helpers/class.Icon.php
 *
 * Icons
 *
 * @access public
 * @author Dieter Raber, <dieter@taotesting.com>
 * @date   2019-04-26 10:09:50
 * @package tao
 * @subpackage helpers
 */
class tao_helpers_Icon
{
    /**
     * This function builds the actual HTML element and is used by all other functions.
     * The doc for $options is the applicable for all other functions.
     *
     * @param string $icon name of the icon to display
     * @param array $options (optional) hashtable with HTML attributes, also allows to set element="almostAnyHtmlElement"
     * @param string HTML element with icon
     */
    protected static function buildIcon($icon, $options = array())
    {
        $options['class'] = !empty($options['class']) ? $options['class'] . ' ' . $icon : $icon;
        $element = !empty($options['element']) ? $options['element'] : 'span';
        unset($options['element']);
        $retVal = '<' . $element . ' ';
        foreach ($options as $key => $value) {
            $retVal .= $key . '="' . $value . '"';
        }
        $retVal .= '></' . $element . '>';
        return $retVal;
    }
    
    /**
     * List of all icons as constant
     */
    public const CLASS_TAB = 'icon-tab';
    public const CLASS_UNTAB = 'icon-untab';
    public const CLASS_MULTI_SELECT = 'icon-multi-select';
    public const CLASS_CLIPBOARD = 'icon-clipboard';
    public const CLASS_FILEBOX = 'icon-filebox';
    public const CLASS_CLICK_TO_SPEAK = 'icon-click-to-speak';
    public const CLASS_SPEECH_BUBBLE = 'icon-speech-bubble';
    public const CLASS_MICROPHONE = 'icon-microphone';
    public const CLASS_MICROPHONE_OFF = 'icon-microphone-off';
    public const CLASS_DISCONNECT = 'icon-disconnect';
    public const CLASS_CONNECT = 'icon-connect';
    public const CLASS_ELIMINATE = 'icon-eliminate';
    public const CLASS_WHEELCHAIR = 'icon-wheelchair';
    public const CLASS_TEXT_MARKER = 'icon-text-marker';
    public const CLASS_UNSHIELD = 'icon-unshield';
    public const CLASS_SHIELD = 'icon-shield';
    public const CLASS_TREE = 'icon-tree';
    public const CLASS_HOME = 'icon-home';
    public const CLASS_SHARED_FILE = 'icon-shared-file';
    public const CLASS_END_ATTEMPT = 'icon-end-attempt';
    public const CLASS_ICON = 'icon-icon';
    public const CLASS_RADIO_BG = 'icon-radio-bg';
    public const CLASS_CHECKBOX_BG = 'icon-checkbox-bg';
    public const CLASS_TAG = 'icon-tag';
    public const CLASS_STYLE = 'icon-style';
    public const CLASS_OWNERSHIP_TRANSFER = 'icon-ownership-transfer';
    public const CLASS_PROPERTY_ADVANCED = 'icon-property-advanced';
    public const CLASS_PROPERTY_ADD = 'icon-property-add';
    public const CLASS_REPOSITORY_ADD = 'icon-repository-add';
    public const CLASS_REPOSITORY_REMOVE = 'icon-repository-remove';
    public const CLASS_REPOSITORY = 'icon-repository';
    public const CLASS_RESULT_SERVER = 'icon-result-server';
    public const CLASS_FOLDER = 'icon-folder';
    public const CLASS_FOLDER_OPEN = 'icon-folder-open';
    public const CLASS_LEFT = 'icon-left';
    public const CLASS_RIGHT = 'icon-right';
    public const CLASS_UP = 'icon-up';
    public const CLASS_DOWN = 'icon-down';
    public const CLASS_UNDO = 'icon-undo';
    public const CLASS_REDO = 'icon-redo';
    public const CLASS_SCREEN = 'icon-screen';
    public const CLASS_LAPTOP = 'icon-laptop';
    public const CLASS_TABLET = 'icon-tablet';
    public const CLASS_PHONE = 'icon-phone';
    public const CLASS_MOVE = 'icon-move';
    public const CLASS_BIN = 'icon-bin';
    public const CLASS_SHUFFLE = 'icon-shuffle';
    public const CLASS_PRINT = 'icon-print';
    public const CLASS_TOOLS = 'icon-tools';
    public const CLASS_SETTINGS = 'icon-settings';
    public const CLASS_VIDEO = 'icon-video';
    public const CLASS_FIND = 'icon-find';
    public const CLASS_IMAGE = 'icon-image';
    public const CLASS_EDIT = 'icon-edit';
    public const CLASS_DOCUMENT = 'icon-document';
    public const CLASS_RESIZE_GRID = 'icon-resize-grid';
    public const CLASS_RESIZE = 'icon-resize';
    public const CLASS_HELP = 'icon-help';
    public const CLASS_MOBILE_MENU = 'icon-mobile-menu';
    public const CLASS_FIX = 'icon-fix';
    public const CLASS_UNLOCK = 'icon-unlock';
    public const CLASS_LOCK = 'icon-lock';
    public const CLASS_UL = 'icon-ul';
    public const CLASS_OL = 'icon-ol';
    public const CLASS_EMAIL = 'icon-email';
    public const CLASS_DOWNLOAD = 'icon-download';
    public const CLASS_LOGOUT = 'icon-logout';
    public const CLASS_LOGIN = 'icon-login';
    public const CLASS_SPINNER = 'icon-spinner';
    public const CLASS_PREVIEW = 'icon-preview';
    public const CLASS_EXTERNAL = 'icon-external';
    public const CLASS_TIME = 'icon-time';
    public const CLASS_SAVE = 'icon-save';
    public const CLASS_WARNING = 'icon-warning';
    public const CLASS_ADD = 'icon-add';
    public const CLASS_ERROR = 'icon-error';
    public const CLASS_CLOSE = 'icon-close';
    public const CLASS_SUCCESS = 'icon-success';
    public const CLASS_REMOVE = 'icon-remove';
    public const CLASS_INFO = 'icon-info';
    public const CLASS_DANGER = 'icon-danger';
    public const CLASS_USERS = 'icon-users';
    public const CLASS_USER = 'icon-user';
    public const CLASS_TEST_TAKER = 'icon-test-taker';
    public const CLASS_TEST_TAKERS = 'icon-test-takers';
    public const CLASS_ITEM = 'icon-item';
    public const CLASS_TEST = 'icon-test';
    public const CLASS_DELIVERY = 'icon-delivery';
    public const CLASS_EYE_SLASH = 'icon-eye-slash';
    public const CLASS_RESULT = 'icon-result';
    public const CLASS_DELIVERY_SMALL = 'icon-delivery-small';
    public const CLASS_UPLOAD = 'icon-upload';
    public const CLASS_RESULT_SMALL = 'icon-result-small';
    public const CLASS_MOBILE_PREVIEW = 'icon-mobile-preview';
    public const CLASS_EXTENSION = 'icon-extension';
    public const CLASS_DESKTOP_PREVIEW = 'icon-desktop-preview';
    public const CLASS_TABLET_PREVIEW = 'icon-tablet-preview';
    public const CLASS_INSERT_HORIZONTAL_LINE = 'icon-insert-horizontal-line';
    public const CLASS_TABLE = 'icon-table';
    public const CLASS_ANCHOR = 'icon-anchor';
    public const CLASS_UNLINK = 'icon-unlink';
    public const CLASS_LINK = 'icon-link';
    public const CLASS_RIGHT_LEFT = 'icon-right-left';
    public const CLASS_LEFT_RIGHT = 'icon-left-right';
    public const CLASS_SPECIAL_CHARACTER = 'icon-special-character';
    public const CLASS_SOURCE = 'icon-source';
    public const CLASS_NEW_PAGE = 'icon-new-page';
    public const CLASS_TEMPLATES = 'icon-templates';
    public const CLASS_CUT = 'icon-cut';
    public const CLASS_REPLACE = 'icon-replace';
    public const CLASS_COPY = 'icon-copy';
    public const CLASS_PASTE = 'icon-paste';
    public const CLASS_SELECT_ALL = 'icon-select-all';
    public const CLASS_PASTE_TEXT = 'icon-paste-text';
    public const CLASS_PASTE_WORD = 'icon-paste-word';
    public const CLASS_BOLD = 'icon-bold';
    public const CLASS_ITALIC = 'icon-italic';
    public const CLASS_UNDERLINE = 'icon-underline';
    public const CLASS_SUBSCRIPT = 'icon-subscript';
    public const CLASS_SUPERSCRIPT = 'icon-superscript';
    public const CLASS_STRIKE_THROUGH = 'icon-strike-through';
    public const CLASS_DECREASE_INDENT = 'icon-decrease-indent';
    public const CLASS_INCREASE_INDENT = 'icon-increase-indent';
    public const CLASS_BLOCK_QUOTE = 'icon-block-quote';
    public const CLASS_DIV_CONTAINER = 'icon-div-container';
    public const CLASS_ALIGN_LEFT = 'icon-align-left';
    public const CLASS_CENTER = 'icon-center';
    public const CLASS_ALIGN_RIGHT = 'icon-align-right';
    public const CLASS_JUSTIFY = 'icon-justify';
    public const CLASS_CHOICE = 'icon-choice';
    public const CLASS_INLINE_CHOICE = 'icon-inline-choice';
    public const CLASS_MATCH = 'icon-match';
    public const CLASS_ASSOCIATE = 'icon-associate';
    public const CLASS_MEDIA = 'icon-media';
    public const CLASS_GRAPHIC_ORDER = 'icon-graphic-order';
    public const CLASS_HOTSPOT = 'icon-hotspot';
    public const CLASS_GRAPHIC_GAP = 'icon-graphic-gap';
    public const CLASS_GRAPHIC_ASSOCIATE = 'icon-graphic-associate';
    public const CLASS_SELECT_POINT = 'icon-select-point';
    public const CLASS_PIN = 'icon-pin';
    public const CLASS_IMPORT = 'icon-import';
    public const CLASS_EXPORT = 'icon-export';
    public const CLASS_MOVE_ITEM = 'icon-move-item';
    public const CLASS_META_DATA = 'icon-meta-data';
    public const CLASS_SLIDER = 'icon-slider';
    public const CLASS_SUMMARY_REPORT = 'icon-summary-report';
    public const CLASS_TEXT_ENTRY = 'icon-text-entry';
    public const CLASS_EXTENDED_TEXT = 'icon-extended-text';
    public const CLASS_ERASER = 'icon-eraser';
    public const CLASS_ROW = 'icon-row';
    public const CLASS_COLUMN = 'icon-column';
    public const CLASS_TEXT_COLOR = 'icon-text-color';
    public const CLASS_BACKGROUND_COLOR = 'icon-background-color';
    public const CLASS_SPELL_CHECK = 'icon-spell-check';
    public const CLASS_POLYGON = 'icon-polygon';
    public const CLASS_RECTANGLE = 'icon-rectangle';
    public const CLASS_GAP_MATCH = 'icon-gap-match';
    public const CLASS_ORDER = 'icon-order';
    public const CLASS_HOTTEXT = 'icon-hottext';
    public const CLASS_FREE_FORM = 'icon-free-form';
    public const CLASS_STEP_BACKWARD = 'icon-step-backward';
    public const CLASS_FAST_BACKWARD = 'icon-fast-backward';
    public const CLASS_BACKWARD = 'icon-backward';
    public const CLASS_PLAY = 'icon-play';
    public const CLASS_PAUSE = 'icon-pause';
    public const CLASS_STOP = 'icon-stop';
    public const CLASS_FORWARD = 'icon-forward';
    public const CLASS_FAST_FORWARD = 'icon-fast-forward';
    public const CLASS_STEP_FORWARD = 'icon-step-forward';
    public const CLASS_ELLIPSIS = 'icon-ellipsis';
    public const CLASS_CIRCLE = 'icon-circle';
    public const CLASS_TARGET = 'icon-target';
    public const CLASS_GUIDE_ARROW = 'icon-guide-arrow';
    public const CLASS_RANGE_SLIDER_RIGHT = 'icon-range-slider-right';
    public const CLASS_RANGE_SLIDER_LEFT = 'icon-range-slider-left';
    public const CLASS_RADIO_CHECKED = 'icon-radio-checked';
    public const CLASS_CHECKBOX_INDETERMINATE = 'icon-checkbox-indeterminate';
    public const CLASS_CHECKBOX = 'icon-checkbox';
    public const CLASS_CHECKBOX_CROSSED = 'icon-checkbox-crossed';
    public const CLASS_CHECKBOX_CHECKED = 'icon-checkbox-checked';
    public const CLASS_RESULT_NOK = 'icon-result-nok';
    public const CLASS_RESULT_OK = 'icon-result-ok';
    public const CLASS_NOT_EVALUATED = 'icon-not-evaluated';
    public const CLASS_FILTER = 'icon-filter';
    public const CLASS_TRANSLATE = 'icon-translate';
    public const CLASS_EJECT = 'icon-eject';
    public const CLASS_CONTINUE = 'icon-continue';
    public const CLASS_RADIO = 'icon-radio';
    public const CLASS_SPHERE = 'icon-sphere';
    public const CLASS_RESET = 'icon-reset';
    public const CLASS_SMALLER = 'icon-smaller';
    public const CLASS_LARGER = 'icon-larger';
    public const CLASS_CLOCK = 'icon-clock';
    public const CLASS_FONT = 'icon-font';
    public const CLASS_MATHS = 'icon-maths';
    public const CLASS_GRIP = 'icon-grip';
    public const CLASS_RUBRIC = 'icon-rubric';
    public const CLASS_AUDIO = 'icon-audio';
    public const CLASS_GRIP_H = 'icon-grip-h';
    public const CLASS_MAGICWAND = 'icon-magicwand';
    public const CLASS_LOOP = 'icon-loop';
    public const CLASS_CALENDAR = 'icon-calendar';
    public const CLASS_RELOAD = 'icon-reload';
    public const CLASS_SPEED = 'icon-speed';
    public const CLASS_VOLUME = 'icon-volume';
    public const CLASS_CONTRAST = 'icon-contrast';
    public const CLASS_HEADPHONES = 'icon-headphones';
    public const CLASS_COMPRESS = 'icon-compress';
    public const CLASS_MAP_O = 'icon-map-o';
    public const CLASS_VARIABLE = 'icon-variable';
    public const CLASS_TOOLTIP = 'icon-tooltip';
    public const CLASS_GLOBE = 'icon-globe';
    public const CLASS_HIGHLIGHTER = 'icon-highlighter';
    public const CLASS_ELIMINATE_CROSSED = 'icon-eliminate-crossed';

    
    /**
     * List of all icons as function
     */

    public static function iconTab($options = array())
    {
        return self::buildIcon(self::CLASS_TAB, $options);
    }

    public static function iconUntab($options = array())
    {
        return self::buildIcon(self::CLASS_UNTAB, $options);
    }

    public static function iconMultiSelect($options = array())
    {
        return self::buildIcon(self::CLASS_MULTI_SELECT, $options);
    }

    public static function iconClipboard($options = array())
    {
        return self::buildIcon(self::CLASS_CLIPBOARD, $options);
    }

    public static function iconFilebox($options = array())
    {
        return self::buildIcon(self::CLASS_FILEBOX, $options);
    }

    public static function iconClickToSpeak($options = array())
    {
        return self::buildIcon(self::CLASS_CLICK_TO_SPEAK, $options);
    }

    public static function iconSpeechBubble($options = array())
    {
        return self::buildIcon(self::CLASS_SPEECH_BUBBLE, $options);
    }

    public static function iconMicrophone($options = array())
    {
        return self::buildIcon(self::CLASS_MICROPHONE, $options);
    }

    public static function iconMicrophoneOff($options = array())
    {
        return self::buildIcon(self::CLASS_MICROPHONE_OFF, $options);
    }

    public static function iconDisconnect($options = array())
    {
        return self::buildIcon(self::CLASS_DISCONNECT, $options);
    }

    public static function iconConnect($options = array())
    {
        return self::buildIcon(self::CLASS_CONNECT, $options);
    }

    public static function iconEliminate($options = array())
    {
        return self::buildIcon(self::CLASS_ELIMINATE, $options);
    }

    public static function iconWheelchair($options = array())
    {
        return self::buildIcon(self::CLASS_WHEELCHAIR, $options);
    }

    public static function iconTextMarker($options = array())
    {
        return self::buildIcon(self::CLASS_TEXT_MARKER, $options);
    }

    public static function iconUnshield($options = array())
    {
        return self::buildIcon(self::CLASS_UNSHIELD, $options);
    }

    public static function iconShield($options = array())
    {
        return self::buildIcon(self::CLASS_SHIELD, $options);
    }

    public static function iconTree($options = array())
    {
        return self::buildIcon(self::CLASS_TREE, $options);
    }

    public static function iconHome($options = array())
    {
        return self::buildIcon(self::CLASS_HOME, $options);
    }

    public static function iconSharedFile($options = array())
    {
        return self::buildIcon(self::CLASS_SHARED_FILE, $options);
    }

    public static function iconEndAttempt($options = array())
    {
        return self::buildIcon(self::CLASS_END_ATTEMPT, $options);
    }

    public static function iconIcon($options = array())
    {
        return self::buildIcon(self::CLASS_ICON, $options);
    }

    public static function iconRadioBg($options = array())
    {
        return self::buildIcon(self::CLASS_RADIO_BG, $options);
    }

    public static function iconCheckboxBg($options = array())
    {
        return self::buildIcon(self::CLASS_CHECKBOX_BG, $options);
    }

    public static function iconTag($options = array())
    {
        return self::buildIcon(self::CLASS_TAG, $options);
    }

    public static function iconStyle($options = array())
    {
        return self::buildIcon(self::CLASS_STYLE, $options);
    }

    public static function iconOwnershipTransfer($options = array())
    {
        return self::buildIcon(self::CLASS_OWNERSHIP_TRANSFER, $options);
    }

    public static function iconPropertyAdvanced($options = array())
    {
        return self::buildIcon(self::CLASS_PROPERTY_ADVANCED, $options);
    }

    public static function iconPropertyAdd($options = array())
    {
        return self::buildIcon(self::CLASS_PROPERTY_ADD, $options);
    }

    public static function iconRepositoryAdd($options = array())
    {
        return self::buildIcon(self::CLASS_REPOSITORY_ADD, $options);
    }

    public static function iconRepositoryRemove($options = array())
    {
        return self::buildIcon(self::CLASS_REPOSITORY_REMOVE, $options);
    }

    public static function iconRepository($options = array())
    {
        return self::buildIcon(self::CLASS_REPOSITORY, $options);
    }

    public static function iconResultServer($options = array())
    {
        return self::buildIcon(self::CLASS_RESULT_SERVER, $options);
    }

    public static function iconFolder($options = array())
    {
        return self::buildIcon(self::CLASS_FOLDER, $options);
    }

    public static function iconFolderOpen($options = array())
    {
        return self::buildIcon(self::CLASS_FOLDER_OPEN, $options);
    }

    public static function iconLeft($options = array())
    {
        return self::buildIcon(self::CLASS_LEFT, $options);
    }

    public static function iconRight($options = array())
    {
        return self::buildIcon(self::CLASS_RIGHT, $options);
    }

    public static function iconUp($options = array())
    {
        return self::buildIcon(self::CLASS_UP, $options);
    }

    public static function iconDown($options = array())
    {
        return self::buildIcon(self::CLASS_DOWN, $options);
    }

    public static function iconUndo($options = array())
    {
        return self::buildIcon(self::CLASS_UNDO, $options);
    }

    public static function iconRedo($options = array())
    {
        return self::buildIcon(self::CLASS_REDO, $options);
    }

    public static function iconScreen($options = array())
    {
        return self::buildIcon(self::CLASS_SCREEN, $options);
    }

    public static function iconLaptop($options = array())
    {
        return self::buildIcon(self::CLASS_LAPTOP, $options);
    }

    public static function iconTablet($options = array())
    {
        return self::buildIcon(self::CLASS_TABLET, $options);
    }

    public static function iconPhone($options = array())
    {
        return self::buildIcon(self::CLASS_PHONE, $options);
    }

    public static function iconMove($options = array())
    {
        return self::buildIcon(self::CLASS_MOVE, $options);
    }

    public static function iconBin($options = array())
    {
        return self::buildIcon(self::CLASS_BIN, $options);
    }

    public static function iconShuffle($options = array())
    {
        return self::buildIcon(self::CLASS_SHUFFLE, $options);
    }

    public static function iconPrint($options = array())
    {
        return self::buildIcon(self::CLASS_PRINT, $options);
    }

    public static function iconTools($options = array())
    {
        return self::buildIcon(self::CLASS_TOOLS, $options);
    }

    public static function iconSettings($options = array())
    {
        return self::buildIcon(self::CLASS_SETTINGS, $options);
    }

    public static function iconVideo($options = array())
    {
        return self::buildIcon(self::CLASS_VIDEO, $options);
    }

    public static function iconFind($options = array())
    {
        return self::buildIcon(self::CLASS_FIND, $options);
    }

    public static function iconImage($options = array())
    {
        return self::buildIcon(self::CLASS_IMAGE, $options);
    }

    public static function iconEdit($options = array())
    {
        return self::buildIcon(self::CLASS_EDIT, $options);
    }

    public static function iconDocument($options = array())
    {
        return self::buildIcon(self::CLASS_DOCUMENT, $options);
    }

    public static function iconResizeGrid($options = array())
    {
        return self::buildIcon(self::CLASS_RESIZE_GRID, $options);
    }

    public static function iconResize($options = array())
    {
        return self::buildIcon(self::CLASS_RESIZE, $options);
    }

    public static function iconHelp($options = array())
    {
        return self::buildIcon(self::CLASS_HELP, $options);
    }

    public static function iconMobileMenu($options = array())
    {
        return self::buildIcon(self::CLASS_MOBILE_MENU, $options);
    }

    public static function iconFix($options = array())
    {
        return self::buildIcon(self::CLASS_FIX, $options);
    }

    public static function iconUnlock($options = array())
    {
        return self::buildIcon(self::CLASS_UNLOCK, $options);
    }

    public static function iconLock($options = array())
    {
        return self::buildIcon(self::CLASS_LOCK, $options);
    }

    public static function iconUl($options = array())
    {
        return self::buildIcon(self::CLASS_UL, $options);
    }

    public static function iconOl($options = array())
    {
        return self::buildIcon(self::CLASS_OL, $options);
    }

    public static function iconEmail($options = array())
    {
        return self::buildIcon(self::CLASS_EMAIL, $options);
    }

    public static function iconDownload($options = array())
    {
        return self::buildIcon(self::CLASS_DOWNLOAD, $options);
    }

    public static function iconLogout($options = array())
    {
        return self::buildIcon(self::CLASS_LOGOUT, $options);
    }

    public static function iconLogin($options = array())
    {
        return self::buildIcon(self::CLASS_LOGIN, $options);
    }

    public static function iconSpinner($options = array())
    {
        return self::buildIcon(self::CLASS_SPINNER, $options);
    }

    public static function iconPreview($options = array())
    {
        return self::buildIcon(self::CLASS_PREVIEW, $options);
    }

    public static function iconExternal($options = array())
    {
        return self::buildIcon(self::CLASS_EXTERNAL, $options);
    }

    public static function iconTime($options = array())
    {
        return self::buildIcon(self::CLASS_TIME, $options);
    }

    public static function iconSave($options = array())
    {
        return self::buildIcon(self::CLASS_SAVE, $options);
    }

    public static function iconWarning($options = array())
    {
        return self::buildIcon(self::CLASS_WARNING, $options);
    }

    public static function iconAdd($options = array())
    {
        return self::buildIcon(self::CLASS_ADD, $options);
    }

    public static function iconError($options = array())
    {
        return self::buildIcon(self::CLASS_ERROR, $options);
    }

    public static function iconClose($options = array())
    {
        return self::buildIcon(self::CLASS_CLOSE, $options);
    }

    public static function iconSuccess($options = array())
    {
        return self::buildIcon(self::CLASS_SUCCESS, $options);
    }

    public static function iconRemove($options = array())
    {
        return self::buildIcon(self::CLASS_REMOVE, $options);
    }

    public static function iconInfo($options = array())
    {
        return self::buildIcon(self::CLASS_INFO, $options);
    }

    public static function iconDanger($options = array())
    {
        return self::buildIcon(self::CLASS_DANGER, $options);
    }

    public static function iconUsers($options = array())
    {
        return self::buildIcon(self::CLASS_USERS, $options);
    }

    public static function iconUser($options = array())
    {
        return self::buildIcon(self::CLASS_USER, $options);
    }

    public static function iconTestTaker($options = array())
    {
        return self::buildIcon(self::CLASS_TEST_TAKER, $options);
    }

    public static function iconTestTakers($options = array())
    {
        return self::buildIcon(self::CLASS_TEST_TAKERS, $options);
    }

    public static function iconItem($options = array())
    {
        return self::buildIcon(self::CLASS_ITEM, $options);
    }

    public static function iconTest($options = array())
    {
        return self::buildIcon(self::CLASS_TEST, $options);
    }

    public static function iconDelivery($options = array())
    {
        return self::buildIcon(self::CLASS_DELIVERY, $options);
    }

    public static function iconEyeSlash($options = array())
    {
        return self::buildIcon(self::CLASS_EYE_SLASH, $options);
    }

    public static function iconResult($options = array())
    {
        return self::buildIcon(self::CLASS_RESULT, $options);
    }

    public static function iconDeliverySmall($options = array())
    {
        return self::buildIcon(self::CLASS_DELIVERY_SMALL, $options);
    }

    public static function iconUpload($options = array())
    {
        return self::buildIcon(self::CLASS_UPLOAD, $options);
    }

    public static function iconResultSmall($options = array())
    {
        return self::buildIcon(self::CLASS_RESULT_SMALL, $options);
    }

    public static function iconMobilePreview($options = array())
    {
        return self::buildIcon(self::CLASS_MOBILE_PREVIEW, $options);
    }

    public static function iconExtension($options = array())
    {
        return self::buildIcon(self::CLASS_EXTENSION, $options);
    }

    public static function iconDesktopPreview($options = array())
    {
        return self::buildIcon(self::CLASS_DESKTOP_PREVIEW, $options);
    }

    public static function iconTabletPreview($options = array())
    {
        return self::buildIcon(self::CLASS_TABLET_PREVIEW, $options);
    }

    public static function iconInsertHorizontalLine($options = array())
    {
        return self::buildIcon(self::CLASS_INSERT_HORIZONTAL_LINE, $options);
    }

    public static function iconTable($options = array())
    {
        return self::buildIcon(self::CLASS_TABLE, $options);
    }

    public static function iconAnchor($options = array())
    {
        return self::buildIcon(self::CLASS_ANCHOR, $options);
    }

    public static function iconUnlink($options = array())
    {
        return self::buildIcon(self::CLASS_UNLINK, $options);
    }

    public static function iconLink($options = array())
    {
        return self::buildIcon(self::CLASS_LINK, $options);
    }

    public static function iconRightLeft($options = array())
    {
        return self::buildIcon(self::CLASS_RIGHT_LEFT, $options);
    }

    public static function iconLeftRight($options = array())
    {
        return self::buildIcon(self::CLASS_LEFT_RIGHT, $options);
    }

    public static function iconSpecialCharacter($options = array())
    {
        return self::buildIcon(self::CLASS_SPECIAL_CHARACTER, $options);
    }

    public static function iconSource($options = array())
    {
        return self::buildIcon(self::CLASS_SOURCE, $options);
    }

    public static function iconNewPage($options = array())
    {
        return self::buildIcon(self::CLASS_NEW_PAGE, $options);
    }

    public static function iconTemplates($options = array())
    {
        return self::buildIcon(self::CLASS_TEMPLATES, $options);
    }

    public static function iconCut($options = array())
    {
        return self::buildIcon(self::CLASS_CUT, $options);
    }

    public static function iconReplace($options = array())
    {
        return self::buildIcon(self::CLASS_REPLACE, $options);
    }

    public static function iconCopy($options = array())
    {
        return self::buildIcon(self::CLASS_COPY, $options);
    }

    public static function iconPaste($options = array())
    {
        return self::buildIcon(self::CLASS_PASTE, $options);
    }

    public static function iconSelectAll($options = array())
    {
        return self::buildIcon(self::CLASS_SELECT_ALL, $options);
    }

    public static function iconPasteText($options = array())
    {
        return self::buildIcon(self::CLASS_PASTE_TEXT, $options);
    }

    public static function iconPasteWord($options = array())
    {
        return self::buildIcon(self::CLASS_PASTE_WORD, $options);
    }

    public static function iconBold($options = array())
    {
        return self::buildIcon(self::CLASS_BOLD, $options);
    }

    public static function iconItalic($options = array())
    {
        return self::buildIcon(self::CLASS_ITALIC, $options);
    }

    public static function iconUnderline($options = array())
    {
        return self::buildIcon(self::CLASS_UNDERLINE, $options);
    }

    public static function iconSubscript($options = array())
    {
        return self::buildIcon(self::CLASS_SUBSCRIPT, $options);
    }

    public static function iconSuperscript($options = array())
    {
        return self::buildIcon(self::CLASS_SUPERSCRIPT, $options);
    }

    public static function iconStrikeThrough($options = array())
    {
        return self::buildIcon(self::CLASS_STRIKE_THROUGH, $options);
    }

    public static function iconDecreaseIndent($options = array())
    {
        return self::buildIcon(self::CLASS_DECREASE_INDENT, $options);
    }

    public static function iconIncreaseIndent($options = array())
    {
        return self::buildIcon(self::CLASS_INCREASE_INDENT, $options);
    }

    public static function iconBlockQuote($options = array())
    {
        return self::buildIcon(self::CLASS_BLOCK_QUOTE, $options);
    }

    public static function iconDivContainer($options = array())
    {
        return self::buildIcon(self::CLASS_DIV_CONTAINER, $options);
    }

    public static function iconAlignLeft($options = array())
    {
        return self::buildIcon(self::CLASS_ALIGN_LEFT, $options);
    }

    public static function iconCenter($options = array())
    {
        return self::buildIcon(self::CLASS_CENTER, $options);
    }

    public static function iconAlignRight($options = array())
    {
        return self::buildIcon(self::CLASS_ALIGN_RIGHT, $options);
    }

    public static function iconJustify($options = array())
    {
        return self::buildIcon(self::CLASS_JUSTIFY, $options);
    }

    public static function iconChoice($options = array())
    {
        return self::buildIcon(self::CLASS_CHOICE, $options);
    }

    public static function iconInlineChoice($options = array())
    {
        return self::buildIcon(self::CLASS_INLINE_CHOICE, $options);
    }

    public static function iconMatch($options = array())
    {
        return self::buildIcon(self::CLASS_MATCH, $options);
    }

    public static function iconAssociate($options = array())
    {
        return self::buildIcon(self::CLASS_ASSOCIATE, $options);
    }

    public static function iconMedia($options = array())
    {
        return self::buildIcon(self::CLASS_MEDIA, $options);
    }

    public static function iconGraphicOrder($options = array())
    {
        return self::buildIcon(self::CLASS_GRAPHIC_ORDER, $options);
    }

    public static function iconHotspot($options = array())
    {
        return self::buildIcon(self::CLASS_HOTSPOT, $options);
    }

    public static function iconGraphicGap($options = array())
    {
        return self::buildIcon(self::CLASS_GRAPHIC_GAP, $options);
    }

    public static function iconGraphicAssociate($options = array())
    {
        return self::buildIcon(self::CLASS_GRAPHIC_ASSOCIATE, $options);
    }

    public static function iconSelectPoint($options = array())
    {
        return self::buildIcon(self::CLASS_SELECT_POINT, $options);
    }

    public static function iconPin($options = array())
    {
        return self::buildIcon(self::CLASS_PIN, $options);
    }

    public static function iconImport($options = array())
    {
        return self::buildIcon(self::CLASS_IMPORT, $options);
    }

    public static function iconExport($options = array())
    {
        return self::buildIcon(self::CLASS_EXPORT, $options);
    }

    public static function iconMoveItem($options = array())
    {
        return self::buildIcon(self::CLASS_MOVE_ITEM, $options);
    }

    public static function iconMetaData($options = array())
    {
        return self::buildIcon(self::CLASS_META_DATA, $options);
    }

    public static function iconSlider($options = array())
    {
        return self::buildIcon(self::CLASS_SLIDER, $options);
    }

    public static function iconSummaryReport($options = array())
    {
        return self::buildIcon(self::CLASS_SUMMARY_REPORT, $options);
    }

    public static function iconTextEntry($options = array())
    {
        return self::buildIcon(self::CLASS_TEXT_ENTRY, $options);
    }

    public static function iconExtendedText($options = array())
    {
        return self::buildIcon(self::CLASS_EXTENDED_TEXT, $options);
    }

    public static function iconEraser($options = array())
    {
        return self::buildIcon(self::CLASS_ERASER, $options);
    }

    public static function iconRow($options = array())
    {
        return self::buildIcon(self::CLASS_ROW, $options);
    }

    public static function iconColumn($options = array())
    {
        return self::buildIcon(self::CLASS_COLUMN, $options);
    }

    public static function iconTextColor($options = array())
    {
        return self::buildIcon(self::CLASS_TEXT_COLOR, $options);
    }

    public static function iconBackgroundColor($options = array())
    {
        return self::buildIcon(self::CLASS_BACKGROUND_COLOR, $options);
    }

    public static function iconSpellCheck($options = array())
    {
        return self::buildIcon(self::CLASS_SPELL_CHECK, $options);
    }

    public static function iconPolygon($options = array())
    {
        return self::buildIcon(self::CLASS_POLYGON, $options);
    }

    public static function iconRectangle($options = array())
    {
        return self::buildIcon(self::CLASS_RECTANGLE, $options);
    }

    public static function iconGapMatch($options = array())
    {
        return self::buildIcon(self::CLASS_GAP_MATCH, $options);
    }

    public static function iconOrder($options = array())
    {
        return self::buildIcon(self::CLASS_ORDER, $options);
    }

    public static function iconHottext($options = array())
    {
        return self::buildIcon(self::CLASS_HOTTEXT, $options);
    }

    public static function iconFreeForm($options = array())
    {
        return self::buildIcon(self::CLASS_FREE_FORM, $options);
    }

    public static function iconStepBackward($options = array())
    {
        return self::buildIcon(self::CLASS_STEP_BACKWARD, $options);
    }

    public static function iconFastBackward($options = array())
    {
        return self::buildIcon(self::CLASS_FAST_BACKWARD, $options);
    }

    public static function iconBackward($options = array())
    {
        return self::buildIcon(self::CLASS_BACKWARD, $options);
    }

    public static function iconPlay($options = array())
    {
        return self::buildIcon(self::CLASS_PLAY, $options);
    }

    public static function iconPause($options = array())
    {
        return self::buildIcon(self::CLASS_PAUSE, $options);
    }

    public static function iconStop($options = array())
    {
        return self::buildIcon(self::CLASS_STOP, $options);
    }

    public static function iconForward($options = array())
    {
        return self::buildIcon(self::CLASS_FORWARD, $options);
    }

    public static function iconFastForward($options = array())
    {
        return self::buildIcon(self::CLASS_FAST_FORWARD, $options);
    }

    public static function iconStepForward($options = array())
    {
        return self::buildIcon(self::CLASS_STEP_FORWARD, $options);
    }

    public static function iconEllipsis($options = array())
    {
        return self::buildIcon(self::CLASS_ELLIPSIS, $options);
    }

    public static function iconCircle($options = array())
    {
        return self::buildIcon(self::CLASS_CIRCLE, $options);
    }

    public static function iconTarget($options = array())
    {
        return self::buildIcon(self::CLASS_TARGET, $options);
    }

    public static function iconGuideArrow($options = array())
    {
        return self::buildIcon(self::CLASS_GUIDE_ARROW, $options);
    }

    public static function iconRangeSliderRight($options = array())
    {
        return self::buildIcon(self::CLASS_RANGE_SLIDER_RIGHT, $options);
    }

    public static function iconRangeSliderLeft($options = array())
    {
        return self::buildIcon(self::CLASS_RANGE_SLIDER_LEFT, $options);
    }

    public static function iconRadioChecked($options = array())
    {
        return self::buildIcon(self::CLASS_RADIO_CHECKED, $options);
    }

    public static function iconCheckboxIndeterminate($options = array())
    {
        return self::buildIcon(self::CLASS_CHECKBOX_INDETERMINATE, $options);
    }

    public static function iconCheckbox($options = array())
    {
        return self::buildIcon(self::CLASS_CHECKBOX, $options);
    }

    public static function iconCheckboxCrossed($options = array())
    {
        return self::buildIcon(self::CLASS_CHECKBOX_CROSSED, $options);
    }

    public static function iconCheckboxChecked($options = array())
    {
        return self::buildIcon(self::CLASS_CHECKBOX_CHECKED, $options);
    }

    public static function iconResultNok($options = array())
    {
        return self::buildIcon(self::CLASS_RESULT_NOK, $options);
    }

    public static function iconResultOk($options = array())
    {
        return self::buildIcon(self::CLASS_RESULT_OK, $options);
    }

    public static function iconNotEvaluated($options = array())
    {
        return self::buildIcon(self::CLASS_NOT_EVALUATED, $options);
    }

    public static function iconFilter($options = array())
    {
        return self::buildIcon(self::CLASS_FILTER, $options);
    }

    public static function iconTranslate($options = array())
    {
        return self::buildIcon(self::CLASS_TRANSLATE, $options);
    }

    public static function iconEject($options = array())
    {
        return self::buildIcon(self::CLASS_EJECT, $options);
    }

    public static function iconContinue($options = array())
    {
        return self::buildIcon(self::CLASS_CONTINUE, $options);
    }

    public static function iconRadio($options = array())
    {
        return self::buildIcon(self::CLASS_RADIO, $options);
    }

    public static function iconSphere($options = array())
    {
        return self::buildIcon(self::CLASS_SPHERE, $options);
    }

    public static function iconReset($options = array())
    {
        return self::buildIcon(self::CLASS_RESET, $options);
    }

    public static function iconSmaller($options = array())
    {
        return self::buildIcon(self::CLASS_SMALLER, $options);
    }

    public static function iconLarger($options = array())
    {
        return self::buildIcon(self::CLASS_LARGER, $options);
    }

    public static function iconClock($options = array())
    {
        return self::buildIcon(self::CLASS_CLOCK, $options);
    }

    public static function iconFont($options = array())
    {
        return self::buildIcon(self::CLASS_FONT, $options);
    }

    public static function iconMaths($options = array())
    {
        return self::buildIcon(self::CLASS_MATHS, $options);
    }

    public static function iconGrip($options = array())
    {
        return self::buildIcon(self::CLASS_GRIP, $options);
    }

    public static function iconRubric($options = array())
    {
        return self::buildIcon(self::CLASS_RUBRIC, $options);
    }

    public static function iconAudio($options = array())
    {
        return self::buildIcon(self::CLASS_AUDIO, $options);
    }

    public static function iconGripH($options = array())
    {
        return self::buildIcon(self::CLASS_GRIP_H, $options);
    }

    public static function iconMagicwand($options = array())
    {
        return self::buildIcon(self::CLASS_MAGICWAND, $options);
    }

    public static function iconLoop($options = array())
    {
        return self::buildIcon(self::CLASS_LOOP, $options);
    }

    public static function iconCalendar($options = array())
    {
        return self::buildIcon(self::CLASS_CALENDAR, $options);
    }

    public static function iconReload($options = array())
    {
        return self::buildIcon(self::CLASS_RELOAD, $options);
    }

    public static function iconSpeed($options = array())
    {
        return self::buildIcon(self::CLASS_SPEED, $options);
    }

    public static function iconVolume($options = array())
    {
        return self::buildIcon(self::CLASS_VOLUME, $options);
    }

    public static function iconContrast($options = array())
    {
        return self::buildIcon(self::CLASS_CONTRAST, $options);
    }

    public static function iconHeadphones($options = array())
    {
        return self::buildIcon(self::CLASS_HEADPHONES, $options);
    }

    public static function iconCompress($options = array())
    {
        return self::buildIcon(self::CLASS_COMPRESS, $options);
    }

    public static function iconMapO($options = array())
    {
        return self::buildIcon(self::CLASS_MAP_O, $options);
    }

    public static function iconVariable($options = array())
    {
        return self::buildIcon(self::CLASS_VARIABLE, $options);
    }

    public static function iconTooltip($options = array())
    {
        return self::buildIcon(self::CLASS_TOOLTIP, $options);
    }

    public static function iconGlobe($options = array())
    {
        return self::buildIcon(self::CLASS_GLOBE, $options);
    }

    public static function iconHighlighter($options = array())
    {
        return self::buildIcon(self::CLASS_HIGHLIGHTER, $options);
    }

    public static function iconEliminateCrossed($options = array())
    {
        return self::buildIcon(self::CLASS_ELIMINATE_CROSSED, $options);
    }
}
