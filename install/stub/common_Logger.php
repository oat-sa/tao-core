<?php
/**
 * 
 * This is a stub of the Logger to be used duringg the instalation
 * @author bout
 *
 */
class common_Logger {
   	private static function log($pMessage) {
   		echo $pMessage.'<br />';
   	}
   
    const TRACE_LEVEL = 0;
    const DEBUG_LEVEL = 1;
    const INFO_LEVEL = 2;
    const WARNING_LEVEL = 3;
    const ERROR_LEVEL = 4;
    const FATAL_LEVEL = 5;

    public static function enable() {}
    public static function disable() {}
    public static function restore() {}
    public static function t($pMessage, $tags = array()) {}
    public static function d($pMessage, $tags = array()) {}
    public static function i($pMessage, $tags = array()) {}
    public static function w($pMessage, $tags = array()) {self::log($pMessage);}
    public static function e($pMessage, $tags = array()) {self::log($pMessage);}
    public static function f($pMessage, $tags = array()) {self::log($pMessage);}
}
?>