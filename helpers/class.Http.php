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
 * Copyright (c) 2008-2010 (original work) Deutsche Institut für Internationale Pädagogische Forschung
 *                         (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor
 *                         (under the project TAO-SUSTAIN & TAO-DEV);
 *               2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

use oat\generis\Helper\SystemHelper;
use oat\oatbox\service\ServiceManager;
use oat\tao\helpers\FileUploadException;
use oat\tao\model\http\ContentDetector;
use oat\tao\model\stream\StreamRange;
use oat\tao\model\stream\StreamRangeException;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Description of class
 *
 * @author "Patrick Plichart, <patrick@taotesting.com>"
 */
class tao_helpers_Http
{
    public const BYTES_BY_CYCLE =  5242880; //1024 * 1024 * 5

    public static $headers;

    /**
     * @author "Patrick Plichart, <patrick@taotesting.com>"
     * @return boolean|Ambigous <unknown, string>
     */
    public static function getDigest()
    {
        // seems apache-php is absorbing the header
        if (isset($_SERVER['PHP_AUTH_DIGEST'])) {
            $digest = $_SERVER['PHP_AUTH_DIGEST'];
        // most other servers
        } elseif (isset($_SERVER['HTTP_AUTHENTICATION'])) {
            if (strpos(strtolower($_SERVER['HTTP_AUTHENTICATION']), 'digest') === 0) {
                $digest = substr($_SERVER['HTTP_AUTHORIZATION'], 7);
            }
        } else {
            return false;
        }

        return $digest;
    }

    /**
     * @author "Patrick Plichart, <patrick@taotesting.com>"
     * @param string $digest
     * @return Ambigous <boolean, multitype:unknown >
     */
    public static function parseDigest($digest)
    {
        // protect against missing data
        $needed_parts = [
            'nonce' => 1,
            'nc' => 1,
            'cnonce' => 1,
            'qop' => 1,
            'username' => 1,
            'uri' => 1,
            'response' => 1
        ];
        $data = [];
        $keys = implode('|', array_keys($needed_parts));

        preg_match_all('@(' . $keys . ')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', $digest, $matches, PREG_SET_ORDER);

        foreach ($matches as $m) {
            $data[$m[1]] = $m[3] ? $m[3] : $m[4];
            unset($needed_parts[$m[1]]);
        }
        return $needed_parts ? false : $data;
    }

    /**
     * Return array of HTTP headers from the current request
     * @return array|false
     */
    public static function getHeaders()
    {
        if (self::$headers === null) {
            if (function_exists('apache_request_headers')) {
                $headers = apache_request_headers();
            } else {
                $headers = [];
                if (isset($_SERVER['CONTENT_TYPE'])) {
                    $headers['Content-Type'] = $_SERVER['CONTENT_TYPE'];
                }
                if (isset($_ENV['CONTENT_TYPE'])) {
                    $headers['Content-Type'] = $_ENV['CONTENT_TYPE'];
                }
                foreach ($_SERVER as $key => $value) {
                    if (substr($key, 0, 5) == "HTTP_") {
                        // this is chaos, basically it is just there to capitalize the first
                        // letter of every word that is not an initial HTTP and strip HTTP
                        // code from przemek
                        $key = str_replace(" ", "-", ucwords(strtolower(str_replace("_", " ", substr($key, 5)))));
                        $headers[$key] = $value;
                    }
                }
            }
            self::$headers = $headers;
        }

        return self::$headers;
    }

    /**
     * @author "Patrick Plichart, <patrick@taotesting.com>"
     * @return string[]
     */
    public static function getFiles()
    {
        if (isset($_POST['contentName'])) {
            $_FILES['content']['name'] = urldecode($_POST['contentName']);
        }
        return $_FILES;
    }

    /**
     * verify if file uploads exists.
     * return true if key $name exists in $_FILES
     *
     * @author Christophe GARCIA <christopheg@taotesting.com>
     * @param string $name
     * @return boolean
     */
    public static function hasUploadedFile($name)
    {
        return array_key_exists($name, self::getFiles());
    }

    /**
     * Get the files data from an HTTP file upload (ie. from the $_FILES)
     * @author "Bertrand Chevrier <bertrand@taotesting.com>
     * @param string the file field name
     * @return array the file data
     * @throws common_exception_Error in case of wrong upload
     */
    public static function getUploadedFile($name)
    {

        // for large file, the $_FILES may be empty so see this before checking for other updates
        $limit = SystemHelper::getFileUploadLimit();
        $contentLength = intval($_SERVER['CONTENT_LENGTH']);
        if ($limit > 0 && $contentLength > $limit && count(self::getFiles()) === 0) {
            throw new FileUploadException('Exceeded filesize limit of ' . $limit);
        }

        $files = self::getFiles();
        $fileData = $files[$name];
        if (isset($files[$name])) {
            //check for upload errors
            if (isset($fileData['error']) && $fileData['error'] != UPLOAD_ERR_OK) {
                switch ($fileData['error']) {
                    case UPLOAD_ERR_NO_FILE:
                        throw new FileUploadException('No file sent.');
                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:
                        throw new FileUploadException('Exceeded filesize limit of ' . $limit);
                    default:
                        throw new common_exception_Error('Upload fails, check errors');
                }
            }
        }
        if (!is_uploaded_file($fileData['tmp_name'])) {
            throw new common_exception_Error('Non uploaded file in filedata, potential attack');
        }
        return $fileData;
    }

    /**
     * @author "Patrick Plichart, <patrick@taotesting.com>"
     * @param string $supportedMimeTypes
     * @param string $requestedMimeTypes
     * @throws common_exception_NotAcceptable
     * @return string|NULL
     */
    public static function acceptHeader($supportedMimeTypes = null, $requestedMimeTypes = null)
    {
        $acceptTypes = [];
        $accept = strtolower($requestedMimeTypes);
        $accept = explode(',', $accept);
        foreach ($accept as $a) {
            // the default quality is 1.
            $q = 1;
            // check if there is a different quality
            if (strpos($a, ';q=')) {
                // divide "mime/type;q=X" into two parts: "mime/type" i "X"
                list($a, $q) = explode(';q=', $a);
            }
            // mime-type $a is accepted with the quality $q
            // WARNING: $q == 0 means, that mime-type isn’t supported!
            $acceptTypes[$a] = $q;
        }
        arsort($acceptTypes);
        if (!$supportedMimeTypes) {
            return reset($acceptTypes);
        }
        $supportedMimeTypes = array_map('strtolower', (array) $supportedMimeTypes);
        // let’s check our supported types:
        foreach ($acceptTypes as $mime => $q) {
            if ($mime === '*/*') {
                return null;
            }

            if ($q && in_array(trim($mime), $supportedMimeTypes)) {
                return trim($mime);
            }
        }
        throw new common_exception_NotAcceptable();
    }

    /**
     * Sends file content to the client(browser or video/audio player in the browser), it serves images, video/audio
     * files and any other type of file.<br />
     * If the client asks for partial contents, then partial contents are served, if not, the whole file is send.<br />
     * Works well with big files, without eating up memory.
     * @author "Martin for OAT <code@taotesting.com>"
     * @param string $filename the file name
     * @param boolean $contenttype whether to add content type header or not
     * @param boolean $svgzSupport whether to add content encoding header or not
     * @throws common_exception_Error
     */
    public static function returnFile($filename, $contenttype = true, $svgzSupport = false)
    {
        if (tao_helpers_File::securityCheck($filename, true)) {
            if (file_exists($filename)) {
                if ($contenttype) {
                    header('Content-Type: ' . tao_helpers_File::getMimeType($filename));
                }
                $fp = fopen($filename, 'rb');
                if ($fp === false) {
                    header("HTTP/1.0 404 Not Found");
                } else {
                    $pathinfo = pathinfo($filename);
                    if (isset($pathinfo['extension']) && $pathinfo['extension'] === 'svgz' && !$svgzSupport) {
                        header('Content-Encoding: gzip');
                    }

                    // session must be closed because, for example, video files might take a while to be sent to the
                    // client and we need the client to be able to make other calls to the server during that time
                    session_write_close();

                    $http416RequestRangeNotSatisfiable = 'HTTP/1.1 416 Requested Range Not Satisfiable';
                    $http206PartialContent = 'HTTP/1.1 206 Partial Content';
                    $http200OK = 'HTTP/1.1 200 OK';
                    $filesize = filesize($filename);
                    $offset = 0;
                    $length = $filesize;
                    $useFpassthru = false;
                    $partialContent = false;
                    header('Accept-Ranges: bytes');

                    if (isset($_SERVER['HTTP_RANGE'])) {
                        $partialContent = true;
                        preg_match('/bytes=(\d+)-(\d+)?/', $_SERVER['HTTP_RANGE'], $matches);
                        $offset = intval($matches[1]);
                        if (!isset($matches[2])) {
                            // no end position is given, so we serve the file from the start position to the end
                            $useFpassthru = true;
                        } else {
                            $length = intval($matches[2]) - $offset;
                        }
                    }

                    fseek($fp, $offset);

                    if ($partialContent) {
                        if (($offset < 0) || ($offset > $filesize)) {
                            header($http416RequestRangeNotSatisfiable);
                        } else {
                            if ($useFpassthru) {
                                // only a starting position is given
                                header($http206PartialContent);
                                header("Content-Length: " . ($filesize - $offset));
                                header('Content-Range: bytes ' . $offset . '-' . ($filesize - 1) . '/' . $filesize);
                                if (ob_get_level() > 0) {
                                    ob_end_flush();
                                }
                                fpassthru($fp);
                            } else {
                                // we are given a starting position and how many bytes the client asks for
                                $endPosition = $offset + $length;
                                if ($endPosition > $filesize) {
                                    header($http416RequestRangeNotSatisfiable);
                                } else {
                                    header($http206PartialContent);
                                    header("Content-Length: " . ($length));
                                    header(
                                        'Content-Range: bytes ' . $offset . '-' . ($offset + $length - 1) . '/'
                                            . $filesize
                                    );
                                    // send 500KB per cycle
                                    $bytesPerCycle = (1024 * 1024) * 0.5;
                                    $currentPosition = $offset;
                                    if (ob_get_level() > 0) {
                                        ob_end_flush();
                                    }
                                    // because the client might ask for the whole file, we split the serving into little
                                    // pieces this is also good in case someone with bad intentions tries to get the
                                    // whole file many times and eat up the server memory, we are not loading the whole
                                    // file into the memory.
                                    while (!feof($fp)) {
                                        if (($currentPosition + $bytesPerCycle) <= $endPosition) {
                                            $data = fread($fp, $bytesPerCycle);
                                            $currentPosition = $currentPosition + $bytesPerCycle;
                                            echo $data;
                                        } else {
                                            $data = fread($fp, ($endPosition - $currentPosition));
                                            echo $data;
                                        }
                                    }
                                }
                            }
                        }
                    } else {
                        // client does not want partial contents so we just serve the whole file
                        header($http200OK);
                        header("Content-Length: " . $filesize);
                        if (ob_get_level() > 0) {
                            ob_end_flush();
                        }
                        fpassthru($fp);
                    }
                    fclose($fp);
                }
            } else {
                if (class_exists('common_Logger')) {
                    common_Logger::w('File ' . $filename . ' not found');
                }
                header("HTTP/1.0 404 Not Found");
            }
        } else {
            throw new common_exception_Error('Security exception for path ' . $filename);
        }
    }

    public static function returnStream(
        StreamInterface $stream,
        string $mimeType = null,
        ServerRequestInterface $request = null
    ): void {
        header('Accept-Ranges: bytes');
        if (!is_null($mimeType)) {
            header('Content-Type: ' . $mimeType);
        }

        if (self::getContentDetector()->isGzipableMime($mimeType) && self::getContentDetector()->isGzip($stream)) {
            header('Content-Encoding: gzip');
        }

        try {
            $ranges = StreamRange::createFromRequest($stream, $request);
            $contentLength = 0;
            if (!empty($ranges)) {
                header('HTTP/1.1 206 Partial Content');
                foreach ($ranges as $range) {
                    $contentLength += (($range->getLastPos() - $range->getFirstPos()) + 1);
                }
                //@todo Content-Range for multiple ranges?
                header(
                    'Content-Range: bytes ' . $ranges[0]->getFirstPos() . '-' . $ranges[0]->getLastPos()
                        . '/' . $stream->getSize()
                );
            } else {
                $contentLength = $stream->getSize();
                header('HTTP/1.1 200 OK');
            }

            header("Content-Length: " . $contentLength);

            if (empty($ranges)) {
                while (!$stream->eof()) {
                    echo $stream->read(self::BYTES_BY_CYCLE);
                }
            } else {
                foreach ($ranges as $range) {
                    $pos = $range->getFirstPos();
                    $stream->seek($pos);
                    while ($pos <= $range->getLastPos()) {
                        $length = min((($range->getLastPos() - $pos) + 1), self::BYTES_BY_CYCLE);
                        echo $stream->read($length);
                        $pos += $length;
                    }
                }
            }
        } catch (StreamRangeException $e) {
            header('HTTP/1.1 416 Requested Range Not Satisfiable');
        }
    }

    private static function getContentDetector(): ContentDetector
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return ServiceManager::getServiceManager()->get(ContentDetector::class);
    }
}
