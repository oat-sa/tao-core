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

namespace oat\tao\model\Observer\ServiceProvider;

use common_ext_ExtensionsManager;
use oat\oatbox\log\LoggerService;
use oat\tao\model\Observer\GCP\PubSubClientFactory;
use oat\generis\model\DependencyInjection\ContainerServiceProviderInterface;
use oat\tao\model\Observer\GCP\UserDataRemoval\PubSubUserDataPolicyListener;
use oat\tao\model\Observer\GCP\UserDataRemoval\UserDataPolicyConfirmationPublisher;
use oat\tao\model\Observer\GCP\UserDataRemoval\UserDataRemovalCheckHandler;
use oat\tao\model\Observer\GCP\UserDataRemoval\UserDataRemovalHandler;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\env;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

class ObserverServiceProvider implements ContainerServiceProviderInterface
{
    private const PARAM_REMOVAL_SUBSCRIPTION_DEFAULT = 'DATA_POLICY_REMOVAL_SUBSCRIPTION_DEFAULT';
    private const PARAM_CONFIRMATION_REQUEST_SUBSCRIPTION_DEFAULT = 'DATA_POLICY_CONFIRMATION_REQUEST_SUBSCRIPTION_DEFAULT';
    private const PARAM_REMOVAL_CONFIRMATION_TOPIC_DEFAULT = 'DATA_POLICY_REMOVAL_CONFIRMATION_TOPIC_DEFAULT';
    private const PARAM_FULL_REMOVAL_CONFIRMATION_TOPIC_DEFAULT = 'DATA_POLICY_FULL_REMOVAL_CONFIRMATION_TOPIC_DEFAULT';

    public function __invoke(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();
        $parameters = $configurator->parameters();

        $parameters->set(self::PARAM_REMOVAL_SUBSCRIPTION_DEFAULT, 'mock_data_policy_removal_subscription');
        $parameters->set(
            self::PARAM_CONFIRMATION_REQUEST_SUBSCRIPTION_DEFAULT,
            'mock_data_policy_confirmation_request_subscription'
        );
        $parameters->set(self::PARAM_REMOVAL_CONFIRMATION_TOPIC_DEFAULT, 'mock_data_policy_removal_confirmation_topic');
        $parameters->set(
            self::PARAM_FULL_REMOVAL_CONFIRMATION_TOPIC_DEFAULT,
            'mock_data_policy_full_removal_confirmation_topic'
        );

        $services->set(PubSubClientFactory::class, PubSubClientFactory::class);

        $services
            ->set(UserDataPolicyConfirmationPublisher::class, UserDataPolicyConfirmationPublisher::class)
            ->args(
                [
                    service(PubSubClientFactory::class),
                    service(LoggerService::SERVICE_ID),
                ]
            );

        $services
            ->set(UserDataRemovalHandler::class, UserDataRemovalHandler::class)
            ->args(
                [
                    service(LoggerService::SERVICE_ID),
                    service(common_ext_ExtensionsManager::SERVICE_ID),
                    service(\tao_models_classes_UserService::SERVICE_ID),
                    service(UserDataPolicyConfirmationPublisher::class),
                    env('DATA_DELETION_CONFIRMATION_PUB_SUB_TOPIC')
                        ->default(self::PARAM_REMOVAL_CONFIRMATION_TOPIC_DEFAULT)
                        ->string(),
                ]
            );

        $services
            ->set(UserDataRemovalCheckHandler::class, UserDataRemovalCheckHandler::class)
            ->args(
                [
                    service(LoggerService::SERVICE_ID),
                    service(common_ext_ExtensionsManager::SERVICE_ID),
                    service(\tao_models_classes_UserService::SERVICE_ID),
                    service(UserDataPolicyConfirmationPublisher::class),
                    env('DATA_DELETION_FULL_REMOVAL_CONFIRMATION_PUB_SUB_TOPIC')
                        ->default(self::PARAM_FULL_REMOVAL_CONFIRMATION_TOPIC_DEFAULT)
                        ->string(),
                ]
            );

        $services
            ->set(PubSubUserDataPolicyListener::class, PubSubUserDataPolicyListener::class)
            ->args(
                [
                    service(PubSubClientFactory::class),
                    service(LoggerService::SERVICE_ID),
                ]
            )
            ->public();

        $services
            ->get(PubSubUserDataPolicyListener::class)
            ->call(
                'addHandler',
                [
                    env('DATA_DELETION_REQUEST_PUB_SUB_SUBSCRIPTION')
                        ->default(self::PARAM_REMOVAL_SUBSCRIPTION_DEFAULT)
                        ->string(),
                    service(UserDataRemovalHandler::class),
                ]
            );

        $services
            ->get(PubSubUserDataPolicyListener::class)
            ->call(
                'addHandler',
                [
                    env('DATA_DELETION_CONFIRMATION_PUB_SUB_SUBSCRIPTION')
                        ->default(self::PARAM_CONFIRMATION_REQUEST_SUBSCRIPTION_DEFAULT)
                        ->string(),
                    service(UserDataRemovalCheckHandler::class),
                ]
            );
    }
}
