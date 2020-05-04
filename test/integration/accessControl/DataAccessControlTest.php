<?php

namespace oat\tao\test\integration\accessControl;

use oat\generis\model\data\permission\PermissionInterface;
use oat\generis\model\GenerisRdf;
use oat\generis\test\GenerisPhpUnitTestRunner;
use oat\oatbox\user\User;
use oat\tao\model\accessControl\data\DataAccessControl;
use oat\tao\model\TaoOntology;
use PHPUnit\Framework\Assert;
use oat\generis\test\MockObject;

class DataAccessControlTest extends GenerisPhpUnitTestRunner
{
    const SAMPLE_ITEMS_LABEL = 'SampleTestItem_';

    /**
     * @var \tao_models_classes_UserService
     */
    protected $userService;

    /**
     * @var MockObject
     */
    protected $dac;

    /**
     * @var array user data set
     */
    private $testAdminData = [
        GenerisRdf::PROPERTY_USER_LOGIN     =>  'jdoe_admin',
        GenerisRdf::PROPERTY_USER_PASSWORD  =>  'jdoe_admin123',
        GenerisRdf::PROPERTY_USER_LASTNAME  =>  'Doe',
        GenerisRdf::PROPERTY_USER_FIRSTNAME =>  'John',
        GenerisRdf::PROPERTY_USER_MAIL      =>  'jdoe@tao.lu',
        GenerisRdf::PROPERTY_USER_UILG      =>  'http://www.tao.lu/Ontologies/TAO.rdf#Langen-US',
        GenerisRdf::PROPERTY_USER_ROLES     =>  'http://www.tao.lu/Ontologies/TAO.rdf#GlobalManager',
        'plainPassword'                     =>  'jdoe_admin123',
    ];

    /**
     * @var array user data set
     */
    private $testAnonymousData = [
        GenerisRdf::PROPERTY_USER_LOGIN     =>  'jdoe_anon',
        GenerisRdf::PROPERTY_USER_PASSWORD  =>  'jdoe_anon123',
        GenerisRdf::PROPERTY_USER_LASTNAME  =>  'Doe',
        GenerisRdf::PROPERTY_USER_FIRSTNAME =>  'John',
        GenerisRdf::PROPERTY_USER_MAIL      =>  'jdoe@tao.lu',
        GenerisRdf::PROPERTY_USER_UILG      =>  'http://www.tao.lu/Ontologies/TAO.rdf#Langen-US',
        GenerisRdf::PROPERTY_USER_ROLES     =>  'http://www.tao.lu/Ontologies/TAO.rdf#Anonymous',
        'plainPassword'                     =>  'jdoe_anon123'
    ];

    private $adminUser;
    private $anonUser;

    public function setUp(): void
    {
        $this->userService = \tao_models_classes_UserService::singleton();
        $this->testAdminData[GenerisRdf::PROPERTY_USER_PASSWORD] = \core_kernel_users_Service::getPasswordHash()->encrypt($this->testAdminData[GenerisRdf::PROPERTY_USER_PASSWORD]);
        $this->testAnonymousData[GenerisRdf::PROPERTY_USER_PASSWORD] = \core_kernel_users_Service::getPasswordHash()->encrypt($this->testAnonymousData[GenerisRdf::PROPERTY_USER_PASSWORD]);
    }

    /**
     * Test hasAccess method of DataAccessControl class
     */
    public function testHasAccess()
    {
        //Generate sample items to test access on and move their uris into array
        $this->createSampleItems();
        $items = $this->getItemByLabel(self::SAMPLE_ITEMS_LABEL);
        $urisList = [];
        foreach ($items as $itemResource) {
            $urisList[] = $itemResource->getUri();
        }

        $isAdminUserCreated = $this->createAdminUser();
        $isAnonUserCreated = $this->createAnonymousUser();

        //Run assertions for the user which should have no access only in case of NoAccess provider configured
        if ($isAdminUserCreated) {
            $this->userService->loginUser($this->testAdminData[GenerisRdf::PROPERTY_USER_LOGIN], $this->testAdminData['plainPassword']);
            $adminUser = \common_session_SessionManager::getSession()->getUser();

            //Check on a provider that gives full access
            $this->setFreeAccessTestPermissionProvider();
            $this->check($adminUser, $urisList);

            //Check on a provider that gives full access to admin only
            $this->setDACTestPermissionProvider();
            $this->check($adminUser, $urisList);

            //Check on a provider that gives no access to anyone
            $this->setNoAccessTestPermissionProvider();
            $this->check($adminUser, $urisList);


            $this->userService->logout();
        } else {
            $this->fail("Admin user was not created, so no tests for him");
        }

        //Run assertions for the user which should have access only in case of FreeAccess provider configured
        if ($isAnonUserCreated) {
            $this->userService->loginUser($this->testAnonymousData[GenerisRdf::PROPERTY_USER_LOGIN], $this->testAnonymousData['plainPassword']);
            $anonUser = \common_session_SessionManager::getSession()->getUser();

            //Check on a provider that gives full access
            $this->setFreeAccessTestPermissionProvider();
            $this->check($anonUser, $urisList);

            //Check on a provider that gives full access to admin only
            $this->setDACTestPermissionProvider();
            $this->check($anonUser, $urisList);

            //Check on a provider that gives no access to anyone
            $this->setNoAccessTestPermissionProvider();
            $this->check($anonUser, $urisList);

            $this->userService->logout();
        } else {
            $this->fail("Anon user was not created, so no tests for him");
        }
    }

