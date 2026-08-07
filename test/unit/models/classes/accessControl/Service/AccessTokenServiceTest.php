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
 * Copyright (c) 2026 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\accessControl\Service;

use DateTimeImmutable;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\JwtFacade;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use oat\oatbox\cache\NoCache;
use oat\oatbox\user\BasicUser;
use oat\oatbox\user\UserService;
use oat\tao\model\accessControl\Service\AccessTokenService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class AccessTokenServiceTest extends TestCase
{
    private const ENV_AUTH_URI = 'http://auth-service:8080';
    private const ENV_CLIENT_ID = 'test-client-id';
    private const ENV_CLIENT_SECRET = 'test-client-secret';
    private const TENANT_ID = 'test-tenant-id';
    private AccessTokenService $sut;
    private UserService&MockObject $userService;
    private ClientInterface&MockObject $client;

    /**
     * @before
     */
    public function init(): void
    {
        $this->userService = $this->createMock(UserService::class);
        $this->client = $this->createMock(ClientInterface::class);
        $this->sut = new AccessTokenService(
            $this->userService,
            new NoCache(),
            client: $this->client,
        );
    }

    /**
     * @runInSeparateProcess
     */
    public function testFetchTokensFailsWithoutConfiguration(): void
    {
        $this->expectExceptionObject(new RuntimeException('OAuth2 credentials not found.', 404));
        $this->sut->fetchTokens();
    }

    /**
     * @runInSeparateProcess
     */
    public function testFetchTokensFailsWhenClientReportsErrors(): void
    {
        $exception = $this->createMock(GuzzleException::class);
        $this->expectExceptionObject(new RuntimeException('Failed to fetch Auth tokens.', 424, $exception));
        $user = new BasicUser('identifier', [], 'username');

        $this->initEnvVars();
        $this->client
            ->expects($this->once())
            ->method('send')
            ->willThrowException($exception);
        $this->userService
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($user);
        $this->sut->fetchTokens();
    }

    /**
     * @runInSeparateProcess
     */
    public function testFetchTokensFailsWhenClientResponseIsEmpty(): void
    {
        $exception = $this->createMock(GuzzleException::class);
        $this->expectExceptionObject(new RuntimeException('Failed to fetch Auth tokens.', 424, $exception));
        $user = new BasicUser('identifier', [], 'username');

        $this->initEnvVars();
        $this->client
            ->expects($this->once())
            ->method('send')
            ->willReturn(new Response(200, [], ''));
        $this->userService
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($user);
        $this->sut->fetchTokens();
    }

    /**
     * @runInSeparateProcess
     */
    public function testFetchTokensFailsWhenClientResponseIsUnexpected(): void
    {
        $exception = $this->createMock(GuzzleException::class);
        $this->expectExceptionObject(new RuntimeException('Failed to fetch Auth tokens.', 424, $exception));
        $user = new BasicUser('identifier', [], 'username');

        $this->initEnvVars();
        $this->client
            ->expects($this->once())
            ->method('send')
            ->willReturn(new Response(301, [], 'Location: somewhere-else'));
        $this->userService
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($user);
        $this->sut->fetchTokens();
    }

    /**
     * @runInSeparateProcess
     */
    public function testFetchTokensFailsWhenAccessTokenHasMismatchingTenantId(): void
    {
        $this->expectExceptionObject(new RuntimeException('Not found', 404));
        $userId = 'username';
        $user = new BasicUser('identifier', [], $userId);

        $this->initEnvVars();
        $this->client
            ->expects($this->once())
            ->method('send')
            ->willReturn(
                new Response(200, [], json_encode([
                    'access_token' => $this->generateAccessToken($userId, 'another-tenant-id')
                ]))
            );
        $this->userService
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($user);
        $this->sut->fetchTokens();
    }

    /**
     * @runInSeparateProcess
     */
    public function testFetchTokensFailsWhenAccessTokenHasEmptyTenantId(): void
    {
        $this->expectExceptionObject(new RuntimeException('Unauthorized', 401));
        $userId = 'username';
        $user = new BasicUser('identifier', [], $userId);

        $this->initEnvVars();
        $this->client
            ->expects($this->once())
            ->method('send')
            ->willReturn(
                new Response(200, [], json_encode([
                    'access_token' => $this->generateAccessToken($userId, '')
                ]))
            );
        $this->userService
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($user);
        $this->sut->fetchTokens();
    }

    /**
     * @runInSeparateProcess
     */
    public function testFetchTokens(): void
    {
        $userId = 'username';
        $user = new BasicUser('identifier', [], $userId);

        $this->initEnvVars();
        $expected = ['access_token' => $this->generateAccessToken($userId)];
        $this->client
            ->expects($this->once())
            ->method('send')
            ->willReturn(new Response(200, [], json_encode($expected)));
        $this->userService
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($user);
        $this->assertSame($expected, $this->sut->fetchTokens());
    }

    private function generateAccessToken(string $userId, string $tenantId = self::TENANT_ID): string
    {
        $key = InMemory::base64Encoded(bin2hex(random_bytes(32)));
        return (new JwtFacade())->issue(
            new Sha256(),
            $key,
            static fn(
                Builder $builder,
                DateTimeImmutable $issuedAt,
            ): Builder => $builder
                ->issuedBy('https://backoffice.ngs.test')
                ->expiresAt($issuedAt->modify('+1 minute'))
                ->withClaim('user', ['login' => $userId])
                ->withClaim('tenant_id', $tenantId)
        )->toString();
    }

    private function initEnvVars(): void
    {
        $_ENV['ENV_AUTH_URI'] = self::ENV_AUTH_URI;
        $_ENV['ENV_CLIENT_ID'] = self::ENV_CLIENT_ID;
        $_ENV['ENV_CLIENT_SECRET'] = self::ENV_CLIENT_SECRET;
        $_ENV['TENANT_ID'] = self::TENANT_ID;
    }
}
