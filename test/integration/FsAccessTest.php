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
 * Copyright (c) 2008-2010 (original work) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 *               2013-2015 (update and modification) Open Assessment Technologies SA;
 */

namespace oat\tao\test\integration;

use oat\oatbox\filesystem\FileSystem;
use oat\tao\model\asset\AssetService;
use oat\tao\model\websource\Websource;
use oat\tao\test\TaoPhpUnitTestRunner;
use oat\tao\model\websource\WebsourceManager;
use oat\tao\model\websource\ActionWebSource;
use oat\tao\model\websource\TokenWebSource;
use oat\tao\model\websource\DirectWebSource;
use oat\oatbox\service\ServiceManager;
use oat\oatbox\filesystem\FileSystemService;
use oat\tao\model\user\TaoRoles;
use oat\tao\model\websource\BaseWebsource;
use core_kernel_classes_Resource;
use core_kernel_uri_UriService;
use tao_models_classes_UserService;
use common_ext_ExtensionsManager;
use common_Exception;
use oat\generis\model\GenerisRdf;

/**
 * @author Cédric Alfonsi, <taosupport@tudor.lu>
 * @package tao
 */
class tao_test_FsAccessTest extends TaoPhpUnitTestRunner {

    const TEST_USER_LOGIN = 'FsAccessTestUser';

    private $testUser;
    private $credentials = array();
    
    /**
     * @var FileSystem
     */
    private $fileSystem = null;
    
    protected function setUp()
    {
        $this->disableCache();
        $pass = md5(rand());
        $taoManagerRole = new core_kernel_classes_Resource(TaoRoles::BACK_OFFICE);

        // @TODO: Required to remove test users from previous test execution.
        //        Eliminate usage of singletors and use MYSQLite db mock instead of real db.
        $filters = [GenerisRdf::PROPERTY_USER_LOGIN => self::TEST_USER_LOGIN];
        $formerTestUsers = tao_models_classes_UserService::singleton()->getAllUsers([], $filters);
        foreach ($formerTestUsers as $testUser) {
            if ($testUser instanceof core_kernel_classes_Resource) {
                $testUser->delete();
            }
        }
        if (!$this->testUser) {
            $this->testUser = tao_models_classes_UserService::singleton()->addUser(self::TEST_USER_LOGIN, $pass, $taoManagerRole );
        }
        $this->credentials = array(
            'loginForm_sent' => 1,
            'login' => self::TEST_USER_LOGIN,
            'password' => $pass,
        );

        parent::setUp();
    }
    
    public function tearDown() {
        $this->restoreCache();
        parent::tearDown();
        if($this->testUser instanceof core_kernel_classes_Resource){
            $this->testUser->delete();
        }

        if (!is_null($this->fileSystem)) {
            $serviceManager = ServiceManager::getServiceManager();
            /** @var FileSystemService $fsm */
            $fsm = $serviceManager->get(FileSystemService::SERVICE_ID);
            $fsm->unregisterFileSystem($this->fileSystem->getId());
            $serviceManager->register(FileSystemService::SERVICE_ID, $fsm);
        }
    }

    /**
     * @return BaseWebsource
     */
    private function getWebsourceMock() {
        $websource = $this->prophesize(BaseWebsource::class);
        $websource->getId()->willReturn('fake');
        $websource->getOptions()->willReturn('options');
        return $websource->reveal();
    }
    
    /**
     * @author Lionel Lecaque, lionel@taotesting.com
     */
    public function testAddWebSourceException()
    {
        $this->expectException(common_Exception::class);

        $websource = $this->prophesize(BaseWebsource::class);
        WebsourceManager::singleton()->addWebsource($websource->reveal());
    }
    
    /**
     * 
     * @author Lionel Lecaque, lionel@taotesting.com
     */
    public function testAddWebSource()
    {
        $ext = common_ext_ExtensionsManager::singleton()->getExtensionById('tao');
        $this->assertFalse($ext->hasConfig(WebsourceManager::CONFIG_PREFIX.'fake'));

        $websourceMock = $this->getWebsourceMock();
        WebsourceManager::singleton()->addWebsource($websourceMock);

        $config = $ext->getConfig(WebsourceManager::CONFIG_PREFIX.'fake');

        $expected = array( 'className' => get_class($websourceMock) , 'options' => 'options');
        $this->assertEquals($expected, $config);
    }
    
    
    
    /**
     * @author Lionel Lecaque, lionel@taotesting.com
     */
    public function testRemoveWebSource()
    {
        $websourceMock = $this->getWebsourceMock();
        WebsourceManager::singleton()->addWebsource($websourceMock);

        $ext = common_ext_ExtensionsManager::singleton()->getExtensionById('tao');
        $this->assertTrue($ext->hasConfig(WebsourceManager::CONFIG_PREFIX.'fake'));
        
        WebsourceManager::singleton()->removeWebsource($websourceMock);
        $this->assertFalse($ext->hasConfig(WebsourceManager::CONFIG_PREFIX.'fake'));
    }

