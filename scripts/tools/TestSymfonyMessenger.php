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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\scripts\tools;

use oat\generis\persistence\PersistenceManager;
use oat\oatbox\extension\AbstractAction;
use oat\oatbox\log\ColoredVerboseLogger;
use oat\tao\scripts\tools\symfonyMessenger\ExampleTask;
use oat\tao\scripts\tools\symfonyMessenger\ExampleTaskHandler;
use Pimple\Container;
use Pimple\Psr11\ServiceLocator;
use Psr\Log\LogLevel;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Messenger\EventListener\SendFailedMessageToFailureTransportListener;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;
use Symfony\Component\Messenger\Middleware\SendMessageMiddleware;
use Symfony\Component\Messenger\Transport\Doctrine\DoctrineReceiver;
use Symfony\Component\Messenger\Transport\Doctrine\DoctrineSender;
use Symfony\Component\Messenger\Transport\Sender\SendersLocator;
use Symfony\Component\Messenger\Transport\Doctrine\Connection as MessengerDoctrineConnection;
use Symfony\Component\Messenger\Transport\Serialization\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\JsonSerializableNormalizer;
use Symfony\Component\Messenger\Worker;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;

class TestSymfonyMessenger extends AbstractAction
{
    public function __invoke($params)
    {
        if ($params[0] ?? null === 'w') {
            $this->consume();
        } else {
            $this->enqueue();
        }
    }

    protected function consume()
    {
        $verboseLogger = new ColoredVerboseLogger(LogLevel::DEBUG);
        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addSubscriber(new SendFailedMessageToFailureTransportListener(
            $this->getDoctrineSender('failed_queue'),
            $verboseLogger
        ));

        $handleMessageMiddleware = new HandleMessageMiddleware($this->getHandlersLocator());
        $msgBus = new MessageBus([$handleMessageMiddleware]);
        $doctrineReceiver = $this->getDoctrineReceiver('default');

        $worker = new Worker([$doctrineReceiver], $msgBus, $eventDispatcher, $verboseLogger);
        $worker->run();
    }

    protected function enqueue()
    {
        $sendersLocator = $this->getSendersLocator();
        $sendMsgMiddleware = new SendMessageMiddleware($sendersLocator);
        $msgBus = new MessageBus([$sendMsgMiddleware]);
        $envelope = $msgBus->dispatch(new ExampleTask('p1v'));
        var_dump($envelope);
    }

    /**
     * @return HandlersLocator
     */
    protected function getHandlersLocator()
    {
        return new HandlersLocator([
            ExampleTask::class => [
                static function ($task) {
                    return (new ExampleTaskHandler())($task);
                }
            ]
        ]);
    }

    /**
     * @param string $queueName
     * @return DoctrineReceiver
     */
    protected function getDoctrineReceiver($queueName)
    {
        return new DoctrineReceiver(
            $this->getDoctrineConnection($queueName),
            $this->getSerializer()
        );
    }

    /**
     * @return SendersLocator
     */
    protected function getSendersLocator()
    {
        return new SendersLocator(
            [ExampleTask::class => ['tao/MessengerDoctrineSender']],
            $this->getServiceContainer()
        );
    }

    /**
     * @return ServiceLocator
     */
    protected function getServiceContainer()
    {
        return new ServiceLocator(new Container([
            'tao/MessengerDoctrineSender' => $this->getDoctrineSender('default')
        ]), [
            'tao/MessengerDoctrineSender'
        ]);
    }

    /**
     * @param string $queueName
     * @return DoctrineSender
     */
    protected function getDoctrineSender($queueName)
    {
        return new DoctrineSender(
            $this->getDoctrineConnection($queueName),
            $this->getSerializer()
        );
    }

    /**
     * @return Serializer
     */
    protected function getSerializer()
    {
        return new Serializer(
            new \Symfony\Component\Serializer\Serializer(
                [
                    new JsonSerializableNormalizer(),
                    new DateTimeNormalizer(),
                    new ArrayDenormalizer(),
                    new PropertyNormalizer()
                ],
                [
                    new JsonEncoder()
                ]
            ),
            'json'
        );
    }

    /**
     * @param string $queueName
     * @return MessengerDoctrineConnection
     */
    protected function getDoctrineConnection($queueName)
    {
        return new MessengerDoctrineConnection(
            [
                'queue_name' => $queueName
            ],
            $this->getPersistence()->getPlatForm()->getDbalConnection()
        );
    }

    /**
     * @return \common_persistence_SqlPersistence
     */
    protected function getPersistence()
    {
        return $this->getServiceLocator()
           ->get(PersistenceManager::SERVICE_ID)
           ->getPersistenceById('default');
    }
}
