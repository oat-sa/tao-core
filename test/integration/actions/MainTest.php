<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 31 Milk St # 960789 Boston, MA 02196 USA.
 *
 * Copyright (c) 2026 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\integration\actions;

use GuzzleHttp\Psr7\Response;
use oat\generis\model\GenerisRdf;
use oat\oatbox\service\ServiceManager;
use oat\tao\model\accessControl\AclProxy;
use oat\tao\model\accessControl\func\AclProxy as FuncProxy;
use oat\tao\model\accessControl\func\FuncAccessControl;
use oat\tao\model\action\ActionBlackList;
use oat\tao\model\DynamicConfig\DynamicConfigProviderInterface;
use oat\tao\model\entryPoint\EntryPointService;
use oat\tao\model\entryPoint\Entrypoint;
use oat\tao\model\menu\MenuService;
use oat\tao\model\menu\Perspective;
use oat\tao\model\menu\SectionVisibilityByRoleFilter;
use oat\tao\model\menu\SectionVisibilityFilter;
use oat\tao\model\mvc\DefaultUrlService;
use oat\tao\model\notification\NotificationServiceInterface;
use oat\tao\model\TaoOntology;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

/** Controller integration with in-memory menus, sessions and services; no installation or RDF writes. */
class MainTest extends TestCase
{
    private array $savedStatics = [];
    private array $allowedControllers = [];
    private array $hiddenSections = [];
    private array $restrictedRoles = [];
    private array $remembered = [];
    private array $roles = ['TestAuthor'];
    private array $savedGlobals;
    private bool $firstTime = false;
    private MainControllerHarness $controller;
    private $services;
    private $session;
    private $user;

