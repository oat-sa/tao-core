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
 * Copyright (c) 2023-2025 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\webhooks;

use common_Exception;
use common_exception_Error;
use core_kernel_classes_Class as RdfClass;
use core_kernel_classes_Literal as Literal;
use core_kernel_classes_Property as Property;
use core_kernel_classes_Resource as Resource;
use oat\generis\model\data\Ontology;
use oat\generis\test\ServiceManagerMockTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use oat\tao\model\auth\BasicAuthType;
use oat\tao\model\auth\BasicType;
use oat\tao\model\webhooks\configEntity\Webhook;
use oat\tao\model\webhooks\configEntity\WebhookAuth;
use oat\tao\model\webhooks\configEntity\WebhookEntryFactory;
use oat\tao\model\webhooks\WebhookAuthService;
use oat\tao\model\webhooks\WebHookClassService;
use tao_models_classes_dataBinding_GenerisFormDataBinder;

class WebHookClassServiceTest extends TestCase
{
    use ServiceManagerMockTrait;

    private WebHookClassService|MockObject $webhookService;
    private WebhookEntryFactory|MockObject $webhookEntryFactory;

    protected function setUp(): void
    {
        $authService = $this->createMock(WebhookAuthService::class);
        $authType = $this->createMock(BasicAuthType::class);
        $authType->method('getAuthProperties')->willReturn([]);
        $authService->method('getAuthType')->willReturn($authType);

        $this->webhookEntryFactory = $this->createMock(WebhookEntryFactory::class);

        $this->webhookService = $this->createPartialMock(
            WebHookClassService::class,
            ['getDataBinder', 'getModel', 'getProperty', 'createInstance', 'getRootClass']
        );

        $serviceLocator = $this->getServiceManagerMock([
            WebhookEntryFactory::class => $this->webhookEntryFactory,
            WebhookAuthService::class => $authService,
        ]);

        $this->webhookService->setServiceLocator($serviceLocator);
    }

    /**
     * @throws common_exception_Error
     * @throws common_Exception
     */
    public function testSaveWebhookInstance(): void
    {
        $resource = new Resource('http://www.tao.lu/Ontologies/TAO.rdf#WebHook');

        $dataBinder = $this->createMock(tao_models_classes_dataBinding_GenerisFormDataBinder::class);
        $dataBinder->expects(self::once())->method('bind');
        $this->webhookService->method('getDataBinder')->willReturn($dataBinder);

        $this->webhookService->saveWebhookInstance([], $resource);
    }

    public function testGetRootClass(): void
    {
        $rootClass = (new WebHookClassService())->getRootClass();

        self::assertEquals('http://www.tao.lu/Ontologies/TAO.rdf#WebHook', $rootClass->getUri());
    }

    public function testGetWebhookByUri(): void
    {
        $this->webhookService->method('getProperty')->willReturn(
            new Property('https://property')
        );

        $ontology = $this->createMock(Ontology::class);
        $resource = $this->createMock(RdfClass::class);

        $whMethod = $this->createMock(Resource::class);
        $whMethod->method('getLabel')->willReturn('GET');

        $resource->method('getUri')->willReturn('http://resource-uri');
        $resource->method('getPropertiesValues')->willReturn([
            'http://www.tao.lu/Ontologies/TAO.rdf#WebHookUrl' => [new Literal('url')],
            'http://www.tao.lu/Ontologies/TAO.rdf#WebHookMethod' => [$whMethod],
            'http://www.tao.lu/Ontologies/TAO.rdf#WebHookRetry' => [new Literal('3')]
        ]);
        $ontology->method('getResource')->willReturn($resource);
        $this->webhookService->method('getModel')->willReturn($ontology);

        $this->webhookEntryFactory->expects(self::once())->method('createEntry');

        $this->webhookService->getWebhookByUri('http://some-uri');
    }

    public function testSaveWebhook(): void
    {
        $webHook = new Webhook(
            'id',
            'url',
            'GET',
            3,
            new WebhookAuth(BasicType::class, ['login' => 'login', 'password' => 'password']),
            true
        );

        $ontology = $this->createMock(Ontology::class);
        $ontology->method('getClass')->willReturn(new RdfClass('https://class-uri'));
        $this->webhookService->method('getModel')->willReturn($ontology);
        $this->webhookService->method('createInstance')->willReturn(new Resource('https://resource-uri'));

        $dataBinder = $this->createMock(tao_models_classes_dataBinding_GenerisFormDataBinder::class);
        $dataBinder->expects(self::once())->method('bind');
        $this->webhookService->method('getDataBinder')->willReturn($dataBinder);

        $this->webhookService->saveWebhook($webHook, []);
    }

    public function testFindWebhookByEventClass(): void
    {
        $property = $this->createMock(Property::class);
        $this->webhookService->method('getProperty')->willReturn($property);

        $rootClass = $this->createMock(RdfClass::class);

        $resource = $this->createMock(Resource::class);
        $resource->method('getPropertyValues')->willReturn(['eventName']);

        $rootClass->method('getInstances')->willReturn([$resource]);
        $this->webhookService->method('getRootClass')->willReturn($rootClass);

        $result = $this->webhookService->findWebhookByEventClass('eventName');

        self::assertCount(1, $result);
    }

    public function testGetWebhooks(): void
    {
        $this->webhookService->method('getProperty')->willReturn(
            new Property('https://property')
        );

        $whMethod = $this->createMock(Resource::class);
        $whMethod->method('getLabel')->willReturn('GET');

        $resource = $this->createMock(Resource::class);
        $resource->method('getUri')->willReturn('http://resource-uri');
        $resource->method('getPropertiesValues')->willReturn([
            'http://www.tao.lu/Ontologies/TAO.rdf#WebHookUrl' => [new Literal('url')],
            'http://www.tao.lu/Ontologies/TAO.rdf#WebHookMethod' => [$whMethod],
            'http://www.tao.lu/Ontologies/TAO.rdf#WebHookRetry' => [new Literal('3')]
        ]);

        $rootClass = $this->createMock(RdfClass::class);
        $rootClass->method('getInstances')->willReturn([$resource]);

        $this->webhookService->method('getRootClass')->willReturn($rootClass);


        $webhooks = $this->webhookService->getWebhooks();

        self::assertCount(1, $webhooks);
    }

    private function mockOntology(): Ontology
    {
        $property = $this->createMock(Property::class);
        $this->webhookService->method('getProperty')->willReturn($property);

        $ontology = $this->createMock(Ontology::class);
        $this->webhookService->method('getModel')->willReturn($ontology);

        return $ontology;
    }
}
