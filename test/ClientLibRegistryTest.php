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
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */
namespace oat\tao\test;

use oat\tao\model\ClientLibRegistry;
use oat\tao\test\TaoPhpUnitTestRunner;

/**
 * 
 * @author Lionel Lecaque, lionel@taotesting.com
 */
class ClientLibRegistryTest extends TaoPhpUnitTestRunner
{

    /** @var string */
    protected $baseWwwStub = '';

    /** @var array */
    protected $realExtensionConstants = [];

    /**
     * Initialize TestRunner and add TestCase settings
     *
     * @author Lionel Lecaque, lionel@taotesting.com
     */
    public function setUp()
    {
        TaoPhpUnitTestRunner::initTest();

        $this->baseWwwStub = 'http://taotesting.com/samples/fakeSourceCode/views/';
    }

    /**
     * @param string $extensionId
     * @param array  $stubConstants
     */
    protected function stubExtensionConstants($extensionId, $stubConstants)
    {
        $this->restoreExtensionConstants($extensionId);

        $ext = \common_ext_ExtensionsManager::singleton()->getExtensionById($extensionId);
        if( !is_null($ext) )
        {
            $this->realExtensionConstants[$extensionId] = $ext->getManifest()->getConstants();

            foreach( $stubConstants as $name => $value){
                if( !array_key_exists($name, $this->realExtensionConstants[$extensionId]) ){
                    unset( $stubConstants[$name] );
                }
            }

            $newConstants = array_merge($this->realExtensionConstants[$extensionId], $stubConstants);

            $this->invokeProtectedMethod($ext->getManifest(), 'setConstants', array( $newConstants ));
        }
    }

    /**
     * Restores stubbed extension constants back to initial state
     *
     * @param string $extensionId
     */
    protected function restoreExtensionConstants($extensionId)
    {
        if( !isset($this->realExtensionConstants[$extensionId]) ){
            return;
        }

        $ext = \common_ext_ExtensionsManager::singleton()->getExtensionById($extensionId);
        if( !is_null($ext) )
        {
            $this->invokeProtectedMethod($ext->getManifest(), 'setConstants', array($this->realExtensionConstants[$extensionId]));
        }
    }

    /**
     * Test:
     *  - {@link ClientLibRegistry::getMap}
     *  - {@link ClientLibRegistry::register}
     *  - {@link ClientLibRegistry::remove}
     */
    public function testRegister()
    {
        $this->stubExtensionConstants(
            'tao',
            array( 'BASE_WWW' => $this->baseWwwStub )
        );

        $shortDirname = 'js/';

        $map = ClientLibRegistry::getRegistry()->getMap();
        $this->assertFalse(empty($map));

        ClientLibRegistry::getRegistry()->register('OAT/test', $this->baseWwwStub . $shortDirname);
        $map = ClientLibRegistry::getRegistry()->getMap();

        $this->assertInternalType('array', $map);
        $this->assertTrue(isset($map['OAT/test']));
        $this->assertTrue(isset($map['OAT/test']['path']));
        $this->assertEquals($shortDirname, $map['OAT/test']['path']);
        
        ClientLibRegistry::getRegistry()->remove('OAT/test');

        $map = ClientLibRegistry::getRegistry()->getMap();
        $this->assertFalse(isset($map['OAT/test']));

        $this->restoreExtensionConstants('tao');
    }


}

?>