    protected function setUp(): void
    {
        $this->saveStatic(MenuService::class, 'structure');
        $this->saveStatic(ServiceManager::class, 'instance');
        $this->saveStatic(\common_session_SessionManager::class, 'session');
        $this->saveStatic(AclProxy::class, 'implementations');
        $this->saveStatic(\oat\tao\model\routing\Resolver::class, 'extRoutes');
        $this->saveStatic(\oat\tao\helpers\form\WidgetRegistry::class, 'widgetCache');
        $this->saveStatic(\tao_helpers_form_FormContainer::class, 'forms');
        $this->savedGlobals = [$_POST, $_REQUEST];

        $this->user = $this->createMock(\common_user_User::class);
        $this->user->method('getIdentifier')->willReturn('urn:test:author');
        $this->user->method('getPropertyValues')->willReturnCallback(
            fn ($property) => $property === GenerisRdf::PROPERTY_USER_LOGIN ? ['test-author'] : []
        );
        $this->session = $this->createMock(\common_session_Session::class);
        $this->session->method('getUser')->willReturn($this->user);
        $this->session->method('getUserRoles')->willReturnCallback(fn () => $this->roles);
        $this->session->method('getUserPropertyValues')->willReturnCallback(function ($property) {
            if ($property === TaoOntology::PROPERTY_USER_FIRST_TIME) {
                return $this->firstTime ? [GenerisRdf::GENERIS_TRUE] : [];
            }
            return $property === TaoOntology::PROPERTY_USER_LAST_EXTENSION ? $this->remembered : [];
        });
        // A session URI permits entry selection, but can never reach RDF persistence in index().
        $this->session->method('getUserUri')->willReturnCallback(
            fn () => $this->controller->entryMode ? 'urn:test:author' : null
        );
        \common_session_SessionManager::startSession($this->session);

        $this->allowedControllers = ['tao_actions_Main', 'taoTests_actions_Tests'];
        $acl = $this->createMock(FuncAccessControl::class);
        $acl->method('accessPossible')->willReturnCallback(
            fn ($user, $controller) => in_array($controller, $this->allowedControllers, true)
        );
        $this->setStatic(AclProxy::class, 'implementations', [new FuncProxy()]);
        $this->setStatic(\oat\tao\model\routing\Resolver::class, 'extRoutes', []);

        $extensions = $this->createMock(\common_ext_ExtensionsManager::class);
        $extensions->method('getInstalledExtensionsIds')->willReturn(['tao', 'taoItems', 'taoTests']);
        $extensions->method('getExtensionById')->willReturnCallback(function ($id) {
            $extension = $this->createMock(\common_ext_Extension::class);
            $extension->method('getId')->willReturn($id);
            $extension->method('getName')->willReturn($id);
            $extension->method('getConfig')->willReturnCallback(fn ($key) => $key === 'auth'
                ? [['driver' => MainTestLoginAdapter::class, 'user' => $this->user]]
                : []);
            $manifest = $this->createMock(\oat\oatbox\extension\Manifest::class);
            $manifest->method('getRoutes')->willReturn([]);
            $extension->method('getManifest')->willReturn($manifest);
            return $extension;
        });

        $visibility = $this->createMock(SectionVisibilityFilter::class);
        $visibility->method('isVisible')->willReturnCallback(
            fn ($section) => !in_array($section, $this->hiddenSections, true)
        );
        $visibility->method('createSectionPath')->willReturnCallback(fn ($segments) => implode('/', $segments));
        $notifications = $this->createMock(NotificationServiceInterface::class);
        $notifications->method('getVisibility')->willReturn(false);
        $application = $this->createMock(\oat\tao\model\service\ApplicationService::class);
        $application->method('getDefaultEncoding')->willReturn('UTF-8');
        $userService = $this->createMock(\tao_models_classes_UserService::class);
        $userService->method('getCurrentUser')->willReturn($this->createMock(\core_kernel_classes_Resource::class));
        $container = $this->createMock(\Psr\Container\ContainerInterface::class);
        $container->method('get')->willReturnCallback(function ($id) {
            if ($id === SectionVisibilityByRoleFilter::class) {
                return new SectionVisibilityByRoleFilter($this->restrictedRoles);
            }
            if ($id === \oat\tao\helpers\form\Feeder\SanitizerValidationFeeder::class) {
                return $this->createMock(\oat\tao\helpers\form\Feeder\SanitizerValidationFeederInterface::class);
            }
            throw new \LogicException('Unexpected container lookup: ' . $id);
        });
        $this->services = [
            \oat\tao\model\service\ApplicationService::SERVICE_ID => $application,
            FuncProxy::SERVICE_ID => $acl,
            \common_ext_ExtensionsManager::SERVICE_ID => $extensions,
            \common_ext_ExtensionsManager::class => $extensions,
            SectionVisibilityFilter::SERVICE_ID => $visibility,
            NotificationServiceInterface::SERVICE_ID => $notifications,
            \tao_models_classes_UserService::SERVICE_ID => $userService,
            DefaultUrlService::SERVICE_ID => $this->createMock(DefaultUrlService::class),
            ActionBlackList::SERVICE_ID => $this->createMock(ActionBlackList::class),
        ];
        $manager = $this->getMockBuilder(ServiceManager::class)->disableOriginalConstructor()->getMock();
        $manager->method('get')->willReturnCallback(function ($id) {
            if (!array_key_exists($id, $this->services)) {
                throw new \LogicException('Unexpected service lookup: ' . $id);
            }
            return $this->services[$id];
        });
        $manager->method('getContainer')->willReturn($container);
        ServiceManager::setServiceManager($manager);
        $this->controller = new MainControllerHarness();
        $this->controller->setServiceLocator($manager);
        $this->controller->setResponse(new Response());
        $this->controller->setLogger(new \Psr\Log\NullLogger());
        $this->setMenus([
            $this->perspective('items', 'taoItems', 'Items'),
            $this->perspective('tests', 'taoTests', 'Tests'),
        ]);
    }

