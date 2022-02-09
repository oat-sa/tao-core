# Observer implementations

TAO-Core observer implementations. They support native PHP 
[SplObserver](https://www.php.net/manual/en/class.splobserver.php) and
[SplSubject](https://www.php.net/manual/en/class.splsubject.php).

## LoggerObserver

Used in case you want to notify the subject for logging purposes.

### Example

```php
$observer = new \oat\tao\model\Observer\Log\LoggerObserver($logger);

$anySubject = new \oat\tao\model\Observer\Subject();
$anySubject->withData(
    [
        'some' => 'data',
        'another' => 'data',
    ]
)
$anySubject->attach($observer);
$anySubject->notify(); // Will log subject data...
```

## PubSubObserver

Used in case you want to notify `GCP Pub/Sub` service.

Requirements:

Install GCP lib:

```shell
php composer.phar require "google/cloud-pubsub":"1.34.*" -vvv
```

### Example

```php
$factory = (new \oat\tao\model\Observer\GCP\PubSubClientFactory())->create();

$observer = new \oat\tao\model\Observer\GCP\PubSubObserver(
    $client,
    $logger,
    [
        \oat\tao\model\Observer\GCP\PubSubObserver::CONFIG_TOPIC => 'my_gcp_topic',
    ]
);

$anySubject = new \oat\tao\model\Observer\Subject();
$anySubject->withData(
    [
        'some' => 'data',
        'another' => 'data',
    ]
)
$anySubject->attach($observer);
$anySubject->notify(); // Will send a message to GCP Pub/Sub
```
