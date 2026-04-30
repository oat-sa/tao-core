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

use oat\generis\model\data\Ontology;
use oat\oatbox\log\LoggerService;
use oat\tao\model\Observer\GCP\PubSubClientFactory;
use oat\generis\model\DependencyInjection\ContainerServiceProviderInterface;
use oat\tao\model\DataPolicyOrchestrator\PubSub\Listener\DataRemovalListener;
use oat\tao\model\DataPolicyOrchestrator\PubSub\Listener\FullDataRemovalCheckListener;
use oat\tao\model\DataPolicyOrchestrator\PubSub\Publisher\DataRemovalConfirmationPublisher;
use oat\tao\model\DataPolicyOrchestrator\Handler\DataRemovalHandlerProxy;
use oat\tao\model\DataPolicyOrchestrator\Handler\FullDataRemovalCheckHandlerProxy;
use oat\tao\model\DataPolicyOrchestrator\Handler\UserFullDataRemovalCheckHandler;
use oat\tao\model\DataPolicyOrchestrator\Handler\UserDataRemovalHandler;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use tao_models_classes_UserService;

use function Symfony\Component\DependencyInjection\Loader\Configurator\env;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

// phpcs:disable Generic.Files.LineLength
class DataPolicyServiceProvider implements ContainerServiceProviderInterface
{
    private const PARAM_REMOVAL_SUBSCRIPTION_DEFAULT = 'DATA_POLICY_REMOVAL_SUBSCRIPTION_DEFAULT';
    private const PARAM_FULL_REMOVAL_CHECK_SUBSCRIPTION_DEFAULT = 'DATA_POLICY_REMOVAL_CHECK_SUBSCRIPTION_DEFAULT';
    private const PARAM_REMOVAL_CONFIRMATION_TOPIC_DEFAULT = 'DATA_POLICY_REMOVAL_CONFIRMATION_TOPIC_DEFAULT';
    private const PARAM_FULL_REMOVAL_CONFIRMATION_TOPIC_DEFAULT = 'DATA_POLICY_FULL_REMOVAL_CONFIRMATION_TOPIC_DEFAULT';

    public function __invoke(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();
        $parameters = $configurator->parameters();

        $parameters->set(self::PARAM_REMOVAL_SUBSCRIPTION_DEFAULT, 'data_policy_removal_subscription');
        $parameters->set(
            self::PARAM_FULL_REMOVAL_CHECK_SUBSCRIPTION_DEFAULT,
            'data_policy_full_removal_check_subscription'
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
            ->set(UserDataRemovalHandler::class, UserDataRemovalHandler::class)
            ->args([
                service(LoggerService::SERVICE_ID),
                service(Ontology::SERVICE_ID),
                service(tao_models_classes_UserService::SERVICE_ID),
            ]);

        $services
            ->set(DataRemovalHandlerProxy::class, DataRemovalHandlerProxy::class)
            ->args([
                service(LoggerService::class),
                service(DataRemovalConfirmationPublisher::class),
                env('DATA_POLICY_REMOVAL_CONFIRMATION_PUB_SUB_TOPIC')
                    ->default(self::PARAM_REMOVAL_CONFIRMATION_TOPIC_DEFAULT)
                    ->string(),
            ]);

        $services
            ->get(DataRemovalHandlerProxy::class)
            ->call(
                'addHandler',
                ['remove-deactivated-administrative-profile', service(UserDataRemovalHandler::class)]
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
            ->set(UserFullDataRemovalCheckHandler::class, UserFullDataRemovalCheckHandler::class)
            ->args([
                service(Ontology::SERVICE_ID),
            ]);

        $services
            ->set(FullDataRemovalCheckHandlerProxy::class, FullDataRemovalCheckHandlerProxy::class)
            ->args([
                service(LoggerService::class),
                service(DataRemovalConfirmationPublisher::class),
                env('DATA_POLICY_FULL_REMOVAL_CONFIRMATION_PUB_SUB_TOPIC')
                    ->default(self::PARAM_FULL_REMOVAL_CONFIRMATION_TOPIC_DEFAULT)
                    ->string(),
            ]);

        $services
            ->get(FullDataRemovalCheckHandlerProxy::class)
            ->call(
                'addHandler',
                ['remove-deactivated-administrative-profile', service(UserFullDataRemovalCheckHandler::class)]
            );

        $services
            ->set(FullDataRemovalCheckListener::class, FullDataRemovalCheckListener::class)
            ->args(
                [
                    service(PubSubClientFactory::class),
                    service(FullDataRemovalCheckHandlerProxy::class),
                    service(LoggerService::SERVICE_ID),
                    env('DATA_POLICY_FULL_REMOVAL_CHECK_SUBSCRIPTION')
                        ->default(self::PARAM_FULL_REMOVAL_CHECK_SUBSCRIPTION_DEFAULT)
                        ->string(),
                ]
            )
            ->public();
    }
}