    protected function tearDown(): void
    {
        foreach (array_reverse($this->savedStatics) as [$property, $value]) {
            $property->setValue(null, $value);
        }
        [$_POST, $_REQUEST] = $this->savedGlobals;
    }

    public function testNewTestAuthorLandsOnTestsWithoutItemAccess(): void
    {
        $this->firstTime = true;
        $this->controller->index();

        $this->assertSame($this->url('tests', 'taoTests'), $this->controller->redirectUrl);
        $this->assertNotContains('taoItems_actions_Items', $this->allowedControllers);
    }

    public function testInaccessibleDirectItemsRequestDoesNotPersistOrRedirectToItself(): void
    {
        $this->session->expects($this->never())->method('getUserUri');
        $this->controller->parameters = ['ext' => 'taoItems', 'structure' => 'items'];
        $this->controller->index();

        $this->assertSame($this->url('tests', 'taoTests'), $this->controller->redirectUrl);
        $this->assertArrayNotHasKey('sections', $this->controller->data);
    }

    public function testStaleRememberedItemsDestinationFallsBackToTests(): void
    {
        $this->remembered = [str_replace(ROOT_URL, '', $this->url('items', 'taoItems'))];
        $this->controller->index();

        $this->assertSame($this->url('tests', 'taoTests'), $this->controller->redirectUrl);
    }

    public function testValidRememberedDestinationIsPreserved(): void
    {
        $url = $this->url('tests', 'taoTests') . '&section=tests_section';
        $this->remembered = [str_replace(ROOT_URL, '', $url)];
        $this->controller->index();

        $this->assertSame($url, $this->controller->redirectUrl);
    }

    public function testGlobalManagerCanStillRenderItems(): void
    {
        $this->allowedControllers[] = 'taoItems_actions_Items';
        $this->controller->parameters = ['ext' => 'taoItems', 'structure' => 'items'];
        $this->controller->index();

        $this->assertNull($this->controller->redirectUrl);
        $this->assertSame('items_section', $this->controller->data['sections'][0]->getId());
        $this->assertSame('items', $this->controller->data['shownStructure']);
    }

    public function testNoEligibleDestinationTerminatesWith403(): void
    {
        $this->allowedControllers = ['tao_actions_Main'];
        $this->controller->index();

        $this->assertNull($this->controller->redirectUrl);
        $this->assertSame(403, $this->controller->getPsrResponse()->getStatusCode());
        $this->assertFalse($this->controller->getPsrResponse()->hasHeader('Location'));
        $this->assertSame(403, $this->controller->errorStatus);
    }

    public function testNoMainPerspectivesTerminatesWith403(): void
    {
        $this->setMenus([]);
        $this->controller->index();

        $this->assertNull($this->controller->redirectUrl);
        $this->assertSame(403, $this->controller->getPsrResponse()->getStatusCode());
    }

    public function testFallbackDoesNotBypassMainRouteAuthorization(): void
    {
        $this->allowedControllers = ['taoTests_actions_Tests'];
        $this->controller->index();

        $this->assertNull($this->controller->redirectUrl);
        $this->assertSame(403, $this->controller->getPsrResponse()->getStatusCode());
    }

    /** @dataProvider unavailableDestinationProvider */
    public function testUnavailableRememberedDestinationsAreRejected(string $destination): void
    {
        $this->remembered = [$destination];
        $this->controller->index();

        $this->assertSame($this->url('tests', 'taoTests'), $this->controller->redirectUrl);
    }

    public function unavailableDestinationProvider(): array
    {
        return [
            'unknown perspective' => ['tao/Main/index?ext=taoItems&structure=unknown'],
            'wrong extension' => ['tao/Main/index?ext=taoItems&structure=tests'],
            'array structure' => ['tao/Main/index?ext=taoTests&structure[]=tests'],
            'array extension' => ['tao/Main/index?ext[]=taoTests&structure=tests'],
            'array section' => ['tao/Main/index?ext=taoTests&structure=tests&section[]=tests_section'],
            'unknown section' => ['tao/Main/index?ext=taoTests&structure=tests&section=unknown'],
            'empty section' => ['tao/Main/index?ext=taoTests&structure=tests&section='],
            'remembered shell' => ['tao/Main/index'],
            'remembered entry' => ['tao/Main/entry'],
            'unknown route' => ['unknown/Unknown/index'],
        ];
    }

