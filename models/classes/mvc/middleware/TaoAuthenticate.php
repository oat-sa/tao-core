<?php
/**
 * Created by PhpStorm.
 * User: christophe
 * Date: 28/04/17
 * Time: 10:00
 */

namespace oat\tao\model\mvc\middleware;


use oat\tao\model\accessControl\AclProxy;
use oat\tao\model\accessControl\data\DataAccessControl;
use oat\tao\model\accessControl\data\PermissionException;
use oat\tao\model\accessControl\func\AclProxy as FuncProxy;
use oat\tao\model\mvc\psr7\Resolver;

class TaoAuthenticate extends AbstractTaoMiddleware {

    protected $extension;

    protected $controller;

    protected $action;

    protected $parameters;

    protected function getExtensionId() {
        return $this->extension;
    }

    protected function getControllerClass() {
        return $this->controller;
    }

    protected function getAction() {
        return $this->action;
    }

    protected function getParameters() {
        return $this->parameters;
    }

    protected function getController()
    {
        $controllerClass = $this->getControllerClass();
        if(class_exists($controllerClass)) {
            return new $controllerClass();
        } else {
            throw new \ActionEnforcingException('Controller "'.$controllerClass.'" could not be loaded.', $controllerClass, $this->getAction());
        }
    }

    protected function init($request) {
        /**
         * @var $resolver Resolver
         */
        $resolver = $this->container->get('resolver');
        $resolver->setRequest($request);

        $this->extension = $resolver->getExtensionId();
        $this->controller = $resolver->getControllerClass();
        $this->action = $resolver->getMethodName();

        $post = $request->getParsedBody();
        if(is_null($post)) {
            $post = [];
        }

        $params   = array_merge($request->getQueryParams() , $post);

        $this->parameters = $params;
    }

    protected function verifyAuthorization() {
        $user = \common_session_SessionManager::getSession()->getUser();
        if (!AclProxy::hasAccess($user, $this->getControllerClass(), $this->getAction(), $this->getParameters())) {
            $func  = new FuncProxy();
            $data  = new DataAccessControl();
            //now go into details to see which kind of permissions are not correct
            if($func->hasAccess($user, $this->getControllerClass(), $this->getAction(), $this->getParameters()) &&
                !$data->hasAccess($user, $this->getControllerClass(), $this->getAction(), $this->getParameters())){

                throw new PermissionException($user->getIdentifier(), $this->getAction(), $this->getControllerClass(), $this->getExtensionId());
            }

            throw new \tao_models_classes_AccessDeniedException($user->getIdentifier(), $this->getAction(), $this->getControllerClass(), $this->getExtensionId());
        }
    }

    public function __invoke( $request,  $response,  $args) {

        $this->init($request);
        // Are we authorized to execute this action?
        try {
            $this->verifyAuthorization();
        } catch(PermissionException $pe){
            return $response->withStatus(403)->withHeader('Location' , '/tao/Permission/denied');
        }

        return $response;
    }

}