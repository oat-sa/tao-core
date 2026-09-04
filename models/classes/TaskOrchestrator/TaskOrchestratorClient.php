<?php

declare(strict_types=1);

namespace oat\tao\model\TaskOrchestrator;

use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use Psr\SimpleCache\CacheInterface;
use RuntimeException;

class TaskOrchestratorClient
{
    private string $baseUrl;
    private string $authServerUri;
    private string $clientId;
    private string $clientSecret;
    private GuzzleHttpClient $httpClient;
    private ?string $accessToken = null;
    private ?CacheInterface $cache = null;

    public function __construct(
        string $baseUrl,
        string $authServerUri,
        string $clientId,
        string $clientSecret,
        GuzzleHttpClient $httpClient,
        ?CacheInterface $cache = null
    ) {
        $this->baseUrl = $baseUrl;
        $this->authServerUri = $authServerUri;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->httpClient = $httpClient;
        $this->cache = $cache;
    }

    private function getAccessToken(): string
    {
        $cacheKey = 'task_orchestrator_access_token_' . md5($this->clientId);
        if ($this->cache && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        try {
            $response = $this->httpClient->request('POST', $this->authServerUri . '/v1/oauth2/tokens', [
                'headers' => ['Content-Type' => 'application/json'],
                'json' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            $accessToken = $data['access_token'] ?? null;
            $expiresIn = $data['expires_in'] ?? 3600;

            if (!$accessToken) {
                throw new RuntimeException('Failed to obtain access_token from the authorization server.');
            }

            if ($this->cache) {
                $this->cache->set($cacheKey, $accessToken, $expiresIn - 60);
            }
            $this->accessToken = $accessToken;

            return $accessToken;
        } catch (GuzzleException $e) {
            throw new RuntimeException(
                'Authorization server communication error: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }

    public function sendJob(string $jobId, array $jobPayload): array
    {
        $accessToken = $this->getAccessToken();
        $url = sprintf('%s/api/v1/jobs/%s', $this->baseUrl, $jobId);

        try {
            $response = $this->httpClient->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => $jobPayload,
            ]);

            $statusCode = $response->getStatusCode();
            $responseBody = json_decode($response->getBody()->getContents(), true);

            if ($statusCode === 400 && ($responseBody['error'] ?? null) === 'Invalid request') {
                throw new InvalidArgumentException(
                    'TO API: request validation failed: ' . json_encode($responseBody)
                );
            }
            if ($statusCode === 400 && ($responseBody['message'] ?? null) === 'Missing token') {
                throw new RuntimeException('TO API: missing or invalid authorization token.');
            }
            if ($statusCode >= 400) {
                throw new RuntimeException(sprintf(
                    'TO API: unexpected HTTP %d: %s',
                    $statusCode,
                    json_encode($responseBody)
                ));
            }

            return $responseBody;
        } catch (GuzzleException $e) {
            throw new RuntimeException(
                'Task Orchestrator API communication error: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }
}
