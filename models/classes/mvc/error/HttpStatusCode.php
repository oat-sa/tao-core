<?php

namespace oat\tao\model\mvc\error;

/**
 * HTTP status code constants (replacement for Slim\Http\StatusCode removed in Slim 4).
 */
final class HttpStatusCode
{
    public const HTTP_OK = 200;
    public const HTTP_CREATED = 201;
    public const HTTP_BAD_REQUEST = 400;
    public const HTTP_FORBIDDEN = 403;
    public const HTTP_NOT_FOUND = 404;
    public const HTTP_METHOD_NOT_ALLOWED = 405;
    public const HTTP_INTERNAL_SERVER_ERROR = 500;
    public const HTTP_NOT_IMPLEMENTED = 501;
}
