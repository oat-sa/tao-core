<?php

declare(strict_types=1);

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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\test\integration\session;

use common_session_SessionManager;
use oat\tao\model\session\PretenderSession;
use oat\tao\model\session\SessionSubstitutionService;
use oat\tao\test\TaoPhpUnitTestRunner;

/**
 * Test of `\oat\tao\model\session\SessionSubstitutionService`
 *
 * @author Aleh Hutnikau <hutnikau@1pt.com>
 * @package generis\test
 */
class SessionSubstitutionServiceTest extends TaoPhpUnitTestRunner
{
    private $testUserUri = 'http://sample/first.rdf#tessionSubstitutionServiceTestUser';

    protected function setUp(): void
    {
        parent::setUp();
        common_session_SessionManager::startSession(new \common_test_TestUserSession());
    }

    public static function getSubstituteSessionService()
    {
        return [[new SessionSubstitutionService()]];
    }

    /**
     * @param SessionSubstitutionService $service
     * @dataProvider getSubstituteSessionService
     */
    public function testSubstituteSession($service): void
    {
        $initialSession = common_session_SessionManager::getSession();
        $initialUser = $initialSession->getUser();
        $newUser = new \core_kernel_users_GenerisUser(new \core_kernel_classes_Resource($this->testUserUri));

        $newSession = $service->substituteSession($newUser);

        $this->assertSame($newSession, common_session_SessionManager::getSession());
        $this->assertNotSame($initialUser->getIdentifier(), $newSession->getUser()->getIdentifier());
        $this->assertSame($newUser->getIdentifier(), $newSession->getUser()->getIdentifier());
        $this->assertTrue($newSession instanceof PretenderSession);
    }

    /**
     * @param SessionSubstitutionService $service
     * @dataProvider getSubstituteSessionService
     */
    public function testIsSubstituted($service): void
    {
        $this->assertFalse($service->isSubstituted());
        $newUser = new \core_kernel_users_GenerisUser(new \core_kernel_classes_Resource($this->testUserUri));

        $service->substituteSession($newUser);
        $this->assertTrue($service->isSubstituted());
    }

    /**
     * @param SessionSubstitutionService $service
     * @dataProvider getSubstituteSessionService
     */
    public function testRevert($service): void
    {
        $initialSession = common_session_SessionManager::getSession();
        $this->assertFalse($service->isSubstituted());
        $newUser = new \core_kernel_users_GenerisUser(new \core_kernel_classes_Resource($this->testUserUri));

        $service->substituteSession($newUser);
        $this->assertTrue($service->isSubstituted());
        $this->assertSame(common_session_SessionManager::getSession()->getUser()->getIdentifier(), $this->testUserUri);

        $service->revert();

        $this->assertFalse($service->isSubstituted());
        $this->assertNotSame(common_session_SessionManager::getSession()->getUser()->getIdentifier(), $this->testUserUri);
        $this->assertSame($initialSession->getUser()->getIdentifier(), common_session_SessionManager::getSession()->getUser()->getIdentifier());
    }
}