    /**
     * Make assertions
     *
     * @param User $user user to check on
     * @param array $urisList list of sample items uris to check on
     */
    private function check(User $user, array $urisList = [])
    {
        $expectedClassName = 'tao_actions_RdfController';
        $expectedAction = 'moveAll';

        //Check if admin user has access to functionality
        $this->assertThat(
            $this->dac->hasAccess($user, $expectedClassName, $expectedAction, ['id' => 'http://www.tao.lu/Ontologies/TAOItem.rdf#Item']),
            $this->getConstraintBasedOnProviderAndUser($user)
        );

        //Check if admin user has access to functionality
        $requestParameters = [];
        foreach ($urisList as $uri) {
            $requestParameters[] = ['id' => $uri];
        }
        $this->assertThat(
            $this->dac->hasAccess($user, $expectedClassName, $expectedAction, $requestParameters),
            $this->getConstraintBasedOnProviderAndUser($user)
        );
    }

    /**
     * Get constraint to make assertions based on current permission provider and user role
     *
     * @param User $user to check roles
     * @return \PHPUnit_Framework_Constraint_IsFalse|\PHPUnit_Framework_Constraint_IsTrue
     */
    private function getConstraintBasedOnProviderAndUser(User $user)
    {
        $permissionProvider = $this->dac->getPermissionProvider();
        if (
            $permissionProvider instanceof FreeAccessTestPermissionProvider
            ||
            ($permissionProvider instanceof DACTestPermissionProvider && in_array('http://www.tao.lu/Ontologies/TAO.rdf#GlobalManager', $user->getRoles()))
        ) {
            return Assert::isTrue();
        } else {
            return Assert::isFalse();
        }
    }


    /**
     * Set FreeAccess permission provider as current permission provider
     */
    private function setFreeAccessTestPermissionProvider()
    {
        $this->dac = $this->getMockBuilder(DataAccessControl::class)->setMethods(['getPermissionProvider'])->getMock();
        $this->dac->method('getPermissionProvider')->will($this->returnValue(new FreeAccessTestPermissionProvider()));
    }

    /**
     * Set DAC permission provider as current permission provider
     */
    private function setDACTestPermissionProvider()
    {
        $this->dac = $this->getMockBuilder(DataAccessControl::class)->setMethods(['getPermissionProvider'])->getMock();
        $this->dac->method('getPermissionProvider')->will($this->returnValue(new DACTestPermissionProvider()));
    }

    /**
     * Set NoAccess permission provider as current permission provider
     */
    private function setNoAccessTestPermissionProvider()
    {
        $this->dac = $this->getMockBuilder(DataAccessControl::class)->setMethods(['getPermissionProvider'])->getMock();
        $this->dac->method('getPermissionProvider')->will($this->returnValue(new NoAccessTestPermissionProvider()));
    }

    /**
     * Add admin user to test on
     *
     * @return bool
     */
    private function createAdminUser()
    {
        if ($this->userService->loginAvailable($this->testAdminData[GenerisRdf::PROPERTY_USER_LOGIN])) {
            $tmpclass = new \core_kernel_classes_Class(TaoOntology::CLASS_URI_TAO_USER);
            $this->adminUser = $tmpclass->createInstance();
            if ($this->adminUser->exists()) {
                $result = $this->userService->bindProperties($this->adminUser, $this->testAdminData);

                if ($result) {
                    return true;
                }
            }
        } else {
            $this->adminUser = $this->getUserByLogin($this->testAdminData[GenerisRdf::PROPERTY_USER_LOGIN]);
        }

        return false;
    }

