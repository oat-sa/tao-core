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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\Request;

interface RequestInterface
{
    public function request(): Request;

    /**
     * @param null|mixed $default
     *
     * @return mixed
     */
    public function get(string $key, $default = null);

    public function getAttributes(): array;

    /**
     * @param mixed $default
     *
     * @return mixed
     */
    public function getAttribute(string $key, $default = null);

    /**
     * @param mixed $value
     */
    public function setAttribute(string $key, $value): void;

    public function getRequestParameters(): array;

    /**
     * @param string|int|float|bool|null $default
     *
     * @return string|int|float|bool|null
     */
    public function getRequestParameter(string $key, $default = null);

    /**
     * @param string|int|float|bool|array|null $value
     */
    public function setRequestParameter(string $key, $value): void;

    public function getQueryParameters(): array;

    /**
     * @param string|int|float|bool|null $default
     *
     * @return string|int|float|bool|null
     */
    public function getQueryParameter(string $key, $default = null);

    /**
     * @param string|int|float|bool|array|null $value
     */
    public function setQueryParameter(string $key, $value): void;

    public function getServerParameters(): array;

    /**
     * @param null|mixed $default
     *
     * @return mixed
     */
    public function getServerParameter(string $key, $default = null);

    /**
     * @param mixed $value
     */
    public function setServerParameter(string $key, $value): void;

    public function getFiles(): array;

    /**
     * @param null|mixed $default
     *
     * @return mixed
     */
    public function getFile(string $key, $default = null);

    /**
     * @param mixed $value
     */
    public function setFile(string $key, $value): void;

    public function getCookies(): array;

    /**
     * @param string|int|float|bool|null $default
     *
     * @return string|int|float|bool|null
     */
    public function getCookie(string $key, $default = null);

    /**
     * @param string|int|float|bool|array|null $value
     */
    public function setCookie(string $key, $value): void;

    public function getHeaders(): array;

    /**
     * @return string|null
     */
    public function getHeader(string $key, string $default = null): ?string;

    /**
     * @param string|string[]|null $values
     */
    public function setHeader(string $key, $values, bool $replace = true): void;

    /**
     * @return string|resource
     */
    public function getContent(bool $asResource = false);
}