    /** @dataProvider hiddenCandidateProvider */
    public function testFallbackSkipsHiddenOrUnusableFirstCandidate(string $reason): void
    {
        $items = $this->perspective('items', 'taoItems', 'Items', $reason !== 'empty');
        $this->setMenus([$items, $this->perspective('tests', 'taoTests', 'Tests')]);
        $this->allowedControllers[] = 'taoItems_actions_Items';
        if ($reason === 'perspective') {
            $this->hiddenSections = ['items'];
        } elseif ($reason === 'section') {
            $this->hiddenSections = ['items_section'];
        } elseif ($reason === 'role') {
            $this->restrictedRoles = ['items' => ['TestAuthor']];
        } elseif ($reason === 'section role') {
            $this->restrictedRoles = ['items_section' => ['TestAuthor']];
        }
        $this->controller->index();

        $this->assertSame($this->url('tests', 'taoTests'), $this->controller->redirectUrl);
    }

    public function hiddenCandidateProvider(): array
    {
        return array_map(fn ($reason) => [$reason], ['perspective', 'section', 'role', 'section role', 'empty']);
    }

    public function testExplicitHiddenSectionIsNotAcceptedThroughAnAccessibleSibling(): void
    {
        $tests = $this->perspective('tests', 'taoTests', 'Tests');
        $tests->addSection($this->perspective('other', 'taoTests', 'Tests')->getChildren()[0]);
        $this->setMenus([$tests]);
        $this->hiddenSections = ['tests_section'];
        $this->controller->parameters = [
            'ext' => 'taoTests', 'structure' => 'tests', 'section' => 'tests_section',
        ];
        $this->session->expects($this->never())->method('getUserUri');
        $this->controller->index();

        $this->assertSame($this->url('tests', 'taoTests'), $this->controller->redirectUrl);
    }

    public function testProbingFallbackDoesNotRemoveMenuActions(): void
    {
        $tests = $this->perspective('tests', 'taoTests', 'Tests');
        $this->setMenus([$tests]);
        $actions = $tests->getChildren()[0]->getActions();
        $this->hiddenSections = ['tests_section/edit'];
        $this->controller->index();

        $this->assertSame($this->url('tests', 'taoTests'), $this->controller->redirectUrl);
        $this->assertCount(1, $actions);
        $this->assertSame($actions, $tests->getChildren()[0]->getActions());
    }

    public function testMultipleRegisteredEntryPointsStillShowChooser(): void
    {
        $this->registerEntries([
            _url('index', 'Main', 'tao'),
            $this->url('tests', 'taoTests'),
        ]);
        $this->controller->entry();

        $this->assertNull($this->controller->redirectUrl);
        $this->assertCount(2, $this->controller->data['entries']);
        $this->assertSame(['blocks/entry-points.tpl', 'tao'], $this->controller->data['content-template']);
    }

    public function testRegisteredInaccessibleStructureIsExcluded(): void
    {
        $this->registerEntries([$this->url('items', 'taoItems'), $this->url('tests', 'taoTests')]);
        $this->controller->entry();

        $this->assertSame($this->url('tests', 'taoTests'), $this->controller->redirectUrl);
    }

    public function testOnlyInaccessibleRegisteredStructureUsesAccessibleFallback(): void
    {
        $this->registerEntries([$this->url('items', 'taoItems')]);
        $this->controller->entry();

        $this->assertSame($this->url('tests', 'taoTests'), $this->controller->redirectUrl);
    }

