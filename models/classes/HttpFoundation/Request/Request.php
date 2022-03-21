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

use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class Request implements RequestInterface
{
    /** @var Request */
    private $request;

    public function __construct(SymfonyRequest $request)
    {
        $this->request = $request;
    }

    public function request(): SymfonyRequest
    {
        return $this->request;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key, $default = null)
    {
        return $this->request()->get($key, $default);
    }

    public function getAttributes(): array
    {
        return $this->request()->attributes->all();
    }

    /**
     * {@inheritdoc}
     */
    public function getAttribute(string $key, $default = null)
    {
        return $this->request()->attributes->get($key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function setAttribute(string $key, $value): void
    {
        $this->request()->attributes->set($key, $value);
    }

    public function getRequestParameters(): array
    {
        return $this->request()->request->all();
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestParameter(string $key, $default = null)
    {
        return $this->request()->request->get($key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function setRequestParameter(string $key, $value): void
    {
        $this->request()->request->set($key, $value);
    }

    public function getQueryParameters(): array
    {
        return $this->request()->query->all();
    }

    /**
     * {@inheritdoc}
     */
    public function getQueryParameter(string $key, $default = null)
    {
        return $this->request()->query->get($key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function setQueryParameter(string $key, $value): void
    {
        $this->request()->query->set($key, $value);
    }

    public function getServerParameters(): array
    {
        return $this->request()->server->all();
    }

    /**
     * {@inheritdoc}
     */
    public function getServerParameter(string $key, $default = null)
    {
        return $this->request()->server->get($key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function setServerParameter(string $key, $value): void
    {
        $this->request()->server->set($key, $value);
    }

    public function getFiles(): array
    {
        return $this->request()->files->all();
    }

    /**
     * {@inheritdoc}
     */
    public function getFile(string $key, $default = null)
    {
        return $this->request()->files->get($key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function setFile(string $key, $value): void
    {
        $this->request()->files->set($key, $value);
    }

    public function getCookies(): array
    {
        return $this->request()->cookies->all();
    }

    /**
     * {@inheritdoc}
     */
    public function getCookie(string $key, $default = null)
    {
        return $this->request()->cookies->get($key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function setCookie(string $key, $value): void
    {
        $this->request()->cookies->set($key, $value);
    }

    public function getHeaders(): array
    {
        return $this->request()->headers->all();
    }

    /**
     * {@inheritdoc}
     */
    public function getHeader(string $key, string $default = null): ?string
    {
        return $this->request()->headers->get($key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function setHeader(string $key, $values, bool $replace = true): void
    {
        $this->request()->headers->set($key, $values, $replace);
    }

    /**
     * {@inheritdoc}
     */
    public function getContent(bool $asResource = false)
    {
        return $this->request()->getContent($asResource);
    }
}
