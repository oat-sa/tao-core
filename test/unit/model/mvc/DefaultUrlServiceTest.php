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
 * Foundation, Inc., 31 Milk St # 960789 Boston, MA 02196 USA.
 *
 * Copyright (c) 2026 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\mvc;

use oat\tao\model\DynamicConfig\DynamicConfigProviderInterface;
use oat\tao\model\mvc\DefaultUrlService;
use PHPUnit\Framework\TestCase;

class DefaultUrlServiceTest extends TestCase
{
    public function testGetRootEntryUrlReturnsPlatformUrlWhenConfigured(): void
    {
        $provider = $this->createMock(DynamicConfigProviderInterface::class);
        $provider->expects($this->once())
            ->method('getConfigByName')
            ->with(DynamicConfigProviderInterface::PLATFORM_URL_CONFIG_NAME)
            ->willReturn('https://example.com/portal');

        $service = new TestableDefaultUrlService($provider, [
            'login' => [
                'ext' => 'tao',
                'controller' => 'Main',
                'action' => 'login',
            ],
        ]);

        $this->assertSame('https://example.com/portal', $service->getRootEntryUrl(true));
    }

    public function testGetRootEntryUrlFallsBackToLoginForAnonymousUser(): void
    {
        $provider = $this->createMock(DynamicConfigProviderInterface::class);
        $provider->expects($this->exactly(2))
            ->method('getConfigByName')
            ->willReturnMap([
                [DynamicConfigProviderInterface::PLATFORM_URL_CONFIG_NAME, null],
                [DynamicConfigProviderInterface::LOGIN_URL_CONFIG_NAME, null],
            ]);

        $service = new TestableDefaultUrlService($provider, [
            'login' => [
                'ext' => 'tao',
                'controller' => 'Main',
                'action' => 'login',
            ],
        ]);

        $this->assertSame(_url('login', 'Main', 'tao'), $service->getRootEntryUrl(true));
    }

    public function testGetRootEntryUrlFallsBackToTaoEntryForAuthenticatedUser(): void
    {
        $provider = $this->createMock(DynamicConfigProviderInterface::class);
        $provider->expects($this->once())
            ->method('getConfigByName')
            ->with(DynamicConfigProviderInterface::PLATFORM_URL_CONFIG_NAME)
            ->willReturn(null);

        $service = new TestableDefaultUrlService($provider, [
            'login' => [
                'ext' => 'tao',
                'controller' => 'Main',
                'action' => 'login',
            ],
        ]);

        $this->assertSame(_url('entry', 'Main', 'tao'), $service->getRootEntryUrl(false));
    }

    public function testGetResolverExceptionRedirectUrlReturnsPlatformUrlForAnonymousUser(): void
    {
        $provider = $this->createMock(DynamicConfigProviderInterface::class);
        $provider->expects($this->once())
            ->method('getConfigByName')
            ->with(DynamicConfigProviderInterface::PLATFORM_URL_CONFIG_NAME)
            ->willReturn('https://example.com/portal');

        $service = new TestableDefaultUrlService($provider, [
            'login' => [
                'ext' => 'tao',
                'controller' => 'Main',
                'action' => 'login',
            ],
        ]);

        $this->assertSame('https://example.com/portal', $service->getResolverExceptionRedirectUrl(true));
    }

    public function testGetResolverExceptionRedirectUrlFallsBackToLoginForAnonymousUser(): void
    {
        $provider = $this->createMock(DynamicConfigProviderInterface::class);
        $provider->expects($this->exactly(2))
            ->method('getConfigByName')
            ->willReturnMap([
                [DynamicConfigProviderInterface::PLATFORM_URL_CONFIG_NAME, null],
                [DynamicConfigProviderInterface::LOGIN_URL_CONFIG_NAME, null],
            ]);

        $service = new TestableDefaultUrlService($provider, [
            'login' => [
                'ext' => 'tao',
                'controller' => 'Main',
                'action' => 'login',
            ],
        ]);

        $this->assertSame(_url('login', 'Main', 'tao'), $service->getResolverExceptionRedirectUrl(true));
    }

    public function testGetResolverExceptionRedirectUrlFallsBackToTaoEntryForAuthenticatedUser(): void
    {
        $provider = $this->createMock(DynamicConfigProviderInterface::class);
        $provider->expects($this->never())->method('getConfigByName');

        $service = new TestableDefaultUrlService($provider, [
            'login' => [
                'ext' => 'tao',
                'controller' => 'Main',
                'action' => 'login',
            ],
        ]);

        $this->assertSame(_url('entry', 'Main', 'tao'), $service->getResolverExceptionRedirectUrl(false));
    }
}

class TestableDefaultUrlService extends DefaultUrlService
{
    private DynamicConfigProviderInterface $dynamicConfigProvider;

    public function __construct(DynamicConfigProviderInterface $dynamicConfigProvider, array $options = [])
    {
        parent::__construct($options);
        $this->dynamicConfigProvider = $dynamicConfigProvider;
    }

    protected function getDynamicConfigProvider(): DynamicConfigProviderInterface
    {
        return $this->dynamicConfigProvider;
    }
}