    /**
     * @author Lionel Lecaque, lionel@taotesting.com
     */
    public function testRemoveWebSourceException()
    {
        $this->expectException(common_Exception::class);

        $websource = $this->getWebsourceMock();
        WebsourceManager::singleton()->removeWebsource($websource);
    }

    public function testDirectWebsourceProvider() {
        $ext = common_ext_ExtensionsManager::singleton()->getExtensionById('tao');
        $assetService = ServiceManager::getServiceManager()->get(AssetService::SERVICE_ID);
        $this->registerFileSystem($ext);

        $websource = DirectWebSource::spawnWebsource($this->fileSystem->getId(), $assetService->getJsBaseWww( $ext->getId() ));

        $this->runWebsourceTests($websource);
    }

    public function testTokenWebsourceProvider() {
        $ext = common_ext_ExtensionsManager::singleton()->getExtensionById('tao');
        $this->registerFileSystem($ext);

        $websource = TokenWebSource::spawnWebsource($this->fileSystem->getId(), $this->fileSystem->getAdapter()->getPathPrefix());

        $this->runWebsourceTests($websource);
    }

    public function testActionWebsourceProvider() {
        $ext = common_ext_ExtensionsManager::singleton()->getExtensionById('tao');
        $this->registerFileSystem($ext);

        $websource = ActionWebSource::spawnWebsource($this->fileSystem->getId());

        $this->runWebsourceTests($websource);
    }

    private function registerFileSystem(\common_ext_Extension $ext) {
        $serviceManager = ServiceManager::getServiceManager();
        $fsm = $serviceManager->get(FileSystemService::SERVICE_ID);
        $fsId = core_kernel_uri_UriService::singleton()->generateUri();
        $fsm->registerLocalFileSystem($fsId, $ext->getConstant('DIR_VIEWS'));
        $serviceManager->register(FileSystemService::SERVICE_ID, $fsm);
        $this->fileSystem = $fsm->getFileSystem($fsId);
    }

    /**
     * @param $websource
     * @throws \oat\tao\model\websource\WebsourceNotFound
     * @throws common_Exception
     */
    private function runWebsourceTests($websource)
    {
        $this->assertInstanceOf(Websource::class, $websource);
        $id = $websource->getId();

        $fromManager = WebsourceManager::singleton()->getWebsource($id);
        $this->assertInstanceOf(Websource::class, $fromManager);

        $url = $websource->getAccessUrl('img' . DIRECTORY_SEPARATOR . 'tao.png');
        $this->assertTrue($websource->getFileSystem()->has('img' . DIRECTORY_SEPARATOR . 'tao.png'), 'reference file not found');
        $this->assertUrlHttpCode($url);

        $url = $websource->getAccessUrl('img' . DIRECTORY_SEPARATOR . 'fakeFile_thatDoesNotExist.png');
        $this->assertFalse($websource->getFileSystem()->has('img' . DIRECTORY_SEPARATOR . 'fakeFile_thatDoesNotExist.png'), 'reference file should not be found');
        $this->assertUrlHttpCode($url, '404');

        $url = $websource->getAccessUrl('img' . DIRECTORY_SEPARATOR);
        $this->assertUrlHttpCode($url . 'tao.png');

        $url = $websource->getAccessUrl('css' . DIRECTORY_SEPARATOR);
        $this->assertUrlHttpCode($url . 'font/tao/tao.woff');

        $url = $websource->getAccessUrl('');
        $this->assertUrlHttpCode($url . 'img/tao.png');

        WebsourceManager::singleton()->removeWebsource($websource);

        $this->expectException(\Exception::class);

        WebsourceManager::singleton()->getWebsource($id);
    }
    
    private function assertUrlHttpCode($url, $expectedCode = 200) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, true);    // we want headers
        curl_setopt($ch, CURLOPT_NOBODY, true);    // we don't need body
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_TIMEOUT,10);
        curl_setopt($ch, CURLOPT_COOKIE, $this->getSessionCookie($this->testUser));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $output = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $r = curl_getinfo($ch);
        curl_close($ch);
        
        $this->assertEquals($expectedCode, $httpCode, 'Incorrect response for '.$url);
    }
    
    private function getSessionCookie(core_kernel_classes_Resource $user) {
        // login
        $ch = curl_init(_url('login', 'Main', 'tao'));
        curl_setopt($ch,CURLOPT_POST, count($this->credentials)); // does not work with no body
        curl_setopt($ch,CURLOPT_POSTFIELDS, $this->credentials);
        curl_setopt($ch, CURLOPT_HEADER, true);    // we want headers
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $output = curl_exec($ch);
        
        // get cookie
        preg_match('/^Set-Cookie:\s*([^;]*)/mi', $output, $m);
        $this->assertTrue(isset($m[1]), 'Failed to get Session Cookie');
        return $m[1];
        
    }
}
