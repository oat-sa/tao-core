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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\tao\model\http;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Trait HttpRequestHelperTrait
 * @package oat\tao\model\http
 * @author Moyon Camille
 */
trait HttpRequestHelperTrait
{
    /**
     * Get the HTTP request
     *
     * @return ServerRequestInterface
     */
    abstract protected function getPsrRequest();

    /**
     * Retrieves all message header values.
     *
     * The keys represent the header name as it will be sent over the wire, and
     * each value is an array of strings associated with the header.
     *
     *     // Represent the headers as a string
     *     foreach ($message->getHeaders() as $name => $values) {
     *         echo $name . ": " . implode(", ", $values);
     *     }
     *
     *     // Emit headers iteratively:
     *     foreach ($message->getHeaders() as $name => $values) {
     *         foreach ($values as $value) {
     *             header(sprintf('%s: %s', $name, $value), false);
     *         }
     *     }
     *
     * While header names are not case-sensitive, getHeaders() will preserve the
     * exact case in which headers were originally specified.
     *
     * @return string[][] Returns an associative array of the message's headers. Each
     *     key MUST be a header name, and each value MUST be an array of strings
     *     for that header.
     */
    protected function getHeaders()
    {
        $headers = [];
        foreach ($this->getPsrRequest()->getHeaders() as $name => $values) {
            $headers[strtolower($name)] = (count($values) == 1) ? reset($values) : $values;
        }
        return $headers;
    }

    /**
     * Checks if a header exists by the given case-insensitive name.
     *
     * @param string $name Case-insensitive header field name.
     * @return bool Returns true if any header names match the given header
     *     name using a case-insensitive string comparison. Returns false if
     *     no matching header name is found in the message.
     */
    protected function hasHeader($name)
    {
        return $this->getPsrRequest()->hasHeader(strtolower($name));
    }

    /**
     * Retrieves a message header value by the given case-insensitive name.
     *
     * This method returns an array of all the header values of the given
     * case-insensitive header name.
     *
     * If the header does not appear in the message, this method MUST return an
     * empty array.
     *
     * @param string $name Case-insensitive header field name.
     * @param mixed $default Default value to return if the header does not exist.
     * @return string[] An array of string values as provided for the given
     *    header. If the header does not appear in the message, this method MUST
     *    return an empty array.
     */
    protected function getHeader($name, $default = null)
    {
        if ($this->hasHeader($name)) {
            return $this->getPsrRequest()->getHeader($name);
        } else {
            return $default;
        }
    }

    /**
     * Retrieve any parameters provided in the request body.
     *
     * If the request Content-Type is either application/x-www-form-urlencoded
     * or multipart/form-data, and the request method is POST, this method MUST
     * return the contents of $_POST.
     *
     * Otherwise, this method may return any results of deserializing
     * the request body content; as parsing returns structured content, the
     * potential types MUST be arrays or objects only. A null value indicates
     * the absence of body content.
     *
     * @return null|array|object The deserialized body parameters, if any.
     *     These will typically be an array or object.
     */
    protected function getPostParameters()
    {
        return (array) $this->getPsrRequest()->getParsedBody();
    }

    /**
     * Check if the HTTP request contains POST parameter $name
     *
     * @param $name
     * @return bool
     */
    protected function hasPostParameter($name)
    {
        return array_key_exists($name, $this->getPostParameters());
    }

    /**
     * Get the POST parameter $name
     *
     * @param $name
     * @param mixed $default Default value to return if the post parameter does not exist.
     * @return mixed
     */
    protected function getPostParameter($name, $default = null)
    {
        if ($this->hasPostParameter($name)) {
            return $this->getPostParameters()[$name];
        }
        return $default;
    }

    /**
     * Retrieve query string arguments.
     *
     * Retrieves the deserialized query string arguments, if any.
     *
     * Note: the query params might not be in sync with the URI or server
     * params. If you need to ensure you are only getting the original
     * values, you may need to parse the query string from `getUri()->getQuery()`
     * or from the `QUERY_STRING` server param.
     *
     * @return array
     */
    protected function getGetParameters()
    {
        return (array) $this->getPsrRequest()->getQueryParams();
    }

    /**
     * Check if the HTTP request contains GET parameter $name
     *
     * @param $name
     * @return bool
     */
    protected function hasGetParameter($name)
    {
        return array_key_exists($name, $this->getGetParameters());
    }

    /**
     * Get the GET parameter $name
     *
     * @param $name
     * @param mixed $default Default value to return if the get parameter does not exist.
     * @return bool|mixed Return the GET parameter or false if any
     */
    protected function getGetParameter($name, $default = null)
    {
        if ($this->hasGetParameter($name)) {
            return $this->getGetParameters()[$name];
        }
        return $default;
    }