    public function testRenderingSkipsAnUnresolvableSection(): void
    {
        $tests = $this->perspective('tests', 'taoTests', 'Tests');
        $invalid = $this->perspective('invalid', 'unknown', 'Unknown')->getChildren()[0];
        $tests->addSection($invalid);
        $this->setMenus([$tests]);
        $this->controller->parameters = ['ext' => 'taoTests', 'structure' => 'tests'];
        $this->controller->index();

        $this->assertNull($this->controller->redirectUrl);
        $this->assertCount(1, $this->controller->data['sections']);
        $this->assertSame('tests_section', $this->controller->data['sections'][0]->getId());
    }

    public function testInaccessibleExplicitLoginDestinationUsesNormalEntryThenAccessibleFallback(): void
    {
        $this->submitLogin($this->url('items', 'taoItems'));
        $this->assertSame(_url('index', 'Main', 'tao'), $this->controller->redirectUrl);

        $this->controller->parameters = [];
        $this->controller->redirectUrl = null;
        $this->controller->index();
        $this->assertSame($this->url('tests', 'taoTests'), $this->controller->redirectUrl);
    }

    public function testValidExplicitLoginDestinationIsPreserved(): void
    {
        $url = $this->url('tests', 'taoTests') . '&section=tests_section';
        $this->submitLogin($url);
        $this->assertSame($url, $this->controller->redirectUrl);
    }

    public function testValidRootRelativeLoginDestinationIsPreserved(): void
    {
        $url = parse_url($this->url('tests', 'taoTests'), PHP_URL_PATH)
            . '?' . parse_url($this->url('tests', 'taoTests'), PHP_URL_QUERY);
        $this->submitLogin($url);
        $this->assertSame($url, $this->controller->redirectUrl);
    }

    public function testValidNonBackOfficeLoginDestinationIsPreserved(): void
    {
        $url = _url('index', 'Tests', 'taoTests');
        $this->submitLogin($url);
        $this->assertSame($url, $this->controller->redirectUrl);
    }

    /** @dataProvider malformedLoginDestinationProvider */
    public function testMalformedLoginDestinationUsesNormalEntry($url): void
    {
        $this->submitLogin($url);
        $this->assertSame(_url('index', 'Main', 'tao'), $this->controller->redirectUrl);
    }

    public function malformedLoginDestinationProvider(): array
    {
        return [
            'array redirect' => [['tao/Main/index']],
            'external URL' => ['https://example.org/tao/Main/index'],
            'protocol relative URL' => ['//example.org/tao/Main/index'],
            'backslash authority' => ['/\\example.org/tao/Main/index'],
            'dot segments' => ['/../tao/Main/index'],
            'encoded dot segments' => ['/%2e%2e/tao/Main/index'],
            'unknown controller' => ['/tao/Unknown/index'],
            'unknown action' => ['/tao/Main/unknown'],
            'malformed structure' => ['/tao/Main/index?ext=taoTests&structure[]=tests'],
            'missing extension' => ['/tao/Main/index?structure=tests'],
            'header injection' => ["/tao/Main/index\r\nLocation: https://example.org/"],
        ];
    }

    private function submitLogin($url): void
    {
        $this->setStatic(\oat\tao\helpers\form\WidgetRegistry::class, 'widgetCache', []);
        $this->registerEntries([_url('index', 'Main', 'tao')]);
        $locks = $this->createMock(\oat\tao\model\user\UserLocks::class);
        $locks->method('isLocked')->willReturn(false);
        $events = $this->createMock(\oat\oatbox\event\EventManager::class);
        $events->expects($this->once())->method('trigger')->with($this->isInstanceOf(
            \oat\tao\model\event\LoginSucceedEvent::class
        ));
        $this->services[\oat\tao\model\user\UserLocks::SERVICE_ID] = $locks;
        $this->services[\oat\oatbox\event\EventManager::SERVICE_ID] = $events;
        $_POST = ['loginForm_sent' => 1, 'login' => 'test-author', 'password' => 'test-only'];
        $_REQUEST = $_POST + ['redirect' => $url];
        $this->controller->parameters = $_REQUEST;
        $this->controller->login();
    }

