<?php
declare(strict_types=1);

namespace oat	ao\models\TaskOrchestrator;

use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Exception\GuzzleException;
use Psr\SimpleCache\CacheInterface; // Przykład dla cache'owania tokena

class TaskOrchestratorClient
{
    private string $baseUrl;
    private string $authServerUri;
    private string $clientId;
    private string $clientSecret;
    private GuzzleHttpClient $httpClient;
    private ?string $accessToken = null; // Caching token
    private ?CacheInterface $cache = null; // Opcjonalny cache

    public function __construct(
        string $baseUrl,
        string $authServerUri,
        string $clientId,
        string $clientSecret,
        GuzzleHttpClient $httpClient,
        ?CacheInterface $cache = null // Wstrzyknięcie cache
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
            $expiresIn = $data['expires_in'] ?? 3600; // Domyślnie 1h

            if (!$accessToken) {
                throw new \RuntimeException('Nie udało się uzyskać access_token z serwera autoryzacji.');
            }

            if ($this->cache) {
                $this->cache->set($cacheKey, $accessToken, $expiresIn - 60); // Cache z zapasem
            }
            $this->accessToken = $accessToken;
            return $accessToken;
        } catch (GuzzleException $e) {
            throw new \RuntimeException('Błąd komunikacji z serwerem autoryzacji: ' . $e->getMessage(), 0, $e);
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
                throw new \InvalidArgumentException('TO API: Błąd walidacji żądania: ' . json_encode($responseBody));
            }
            if ($statusCode === 400 && ($responseBody['message'] ?? null) === 'Missing token') {
                throw new \RuntimeException('TO API: Brak tokena autoryzacji w żądaniu lub token nieprawidłowy.');
            }
            if ($statusCode >= 400) { // Ogólne błędy HTTP
                throw new \RuntimeException(sprintf('TO API: Nieoczekiwany błąd HTTP %d: %s', $statusCode, json_encode($responseBody)));
            }

            return $responseBody; // Oczekiwane: { "status": "ok" }
        } catch (GuzzleException $e) {
            throw new \RuntimeException('Błąd komunikacji z Task Orchestrator API: ' . $e->getMessage(), 0, $e);
        }
    }
}
