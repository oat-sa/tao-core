<?php
/**
 * Created by PhpStorm.
 * User: christophe
 * Date: 15/02/17
 * Time: 10:43
 */

namespace oat\tao\scripts\install;

use Doctrine\DBAL\Schema\SchemaException;
use oat\oatbox\extension\InstallAction;
use oat\oatbox\notification\implementation\NotificationService;
use oat\oatbox\notification\NotificationServiceInterface;
use oat\tao\model\notification\RdsNotification;

class InstallNotificationTable extends InstallAction
{

    public function __invoke($params)
    {
        $persistence = \common_persistence_Manager::getPersistence(RdsNotification::PERSISTENCE_OPTION);
        $schemaManager = $persistence->getDriver()->getSchemaManager();
        $schema = $schemaManager->createSchema();
        /**
         * @var \Doctrine\DBAL\Schema\Schema $fromSchema
         */
        $fromSchema = clone $schema;

        try {

            $queueTable = $schema->createtable(RdsNotification::NOTIF_TABLE);

            $queueTable->addOption('engine', 'MyISAM');
            $queueTable->addColumn(RdsNotification::NOTIF_FIELD_ID           , "integer"  ,array("notnull" => true, 'autoincrement' => true));
            $queueTable->addColumn(RdsNotification::NOTIF_FIELD_RECIPIENT    , "string"   ,array("notnull" => true ,"length" => 255));
            $queueTable->addColumn(RdsNotification::NOTIF_FIELD_STATUS       , "integer"  ,array("default" => 0 , "notnull" => false,"length" => 255));
            $queueTable->addColumn(RdsNotification::NOTIF_FIELD_TITLE        , "string"   ,array("length" => 255));
            $queueTable->addColumn(RdsNotification::NOTIF_FIELD_MESSAGE      , "text"     ,array("default" => null));
            $queueTable->addColumn(RdsNotification::NOTIF_FIELD_SENDER       , "string"   ,array("default" => null , "notnull" => false,"length" => 255));
            $queueTable->addColumn(RdsNotification::NOTIF_FIELD_SENDER_NANE  , "string"   ,array("default" => null , "notnull" => false,"length" => 255));
            $queueTable->addColumn(RdsNotification::NOTIF_FIELD_CREATION     , "datetime" ,array("notnull" => true));
            $queueTable->addColumn(RdsNotification::NOTIF_FIELD_UPDATED      , "datetime" ,array("notnull" => true));
            $queueTable->setPrimaryKey(array(RdsNotification::NOTIF_FIELD_ID));

            $queries = $persistence->getPlatform()->getMigrateSchemaSql($fromSchema, $schema);

            foreach ($queries as $query) {
                $persistence->exec($query);
            }

        } catch(SchemaException $e) {
            \common_Logger::i('Database Schema already up to date.');
        }

        $queue = new NotificationService();
        $queue->setServiceLocator($this->getServiceManager());
        $queue->setOption('rds' ,
            array(
                'class'   => RdsNotification::class,
                'options' => [],
            )
        );

        $this->getServiceManager()->register(NotificationServiceInterface::SERVICE_ID, $queue);

    }

}