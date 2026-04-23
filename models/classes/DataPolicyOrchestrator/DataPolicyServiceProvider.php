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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\model\DataPolicyOrchestrator;

use oat\oatbox\log\LoggerService;
use oat\tao\model\Observer\GCP\PubSubClientFactory;
use oat\generis\model\DependencyInjection\ContainerServiceProviderInterface;
use oat\tao\model\DataPolicyOrchestrator\PubSub\Listener\DataRemovalListener;
use oat\tao\model\DataPolicyOrchestrator\PubSub\Listener\DataRemovalCheckListener;
use oat\tao\model\DataPolicyOrchestrator\PubSub\Publisher\DataRemovalConfirmationPublisher;
use oat\tao\model\DataPolicyOrchestrator\Handler\DataRemovalHandlerProxy;
use oat\tao\model\DataPolicyOrchestrator\Handler\FullDataRemovalHandlerProxy;
use oat\tao\model\DataPolicyOrchestrator\Handler\FullDataRemovalHandler;
use oat\tao\model\DataPolicyOrchestrator\Handler\DataRemovalHandler;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use tao_models_classes_UserService;

use function Symfony\Component\DependencyInjection\Loader\Configurator\env;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

// phpcs:disable Generic.Files.LineLength
class DataPolicyServiceProvider implements ContainerServiceProviderInterface
{
    private const PARAM_REMOVAL_SUBSCRIPTION_DEFAULT = 'DATA_POLICY_REMOVAL_SUBSCRIPTION_DEFAULT';
    private const PARAM_FULL_REMOVAL_CONFIRMATION_SUBSCRIPTION_DEFAULT = 'DATA_POLICY_REMOVAL_CONFIRMATION_SUBSCRIPTION_DEFAULT';
    private const PARAM_REMOVAL_CONFIRMATION_TOPIC_DEFAULT = 'DATA_POLICY_REMOVAL_CONFIRMATION_TOPIC_DEFAULT';
    private const PARAM_FULL_REMOVAL_CONFIRMATION_TOPIC_DEFAULT = 'DATA_POLICY_FULL_REMOVAL_CONFIRMATION_TOPIC_DEFAULT';

    public function __invoke(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();
        $parameters = $configurator->parameters();

        $parameters->set(self::PARAM_REMOVAL_SUBSCRIPTION_DEFAULT, 'data_policy_removal_subscription');
        $parameters->set(
            self::PARAM_FULL_REMOVAL_CONFIRMATION_SUBSCRIPTION_DEFAULT,
            'data_policy_removal_confirmation_subscription'
        );
        $parameters->set(self::PARAM_REMOVAL_CONFIRMATION_TOPIC_DEFAULT, 'data_policy_removal_confirmation_topic');
        $parameters->set(
            self::PARAM_FULL_REMOVAL_CONFIRMATION_TOPIC_DEFAULT,
            'data_policy_full_removal_confirmation_topic'
        );

        $services
            ->set(DataRemovalConfirmationPublisher::class, DataRemovalConfirmationPublisher::class)
            ->args(
                [
                    service(PubSubClientFactory::class),
                    service(LoggerService::SERVICE_ID),
                ]
            );

        $services
            ->set(DataRemovalHandler::class, DataRemovalHandler::class)
            ->args([
                service(LoggerService::SERVICE_ID),
                service(tao_models_classes_UserService::SERVICE_ID),
            ]);

        $services
            ->set(DataRemovalHandlerProxy::class, DataRemovalHandlerProxy::class)
            ->args([
                service(LoggerService::class),
                service(DataRemovalConfirmationPublisher::class),
                env('DATA_DELETION_CONFIRMATION_PUB_SUB_TOPIC')
                    ->default(self::PARAM_REMOVAL_CONFIRMATION_TOPIC_DEFAULT)
                    ->string(),
            ]);

        $services
            ->get(DataRemovalHandlerProxy::class)
            ->call(
                'addHandler',
                ['remove-deactivated-administrative-profile', service(DataRemovalHandler::class)]
            );

        $services
            ->set(DataRemovalListener::class, DataRemovalListener::class)
            ->args(
                [
                    service(PubSubClientFactory::class),
                    service(DataRemovalHandlerProxy::class),
                    service(LoggerService::SERVICE_ID),
                    env('DATA_POLICY_REMOVAL_SUBSCRIPTION')
                        ->default(self::PARAM_REMOVAL_SUBSCRIPTION_DEFAULT)
                        ->string(),
                ]
            )
            ->public();

        $services
            ->set(FullDataRemovalHandler::class, FullDataRemovalHandler::class)
            ->args([
                service(tao_models_classes_UserService::SERVICE_ID),
            ]);

        $services
            ->set(FullDataRemovalHandlerProxy::class, FullDataRemovalHandlerProxy::class)
            ->args([
                service(LoggerService::class),
                service(DataRemovalConfirmationPublisher::class),
                env('DATA_DELETION_CONFIRMATION_PUB_SUB_TOPIC')
                    ->default(self::PARAM_FULL_REMOVAL_CONFIRMATION_TOPIC_DEFAULT)
                    ->string(),
            ]);

        $services
            ->get(FullDataRemovalHandlerProxy::class)
            ->call(
                'addHandler',
                ['remove-deactivated-administrative-profile', service(FullDataRemovalHandler::class)]
            );

        $services
            ->set(DataRemovalCheckListener::class, DataRemovalCheckListener::class)
            ->args(
                [
                    service(PubSubClientFactory::class),
                    service(FullDataRemovalHandlerProxy::class),
                    service(LoggerService::SERVICE_ID),
                    env('DATA_POLICY_FULL_REMOVAL_CONFIRMATION_SUBSCRIPTION')
                        ->default(self::PARAM_FULL_REMOVAL_CONFIRMATION_SUBSCRIPTION_DEFAULT)
                        ->string(),
                ]
            )
            ->public();
    }
}