    /**
     * Retrieve attributes derived from the request.
     *
     * The request "attributes" may be used to allow injection of any
     * parameters derived from the request: e.g., the results of path
     * match operations; the results of decrypting cookies; the results of
     * deserializing non-form-encoded message bodies; etc. Attributes
     * will be application and request specific, and CAN be mutable.
     *
     * @return array Attributes derived from the request.
     */
    protected function getAttributeParameters()
    {
        return (array) $this->getPsrRequest()->getAttributes();
    }

    /**
     * Check if the HTTP request contains attribute $name
     *
     * @param $name
     * @return bool
     */
    protected function hasAttributeParameter($name)
    {
        return array_key_exists($name, $this->getAttributeParameters());
    }

    /**
     * Retrieve a single derived request attribute.
     *
     * Retrieves a single derived request attribute as described in
     * getAttributes(). If the attribute has not been previously set, returns
     * the default value as provided.
     *
     * This method obviates the need for a hasAttribute() method, as it allows
     * specifying a default value to return if the attribute is not found.
     *
     * @see getAttributes()
     * @param string $name The attribute name.
     * @param mixed $default Default value to return if the attribute does not exist.
     * @return mixed
     */
    protected function getAttributeParameter($name, $default = null)
    {
        return $this->getPsrRequest()->getAttribute($name, $default);
    }

    /**
     * Retrieve cookies.
     *
     * Retrieves cookies sent by the client to the server.
     *
     * The data MUST be compatible with the structure of the $_COOKIE superglobal.
     *
     * @return array
     */
    protected function getCookieParams()
    {
        return (array) $this->getPsrRequest()->getCookieParams();
    }

    /**
     * Check if the given $name exists as a cookie
     *
     * @param $name
     * @return bool
     */
    protected function hasCookie($name)
    {
        return array_key_exists($name, $this->getCookieParams());
    }

    /**
     * Get the cookie associated to $name
     *
     * @param $name
     * @param mixed $default Default value to return if the cookie does not exist.
     * @return mixed
     */
    protected function getCookie($name, $default = null)
    {
        if ($this->hasCookie($name)) {
            return $this->getCookieParams()[$name];
        } else {
            return $default;
        }
    }

    /**
     * Get the HTTP request method
     *
     * @return string
     */
    protected function getRequestMethod()
    {
        return $this->getPsrRequest()->getMethod();
    }

    /**
     * Check if the HTTP request method is GET
     *
     * @return bool
     */
    protected function isRequestGet()
    {
        return $this->getRequestMethod() == 'GET';
    }

    /**
     * Check if the HTTP request method is POST
     *
     * @return bool
     */
    protected function isRequestPost()
    {
        return $this->getRequestMethod() == 'POST';
    }

    /**
     * Check if the HTTP request method is PUT
     *
     * @return bool
     */
    protected function isRequestPut()
    {
        return $this->getRequestMethod() == 'PUT';
    }

    /**
     * Check if the HTTP request method is DELETE
     *
     * @return bool
     */
    protected function isRequestDelete()
    {
        return $this->getRequestMethod() == 'DELETE';
    }

    /**
     * Check if the HTTP request method is HEAD
     *
     * @return bool
     */
    protected function isRequestHead()
    {
        return $this->getRequestMethod() == 'HEAD';
    }

    /**
     * Check if the current request is using AJAX
     *
     * @return bool
     */
    protected function isXmlHttpRequest()
    {
        $serverParams = $this->getPsrRequest()->getServerParams();
        if (isset($serverParams['HTTP_X_REQUESTED_WITH'])) {
            if (strtolower($serverParams['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                return true;
            }
        }
        return false;
    }

    /**
     * Get the user agent from HTTP request header "user-agent"
     *
     * @return string[]
     */
    protected function getUserAgent()
    {
        return $this->getPsrRequest()->getHeader('user-agent');
    }

    /**
     * Get the query string of HTTP request Uri
     *
     * @return string
     */
    protected function getQueryString()
    {
        return $this->getPsrRequest()->getUri()->getQuery();
    }

    /**
     * Get the request uri e.q. the HTTP request path
     *
     * @return string
     */
    protected function getRequestURI()
    {
        return $this->getPsrRequest()->getUri()->getPath();
    }

    /**
     * Get the content type from HTTP request header "content-type"
     *
     * @return string[]
     */
    protected function getContentType()
    {
        return $this->getPsrRequest()->getHeader('content-type');
    }
}