    private function registerEntries(array $urls): void
    {
        $this->controller->entryMode = true;
        $entries = [];
        foreach ($urls as $url) {
            $entry = $this->createMock(Entrypoint::class);
            $entry->method('getUrl')->willReturn($url);
            $entries[] = $entry;
        }
        $registry = $this->createMock(EntryPointService::class);
        $registry->method('getEntryPoints')->willReturn($entries);
        $this->services[EntryPointService::SERVICE_ID] = $registry;
    }

    private function perspective(
        string $id,
        string $extension,
        string $controller,
        bool $withSection = true
    ): Perspective {
        $sections = $withSection ? sprintf(
            '<sections><section id="%s_section" url="%s/%s/index"><actions>'
            . '<action id="edit" url="%s/%s/edit"/></actions></section></sections>',
            $id,
            $extension,
            $controller,
            $extension,
            $controller
        ) : '';

        return Perspective::fromSimpleXMLElement(
            new \SimpleXMLElement('<structure id="' . $id . '" group="main">' . $sections . '</structure>'),
            $extension
        );
    }

    private function url(string $structure, string $extension): string
    {
        return _url('index', 'Main', 'tao', ['structure' => $structure, 'ext' => $extension]);
    }

    private function setMenus(array $perspectives): void
    {
        $this->setStatic(MenuService::class, 'structure', ['perspectives' => $perspectives]);
    }

    private function saveStatic(string $class, string $name): void
    {
        $property = new ReflectionProperty($class, $name);
        $property->setAccessible(true);
        $this->savedStatics[] = [$property, $property->getValue()];
    }

    private function setStatic(string $class, string $name, $value): void
    {
        $property = new ReflectionProperty($class, $name);
        $property->setAccessible(true);
        $property->setValue(null, $value);
    }
}

class MainControllerHarness extends \tao_actions_Main
{
    public array $parameters = [];
    public array $data = [];
    public ?string $redirectUrl = null;
    public ?int $errorStatus = null;
    public bool $entryMode = false;

    protected function defaultData()
    {
    }

    public function getRequestParameter($name)
    {
        return $this->parameters[$name] ?? null;
    }

    public function hasRequestParameter($name)
    {
        return array_key_exists($name, $this->parameters);
    }

    public function removeSessionAttribute($name)
    {
    }

    public function redirect($url, $statusCode = 302)
    {
        $this->redirectUrl = $url;
    }

    public function forward($action, $controller = null, $extension = null, $params = [])
    {
        if ($action !== 'entry') {
            throw new \LogicException('Unexpected forward: ' . $action);
        }
        return $this->entry();
    }

    public function setData($key, $value)
    {
        $this->data[$key] = $value;
        return $this;
    }

    public function setView($view, $extension = null)
    {
        return $this;
    }

    protected function getClientConfigUrl($extraParameters = [])
    {
        return '';
    }

    public function getDynamicConfigProvider(): DynamicConfigProviderInterface
    {
        return new class implements DynamicConfigProviderInterface {
            public function getConfigByName(string $configName): ?string
            {
                return null;
            }

            public function hasConfig(string $configName): bool
            {
                return false;
            }
        };
    }

    protected function returnError($description, $returnLink = true, $httpStatus = null)
    {
        $this->errorStatus = $httpStatus;
    }
}

class MainTestLoginAdapter implements \oat\oatbox\user\auth\LoginAdapter
{
    private $user;

    public function setOptions(array $options)
    {
        $this->user = $options['user'];
    }

    public function setCredentials($login, $password)
    {
    }

    public function authenticate()
    {
        return $this->user;
    }
}
