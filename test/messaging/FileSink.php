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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA;
 *               
 */
use oat\tao\test\TaoPhpUnitTestRunner;
use oat\tao\model\messaging\Message;
use oat\tao\model\messaging\transportStrategy\FileSink;

include_once dirname(__FILE__) . '/../../includes/raw_start.php';

/**
 * @author Aleh Hutnikau <hutnikau@1pt.com>
 * @package tao
 */
class FileSinkTest extends TaoPhpUnitTestRunner
{

    public function testSend()
    {
        $testfolder = tao_helpers_File::createTempDir();
        
        $expectedFilePath = $testfolder.'testidentifier'.DIRECTORY_SEPARATOR.'message.html';
        $this->assertFileNotExists($expectedFilePath);
        
        $userMock = $this->getMock('oat\oatbox\user\User');
        $userMock->expects($this->once())
        ->method('getIdentifier')
        ->will($this->returnValue('testidentifier'));
        
        
        $messageMock = $this->getMock('oat\tao\model\messaging\Message');
        $messageMock->expects($this->once())
            ->method('getTo')
            ->will($this->returnValue($userMock));
        $messageMock->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue('testBody'));
        
        
        $transporter = new FileSink(array(FileSink::CONFIG_FILEPATH => $testfolder));
        $result = $transporter->send($messageMock);
        
        $this->assertTrue($result);
        $this->assertFileExists($expectedFilePath);
        
        $messageContent = file_get_contents($expectedFilePath);
        
        $this->assertEquals('testBody', $messageContent);
        
        tao_helpers_File::delTree($testfolder);
        $this->assertFalse(is_dir($testfolder));
    }
}
