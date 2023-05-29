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

use oat\tao\model\auth\AbstractAuthType;
use oat\tao\model\webhooks\WebhookAuthService;
use oat\tao\model\webhooks\WebHookClassService;
use oat\tao\model\webhooks\WebhookEventsService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class tao_actions_WebHooks extends tao_actions_SaSModule
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws common_Exception
     * @throws tao_models_classes_dataBinding_GenerisFormDataBindingException
     */
    public function addInstanceForm(): void
    {
        $this->setData('formTitle', __('Create instance'));
        $this->saveInstance();
    }

    /**
     * @throws core_kernel_persistence_Exception
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws tao_models_classes_MissingRequestParameterException
     * @throws common_Exception
     */
    public function authTpl(): void
    {
        $webhookAuthService = $this->getWebhookAuthService();

        /** @var AbstractAuthType $authType */
        $authType = null;
        $instance = null;

        $parsedBody = $this->getPsrRequest()->getParsedBody();

        if ($parsedBody['uri']) {
            $instance = $this->getCurrentInstance();
            $authType = $webhookAuthService->getAuthType(
                $instance->getOnePropertyValue($this->getProperty(WebHookClassService::PROPERTY_AUTH_TYPE))
            );

            $authType->setInstance($instance);
        } else {
            $authType = $webhookAuthService->getAuthType();
        }

        $webhookEventsService = $this->getWebhookEventsService();

        $setEvents = $instance
            ? $instance->getPropertyValues($this->getProperty(WebHookClassService::PROPERTY_WEBHOOK_EVENT))
            : [];

        $events = [];
        foreach ($webhookEventsService->getRegisteredEvents() as $eventClass => $enabled) {
            if ($enabled) {
                $eventLabel = substr($eventClass, strrpos($eventClass, '\\') + 1);
                $eventLabel = trim(preg_replace('/[A-Z]/', ' $0', $eventLabel));

                $events[$eventClass] = [
                    'label' => $eventLabel,
                    'set' => in_array($eventClass, $setEvents, true)
                ];
            }
        }

        $this->setData('events', $events);
        $this->setData('authType', $authType);
        $this->setData('allowedTypes', $webhookAuthService->getTypes());
        $this->setView('auth/form.tpl');

        $this->returnJson([
            'data' => $this->getRenderer()->render(),
            'success' => true,
        ]);

        // prevent further render
        $this->renderer = null;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws common_Exception
     * @throws tao_models_classes_MissingRequestParameterException
     * @throws tao_models_classes_dataBinding_GenerisFormDataBindingException
     */
    public function editInstance(): void
    {
        $this->setData('formTitle', __('Edit Instance'));
        $this->saveInstance($this->getCurrentInstance());
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getClassService(): WebHookClassService
    {
        return $this->getPsrContainer()->get(WebHookClassService::class);
    }

    /**
     * @param null $instance
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws common_Exception
     * @throws tao_models_classes_dataBinding_GenerisFormDataBindingException
     */
    public function saveInstance($instance = null): void
    {
        if (!tao_helpers_Request::isAjax()) {
            throw new InvalidArgumentException('wrong request mode');
        }

        $clazz = $this->getCurrentClass();
        $myFormContainer = new tao_actions_form_Instance($clazz, $instance);

        $myForm = $myFormContainer->getForm();

        if ($myForm && $myForm->isSubmited() && $myForm->isValid()) {
            $webhookAuthService = $this->getWebhookAuthService();
            $values = $myForm->getValues();

            $parsedBody = $this->getPsrRequest()->getParsedBody();

            $authType = $webhookAuthService->getAuthType(
                $this->getResource(
                    $parsedBody[tao_helpers_Uri::encode(WebHookClassService::PROPERTY_AUTH_TYPE)]
                )
            );

            // according to the auth type we need to add properties for the authenticator
            $values[WebHookClassService::PROPERTY_AUTH_TYPE] = $authType->getAuthClass()->getUri();
            foreach ($authType->getAuthProperties() as $authProperty) {
                $propertyUri = $authProperty->getUri();
                $values[$propertyUri] = $parsedBody[tao_helpers_Uri::encode($propertyUri)];
            }

            if ($parsedBody['events']) {
                $values[WebHookClassService::PROPERTY_WEBHOOK_EVENT] = [];
                foreach ($parsedBody['events'] as $eventClass => $eventLabel) {
                    $values[WebHookClassService::PROPERTY_WEBHOOK_EVENT][] = $eventClass;
                }
            }

            try {
                $this->getWebhookClassService()->saveWebhookInstance($values, $instance);

                $this->setData('message', __('Instance saved'));
            } catch (Exception $e) {
                $this->setData('message', __('Undefined Instance can not be saved'));
            }
            $this->setData('reload', true);
        }

        $this->setData('myForm', $myForm->render());
        $this->setView('form.tpl', 'tao');
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getWebhookAuthService(): WebhookAuthService
    {
        return $this->getPsrContainer()->get(WebhookAuthService::class);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getWebhookEventsService(): WebhookEventsService
    {
        return $this->getPsrContainer()->get(WebhookEventsService::class);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getWebhookClassService(): WebHookClassService
    {
        return $this->getPsrContainer()->get(WebHookClassService::class);
    }
}
