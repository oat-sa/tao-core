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
use common_exception_Error;
use common_exception_InvalidArgumentType;
use core_kernel_classes_Class;
use core_kernel_classes_Resource;
use core_kernel_persistence_Exception;
use Laminas\ServiceManager\ServiceLocatorAwareInterface;
use Laminas\ServiceManager\ServiceLocatorAwareTrait;
use oat\tao\model\auth\AuthUriClassMapper;
use oat\tao\model\auth\BasicAuth;
use oat\tao\model\auth\BasicAuthCredentials;
use oat\tao\model\OntologyClassService;
use oat\tao\model\webhooks\configEntity\WebhookAuth;
use oat\tao\model\webhooks\configEntity\WebhookEntryFactory;
use oat\tao\model\webhooks\configEntity\WebhookInterface;
use tao_models_classes_dataBinding_GenerisFormDataBinder as DataBinder;

class WebHookClassService extends OntologyClassService implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    public const CLASS_URI = 'http://www.tao.lu/Ontologies/TAO.rdf#WebHook';
    public const PROPERTY_AUTH_TYPE = 'http://www.tao.lu/Ontologies/TAO.rdf#WebHookAuthType';
    public const PROPERTY_WEB_HOOK_URL = 'http://www.tao.lu/Ontologies/TAO.rdf#WebHookUrl';
    public const PROPERTY_WEB_HOOK_METHOD = 'http://www.tao.lu/Ontologies/TAO.rdf#WebHookMethod';
    public const PROPERTY_WEB_HOOK_RETRY = 'http://www.tao.lu/Ontologies/TAO.rdf#WebHookRetry';
    public const PROPERTY_RESPONSE_VALIDATION = 'http://www.tao.lu/Ontologies/TAO.rdf#WebHookResponseValidation';
    public const PROPERTY_WEBHOOK_EVENT = 'http://www.tao.lu/Ontologies/TAO.rdf#WebhookEvent';
    public const RDF_HTTP_METHOD_POST = 'http://www.tao.lu/Ontologies/TAO.rdf#HTTPMethodPOST';
    public const RDF_HTTP_METHOD_GET = 'http://www.tao.lu/Ontologies/TAO.rdf#HTTPMethodGET';
    public const RDF_COMPLY_ENABLED = 'http://www.tao.lu/Ontologies/TAO.rdf#ComplyEnabled';


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

        return $this->rdfInstanceToEntity($webhookClass);
    }

    public function findWebhookByEventClass(string $eventClassName): array
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

    /**
     * @throws core_kernel_persistence_Exception
     * @throws common_exception_InvalidArgumentType
     * @throws common_Exception
     */
    public function getWebhooks(): array
    {
        $rootClass = $this->getRootClass();
        $webhookInstances = $rootClass->getInstances(false);

        $result = [];
        foreach ($webhookInstances as $webhookInstance) {
            $result[] = $this->rdfInstanceToEntity($webhookInstance);
        }

        return $result;
    }

    /**
     * @throws common_exception_Error
     * @throws common_Exception
     */
    public function saveWebhook(WebhookInterface $webhook, array $events): void
    {
        $values = [
            self::PROPERTY_WEB_HOOK_URL => $webhook->getUrl(),
            self::PROPERTY_WEB_HOOK_RETRY => (string)$webhook->getMaxRetries(),
            self::PROPERTY_WEB_HOOK_METHOD => $webhook->getHttpMethod() === 'POST'
                ? self::RDF_HTTP_METHOD_POST
                : self::RDF_HTTP_METHOD_GET
        ];

        if ($webhook->getResponseValidationEnable()) {
            $values[self::PROPERTY_RESPONSE_VALIDATION] =
                self::RDF_COMPLY_ENABLED;
        }

        $uriClassMapper = new AuthUriClassMapper();
        $authTypeUri = $uriClassMapper->getUriByClass($webhook->getAuth()->getAuthClass());
        $values[self::PROPERTY_AUTH_TYPE] = $authTypeUri;

        if ($authTypeUri === BasicAuth::CLASS_BASIC_AUTH) {
            $credentials = $webhook->getAuth()->getCredentials();
            $values[BasicAuth::PROPERTY_LOGIN] = $credentials[BasicAuthCredentials::LOGIN];
            $values[BasicAuth::PROPERTY_PASSWORD] = $credentials[BasicAuthCredentials::PASSWORD];
        }

        if (!empty($events)) {
            $values[self::PROPERTY_WEBHOOK_EVENT] = [];
            foreach ($events as $eventClass) {
                $values[self::PROPERTY_WEBHOOK_EVENT][] = $eventClass;
            }
        }

        $this->saveWebhookInstance($values);
    }

    /**
     * @throws common_exception_Error
     * @throws common_Exception
     */
    public function saveWebhookInstance(array $parameters, ?core_kernel_classes_Resource $instance = null): void
    {
        if (!$instance) {
            $currentClass = $this->getCurrentClass();

            $instance = $this->createInstance($currentClass);
        }

        // save properties
        $this->getDataBinder($instance)->bind($parameters);
    }

    protected function getCurrentClass(): ?core_kernel_classes_Class
    {
        return $this->getModel()->getClass(self::CLASS_URI);
    }

    protected function getDataBinder(core_kernel_classes_Resource $targetInstance): DataBinder
    {
        return new DataBinder($targetInstance);
    }

    /**
     * @throws core_kernel_persistence_Exception
     * @throws common_Exception
     */
    private function getAuth(core_kernel_classes_Resource $webhookClass): WebhookAuth
    {
        /** @var WebhookAuthService $webhookAuthService */
        $webhookAuthService = $this->getServiceLocator()->getContainer()->get(WebhookAuthService::class);
        $authType = $webhookAuthService->getAuthType(
            $webhookClass->getOnePropertyValue($this->getProperty(self::PROPERTY_AUTH_TYPE))
        );

        $authProperties = $authType->getAuthProperties();
        $extractedAuthProperties = [];
        foreach ($authProperties as $authProperty) {
            $propertyLabel = strtolower($authProperty->getLabel());
            $extractedAuthProperties[$propertyLabel] = (string)$webhookClass->getOnePropertyValue(
                $this->getProperty($authProperty->getUri())
            );
        }

        return $this->getWebhookEntryFactory()->createAuthEntry(get_class($authType), $extractedAuthProperties);
    }

    /**
     * @param core_kernel_classes_Resource $webhookResource
     *
     * @return WebhookInterface
     *
     * @throws common_Exception
     * @throws common_exception_InvalidArgumentType
     * @throws core_kernel_persistence_Exception
     */
    private function rdfInstanceToEntity(core_kernel_classes_Resource $webhookResource): WebhookInterface
    {
        $properties = $webhookResource->getPropertiesValues([
            self::PROPERTY_WEB_HOOK_URL,
            self::PROPERTY_WEB_HOOK_METHOD,
            self::PROPERTY_WEB_HOOK_RETRY,
            self::PROPERTY_AUTH_TYPE,
            self::PROPERTY_RESPONSE_VALIDATION
        ]);

        $responseValidation = empty($properties[self::PROPERTY_RESPONSE_VALIDATION])
            ? ''
            : $properties[self::PROPERTY_RESPONSE_VALIDATION][0]->getLabel();

        return $this->getWebhookEntryFactory()->createEntry(
            $webhookResource->getUri(),
            $properties[self::PROPERTY_WEB_HOOK_URL][0]->__toString(),
            $properties[self::PROPERTY_WEB_HOOK_METHOD][0]->getLabel(),
            (int)$properties[self::PROPERTY_WEB_HOOK_RETRY][0]->__toString(),
            $this->getAuth($webhookResource),
            $responseValidation === 'Enable',
        );
    }

    private function getWebhookEntryFactory(): WebhookEntryFactory
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(WebhookEntryFactory::class);
    }

    private function getWebhookAuthService(): WebhookAuthService
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(WebhookAuthService::class);
    }
}
