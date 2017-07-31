<?php
/**
 * Created by PhpStorm.
 * User: christophe
 * Date: 09/06/17
 * Time: 08:52
 */

namespace oat\tao\model\mvc\Application;


use oat\oatbox\event\EventManagerAwareTrait;
use oat\tao\model\mvc\Application\Event\AbstractApplicationEvent;
use oat\tao\model\mvc\Application\Event\onBootEvent;
use oat\tao\model\mvc\Application\Event\onEndEvent;
use oat\tao\model\mvc\Application\Event\onErrorEvent;
use oat\tao\model\mvc\Application\Event\onFinaliseEvent;
use oat\tao\model\mvc\Application\Event\onForwardEvent;
use oat\tao\model\mvc\Application\Event\onRenderEvent;
use oat\tao\model\mvc\Application\Event\onRoutingEvent;
use oat\tao\model\mvc\Application\Event\onRunEvent;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class ApplicationEventTrigger implements ServiceLocatorAwareInterface
{

    use EventManagerAwareTrait;
    use ServiceLocatorAwareTrait;

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     */
    public function boot(ServerRequestInterface $request , ResponseInterface $response) {
        $event = new onBootEvent('boot' , [] , $request ,  $response );
        return $this->send($event);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     */
    public function routing(ServerRequestInterface $request , ResponseInterface $response) {
        $event = new onRoutingEvent('routing' , [] , $request ,  $response );
        return $this->send($event);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     */
    public function run(ServerRequestInterface $request , ResponseInterface $response) {
        $event = new onRunEvent('run' , [] , $request ,  $response );
        return $this->send($event);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     */
    public function forward(ServerRequestInterface $request , ResponseInterface $response) {
        $event = new onForwardEvent('forward' , [] , $request ,  $response );
        return $this->send($event);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     */
    public function error(ServerRequestInterface $request , ResponseInterface $response) {
        $event = new onErrorEvent('error' , [] , $request ,  $response );
        return $this->send($event);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     */
    public function end(ServerRequestInterface $request , ResponseInterface $response) {
        $event = new onEndEvent('end' , [] , $request ,  $response );
        return $this->send($event);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     */
    public function render(ServerRequestInterface $request , ResponseInterface $response) {
        $event = new onRenderEvent('render' , [] , $request ,  $response );
        return $this->send($event);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     */
    public function finalise(ServerRequestInterface $request , ResponseInterface $response) {
        $event = new onFinaliseEvent('finalise' , [] , $request ,  $response );
        return $this->send($event);
    }

    /**
     * @param AbstractApplicationEvent $event
     */
    protected function send(AbstractApplicationEvent $event) {
        return $this->getEventManager()->trigger($event , []);
    }

}