<?php

namespace oat\tao\controller\entry;

use common_ext_ExtensionsManager as ExtensionsManager;
use core_kernel_users_Exception;
use DateInterval;
use DateTimeImmutable;
use oat\generis\model\user\UserRdf;
use oat\oatbox\event\EventManager;
use oat\oatbox\session\SessionService;
use oat\oatbox\user\LoginService;
use oat\tao\model\entryPoint\EntryPointService;
use oat\tao\model\event\LoginFailedEvent;
use oat\tao\model\event\LoginSucceedEvent;
use oat\tao\model\security\IFrameBlocker;
use oat\tao\model\user\UserLocks;
use tao_actions_form_Login as LoginForm;
use tao_helpers_Date;
use tao_models_classes_accessControl_AclProxy;
use Zend\ServiceManager\ServiceLocatorInterface;

class Login extends \tao_actions_CommonModule
{
    /**
     * @var ExtensionsManager
     */
    private $extensionsManager;

    /**
     * @var EntryPointService
     */
    private $entryPointService;
    /**
     * @var UserLocks
     */
    private $userLocksService;
    /**
     * @var EventManager
     */
    private $eventManager;

    /** @var ServiceLocatorInterface  */
    protected $serviceLocator;

    /**
     * @var SessionService
     */
    private $sessionService;


    public function __construct(ExtensionsManager $extensionsManager, EntryPointService $entryPointService, UserLocks $userLocksService, EventManager $eventManager, ServiceLocatorInterface $serviceLocator, SessionService $sessionService, $veryImportantParam)
    {
        $this->extensionsManager = $extensionsManager;
        $this->entryPointService = $entryPointService;
        $this->userLocksService = $userLocksService;
        $this->eventManager = $eventManager;
        $this->serviceLocator = $serviceLocator;
        $this->sessionService = $sessionService;
    }


    public function login()
    {
        $this->defaultData();

        $extension = $this->extensionsManager->getExtensionById('tao');
        $config = $extension->getConfig('login');

        $disableAutoComplete = !empty($config['disableAutocomplete']);
        $enablePasswordReveal = !empty($config['enablePasswordReveal']);

        $enableIframeProtection = !empty($config['block_iframe_usage']) && $config['block_iframe_usage'];
        if ($enableIframeProtection) {
            IFrameBlocker::setHeader();
        }

        $params = array(
            'disableAutocomplete' => $disableAutoComplete,
            'enablePasswordReveal' => $enablePasswordReveal,
        );

        if ($this->hasRequestParameter('redirect')) {
            $redirectUrl = $_REQUEST['redirect'];

            if (strpos($redirectUrl, '/') === 0 || strpos($redirectUrl, ROOT_URL) === 0) {
                $params['redirect'] = $redirectUrl;
            }
        }

        $container = new LoginForm($params);
        $form = $container->getForm();

        if ($form->isSubmited()) {
            if ($form->isValid()) {
                try {
                    if ($this->userLocksService->isLocked($form->getValue('login'))) {
                        $this->logInfo("User '" . $form->getValue('login') . "' has been locked.");

                        $statusDetails = $this->userLocksService->getStatusDetails($form->getValue('login'));
                        if ($statusDetails['auto']) {
                            $msg = __('You have been locked due to too many failed login attempts. ');
                            if ($this->userLocksService->getOption(UserLocks::OPTION_USE_HARD_LOCKOUT)) {
                                $msg .= __('Please contact your administrator.');
                            } else {
                                /** @var DateInterval $remaining */
                                $remaining = $statusDetails['remaining'];

                                $reference = new DateTimeImmutable();
                                $endTime = $reference->add($remaining);

                                $diffInSeconds = $endTime->getTimestamp() - $reference->getTimestamp();

                                $msg .= __('Please try in %s.',
                                    $diffInSeconds > 60
                                        ? tao_helpers_Date::displayInterval($statusDetails['remaining'], tao_helpers_Date::FORMAT_INTERVAL_LONG)
                                        : $diffInSeconds . ' ' . ($diffInSeconds == 1 ? __('second') : __('seconds'))
                                );
                            }
                        } else {
                            $msg = __('Your account has been locked, please contact your administrator.');
                        }

                        $this->setData('errorMessage', $msg);
                    } else if (LoginService::login($form->getValue('login'), $form->getValue('password'))) {
                        $logins = $this->sessionService->getCurrentSession()->getUser()->getPropertyValues(UserRdf::PROPERTY_LOGIN);

                        $this->eventManager->trigger(new LoginSucceedEvent(current($logins)));

                        $this->logInfo(sprintf("Successful login of user '%s'.", $form->getValue('login')));

                        if ($this->hasRequestParameter('redirect') && tao_models_classes_accessControl_AclProxy::hasAccessUrl($_REQUEST['redirect'])) {
                            $this->redirect($_REQUEST['redirect']);
                        } else {
                            $this->forward('entry');
                        }
                    } else {
                        $this->eventManager->trigger(new LoginFailedEvent($form->getValue('login')));

                        $this->logInfo(sprintf("Unsuccessful login of user '%s'.", $form->getValue('login')));

                        $msg = __('Invalid login or password. Please try again.');

                        if ($this->userLocksService->getOption(UserLocks::OPTION_USE_HARD_LOCKOUT)) {
                            $remainingAttempts = $this->userLocksService->getLockoutRemainingAttempts($form->getValue('login'));
                            if ($remainingAttempts !== false) {
                                if ($remainingAttempts === 0) {
                                    $msg = __('Invalid login or password. Your account has been locked, please contact your administrator.');
                                } else {
                                    $msg .= ' ' . ($remainingAttempts === 1
                                            ? __('Last attempt before your account is locked.')
                                            : __('%d attempts left before your account is locked.', $remainingAttempts));
                                }
                            }
                        }

                        $this->setData('errorMessage', $msg);
                    }
                } catch (core_kernel_users_Exception $e) {
                    $this->setData('errorMessage', __('Invalid login or password. Please try again.'));
                }
            } else {
                foreach ($form->getElements() as $formElement) {
                    $fieldError = $formElement->getError();
                    if ($fieldError) {
                        $this->setData('fieldMessages_' . $formElement->getName(), $fieldError);
                    }
                }
            }
        }

        $this->setData('title', __('TAO Login'));

        $this->setData('autocompleteDisabled', (int)$disableAutoComplete);
        $this->setData('passwordRevealEnabled', (int)$enablePasswordReveal);

        $this->setData('entryPoints', $this->entryPointService->getEntryPoints(EntryPointService::OPTION_PRELOGIN));

        if ($this->hasRequestParameter('msg')) {
            $this->setData('msg', $this->getRequestParameter('msg'));
        }

        $this->setData('show_gdpr', !empty($config['show_gdpr']) && $config['show_gdpr']);

        $this->setData('content-template', array('blocks/login.tpl', 'tao'));

        $this->setView('layout.tpl', 'tao');
    }

    /**
     * Force usage of the injected instance
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

}
