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
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\webhooks;

use common_Exception;
use core_kernel_classes_Class;
use core_kernel_classes_Property as Property;
use core_kernel_persistence_Exception;
use Laminas\ServiceManager\ServiceLocatorAwareTrait;
use oat\generis\model\data\Ontology;
use oat\tao\model\OntologyClassService;
use oat\tao\model\webhooks\configEntity\Webhook;
use oat\tao\model\webhooks\configEntity\WebhookAuth;
use oat\tao\model\webhooks\configEntity\WebhookInterface;

class WebHookClassService extends OntologyClassService
{
    use ServiceLocatorAwareTrait;

    public const CLASS_URI = 'http://www.tao.lu/Ontologies/TAO.rdf#WebHook';
    public const PROPERTY_AUTH_TYPE = 'http://www.tao.lu/Ontologies/TAO.rdf#WebHookAuthType';
    public const PROPERTY_WEB_HOOK_URL = 'http://www.tao.lu/Ontologies/TAO.rdf#WebHookUrl';
    public const PROPERTY_WEB_HOOK_METHOD = 'http://www.tao.lu/Ontologies/TAO.rdf#WebHookMethod';
    public const PROPERTY_WEB_HOOK_RETRY = 'http://www.tao.lu/Ontologies/TAO.rdf#WebHookRetry';
    public const PROPERTY_RESPONSE_VALIDATION = 'http://www.tao.lu/Ontologies/TAO.rdf#WebHookResponseValidation';
    public const PROPERTY_WEBHOOK_EVENT = 'http://www.tao.lu/Ontologies/TAO.rdf#WebhookEvent';


    private ?Ontology $ontology;

    public function getRootClass(): core_kernel_classes_Class
    {
        return new core_kernel_classes_Class(self::CLASS_URI);
    }

    /**
     * @throws core_kernel_persistence_Exception
     * @throws common_Exception
     */
    public function getWebhookByUri(string $uri): ?WebhookInterface
    {
        $webhookClass = $this->getModel()->getResource($uri);

        if ($webhookClass === null) {
            return null;
        }

        $responseValidation = $webhookClass->getOnePropertyValue(
            $this->getProperty(self::PROPERTY_RESPONSE_VALIDATION)
        )->getLabel();

        /** @var WebhookAuthService $webhookAuthService */
        $webhookAuthService = $this->getServiceLocator()->get(WebhookAuthService::SERVICE_ID);
        $authType = $webhookAuthService->getAuthType(
            $webhookClass->getOnePropertyValue($this->getProperty(self::PROPERTY_AUTH_TYPE))
        );

        $authProperties = $authType->getAuthProperties();
        $extractedAuthProperties = [];
        foreach ($authProperties as $authProperty) {
            $propertyLabel = strtolower($authProperty->getLabel());
            $extractedAuthProperties[$propertyLabel] = $webhookClass->getOnePropertyValue(
                $this->getProperty($authProperty->getUri())
            );
        }

        $webHookAuth = new WebhookAuth(
            get_class($authType),
            $extractedAuthProperties
        );

        return new Webhook(
            $uri,
            $uri,
            $webhookClass->getOnePropertyValue(new Property(self::PROPERTY_WEB_HOOK_METHOD))->getLabel(),
            (int)$webhookClass->getOnePropertyValue(new Property(self::PROPERTY_WEB_HOOK_RETRY))->__toString(),
            $webHookAuth,
            $responseValidation === 'Enable'
        );
    }

    public function getWebhookByEventClass(string $eventClassName): array
    {
        $rootClass = $this->getRootClass();
        $webhookInstances = $rootClass->getInstances(false);

        $result = [];

        foreach ($webhookInstances as $webhookInstance) {
            $events = $webhookInstance->getPropertyValues($this->getProperty(self::PROPERTY_WEBHOOK_EVENT));
            if (in_array($eventClassName, $events, true)) {
                $result[] = $webhookInstance;
            }
        }

        return $result;
    }
}