    /**
     * Add anonymous user to test on
     *
     * @return bool
     */
    private function createAnonymousUser()
    {
        if ($this->userService->loginAvailable($this->testAnonymousData[GenerisRdf::PROPERTY_USER_LOGIN])) {
            $tmpclass = new \core_kernel_classes_Class(TaoOntology::CLASS_URI_TAO_USER);
            $this->anonUser = $tmpclass->createInstance();
            if ($this->anonUser->exists()) {
                $result = $this->userService->bindProperties($this->anonUser, $this->testAnonymousData);

                if ($result) {
                    return true;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Search database for user by his login
     *
     * @param $login
     * @return \core_kernel_classes_Resource|mixed
     */
    private function getUserByLogin($login)
    {
        $class = new \core_kernel_classes_Class(GenerisRdf::CLASS_GENERIS_USER);
        $users = $class->searchInstances(
            [GenerisRdf::PROPERTY_USER_LOGIN => $login],
            ['like' => false, 'recursive' => true]
        );

        return current($users);
    }

    /**
     * Create sample items
     */
    private function createSampleItems()
    {
        $rootClass = new \core_kernel_classes_Class(TaoOntology::CLASS_URI_ITEM);
        for ($i = 0; $i < 5; $i++) {
            $rootClass->createInstance(self::SAMPLE_ITEMS_LABEL . $i);
        }
    }

    /**
     * Search database for items by label
     *
     * @param $label
     * @return \core_kernel_classes_Resource[]
     */
    private function getItemByLabel($label)
    {
        $class = new \core_kernel_classes_Class('http://www.tao.lu/Ontologies/TAOItem.rdf#Item');
        $items = $class->searchInstances(
            ['http://www.w3.org/2000/01/rdf-schema#label' => $label],
            ['like' => true, 'recursive' => true]
        );

        return $items;
    }


    /**
     * Clearing up, removing created users (if any) and created sample items (if any)
     */
    public function __destruct()
    {
        if ($this->getUserByLogin($this->testAdminData[GenerisRdf::PROPERTY_USER_LOGIN])) {
            $this->userService->removeUser($this->getUserByLogin($this->testAdminData[GenerisRdf::PROPERTY_USER_LOGIN]));
        }
        if ($this->getUserByLogin($this->testAnonymousData[GenerisRdf::PROPERTY_USER_LOGIN])) {
            $this->userService->removeUser($this->getUserByLogin($this->testAnonymousData[GenerisRdf::PROPERTY_USER_LOGIN]));
        }

        $items = $this->getItemByLabel(self::SAMPLE_ITEMS_LABEL);
        if ($items) {
            foreach ($items as $item) {
                if ($item) {
                    $item->delete(true);
                }
            }
        }
    }
}

/**
 * Class FreeAccessTestPermissionProvider - this access provider configured to give access to anyone
 *
 * @author Ilya Yarkavets <ilya.yarkavets@1pt.com>
 * @package oat\tao\test\integration\accessControl
 */
class FreeAccessTestPermissionProvider implements PermissionInterface
{
    public function getPermissions(User $user, array $resourceIds)
    {
        return array_fill_keys($resourceIds, [PermissionInterface::RIGHT_UNSUPPORTED]);
    }

    public function getSupportedRights()
    {
        return [];
    }

    public function onResourceCreated(\core_kernel_classes_Resource $resource)
    {
        // TODO: Implement onResourceCreated() method.
    }
}

/**
 * Class DACTestPermissionProvider - this access provider configured to give access to admin only
 *
 * @author Ilya Yarkavets <ilya.yarkavets@1pt.com>
 * @package oat\tao\test\integration\accessControl
 */
class DACTestPermissionProvider implements PermissionInterface
{
    private $adminPermissions = [
        'WRITE'
    ];

    private $anonPermissions = [
        'NONE'
    ];


    public function getPermissions(User $user, array $resourceIds)
    {
        if (in_array('http://www.tao.lu/Ontologies/TAO.rdf#GlobalManager', $user->getRoles())) {
            return array_fill_keys($resourceIds, $this->adminPermissions);
        } else {
            return array_fill_keys($resourceIds, $this->anonPermissions);
        }
    }

    public function getSupportedRights()
    {
        return [
            'WRITE',
            'NONE'
        ];
    }

    public function onResourceCreated(\core_kernel_classes_Resource $resource)
    {
        // TODO: Implement onResourceCreated() method.
    }
}

/**
 * Class NoAccessTestPermissionProvider - this access provider configured to give access to noone
 *
 * @author Ilya Yarkavets <ilya.yarkavets@1pt.com>
 * @package oat\tao\test\integration\accessControl
 */
class NoAccessTestPermissionProvider implements PermissionInterface
{
    private $permissions = [
        'NONE'
    ];


    public function getPermissions(User $user, array $resourceIds)
    {
        return array_fill_keys($resourceIds, $this->permissions);
    }

    public function getSupportedRights()
    {
        return [
            'NONE'
        ];
    }

    public function onResourceCreated(\core_kernel_classes_Resource $resource)
    {
        // TODO: Implement onResourceCreated() method.
    }
}
