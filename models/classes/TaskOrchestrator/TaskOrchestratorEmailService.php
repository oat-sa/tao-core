<?php
declare(strict_types=1);

namespace oat	ao\models\TaskOrchestrator;

use oat	ao\models\TaskOrchestrator\TaskOrchestratorClient;
use Ramsey\Uuid\Uuid; // Wymaga instalacji np. composer require ramsey/uuid

class TaskOrchestratorEmailService
{
    private TaskOrchestratorClient $client;
    private string $tenantId;
    private string $actorLogin;

    public function __construct(
        TaskOrchestratorClient $client,
        string $tenantId,
        string $actorLogin
    ) {
        $this->client = $client;
        $this->tenantId = $tenantId;
        $this->actorLogin = $actorLogin;
    }

    public function sendEmail(string $templateId, string $recipientUserLogin, array $templateData = []): string
    {
        $jobId = Uuid::uuid4()->toString(); // Generowanie unikalnego jobId

        $jobPayload = [
            'type' => 'portalEmailNotification',
            'tenantId' => $this->tenantId,
            'status' => 'initial',
            'progress' => 0,
            'user' => [
                'id' => sprintf('%s_%s', $this->tenantId, $this->actorLogin),
                'login' => $this->actorLogin,
            ],
            'email' => [
                'templateId' => $templateId,
                'recipientUserLogin' => $recipientUserLogin,
                'data' => $templateData,
            ],
            'meta' => [
                'labelKey' => 'tao_backoffice_email',
            ],
        ];

        // Wysyłamy joba do Task Orchestrator
        $response = $this->client->sendJob($jobId, $jobPayload);

        // Tutaj można dodać logowanie response
        // error_log(sprintf('TO Email Job sent: %s, Response: %s', $jobId, json_encode($response)));

        return $jobId; // Zwracamy jobId dla celów diagnostycznych
    }
}
