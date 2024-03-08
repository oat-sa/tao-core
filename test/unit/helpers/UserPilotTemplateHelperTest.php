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
 * Copyright (c) 2024 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\test\unit\helpers;

use common_exception_Error;
use common_session_AnonymousSession;
use common_session_Session;
use oat\oatbox\user\User;
use oat\tao\helpers\UserPilotTemplateHelper;
use oat\tao\model\session\Context\TenantDataSessionContext;
use PHPUnit\Framework\MockObject\MockObject;

class UserPilotTemplateHelperTest extends LayoutTest
{
    private null|common_session_Session|MockObject $sessionMock = null;

    /**
     * @throws common_exception_Error
     *
     * @dataProvider provideUserPilotData
     */
    public function testGetUserPilotCode(
        string $userPilotToken,
        array $expectedCalls,
        string $sessionMockClass = null,
        string $userIdentifier = null,
        string $userLabel = null,
        array $userRole = null,
        array $login = null,
        array $email = null
    ): void {
        $this->setEnv('USER_PILOT_TOKEN', $userPilotToken);

        if (null !== $sessionMockClass) {
            $userMock = $this->createMock(User::class);
            $userMock
                ->expects(self::once())
                ->method('getIdentifier')
                ->willReturn($userIdentifier);
            $userMock
                ->expects(self::exactly(2))
                ->method('getPropertyValues')
                ->willReturnOnConsecutiveCalls($login, $email);
            $tenantContext = $this->createMock(TenantDataSessionContext::class);
            $tenantContext
                ->expects(self::once())
                ->method('getTenantId')
                ->willReturn('portal-authoring-client-id-local-dev-acc.nextgen-stack-local');

            $this->sessionMock = $this->createMock($sessionMockClass);
            $this->sessionMock->expects(self::once())->method('getUser')->willReturn($userMock);
            $this->sessionMock->expects(self::once())->method('getUserLabel')->willReturn($userLabel);
            $this->sessionMock->expects(self::once())->method('getUserRoles')->willReturn($userRole);
            $this->sessionMock->expects(self::once())->method('getContexts')->willReturn([$tenantContext]);
        }

        UserPilotTemplateHelper::setSession($this->sessionMock);
        UserPilotTemplateHelper::getUserPilotCode();

        self::assertSame($expectedCalls, TemplateMock::getCalls());
    }

    public function provideUserPilotData(): array
    {
        return [
            'No session' => [
                '',
                []
            ],
            'Anonymous session' => [
                'dummy-user-pilot-token',
                [
                    [
                        'oat\tao\test\unit\helpers\TemplateMock::inc' => [
                            UserPilotTemplateHelper::USER_PILOT_TEMPLATE,
                            'tao',
                            [
                                'userpilot_data' => [
                                    'token' => 'dummy-user-pilot-token',
                                    'user' => [
                                        'id' =>
                                            'portal-authoring-client-id-local-dev-acc.nextgen-stack-local|'
                                            . UserPilotTemplateHelper::NOT_AVAILABLE,
                                        'name' => 'guest',
                                        'login' => UserPilotTemplateHelper::NOT_AVAILABLE,
                                        'email' => UserPilotTemplateHelper::NOT_AVAILABLE,
                                        'roles' => 'https://www.tao.lu/Ontologies/generis.rdf#AnonymousRole',
                                        'interface_language' => UserPilotTemplateHelper::DEFAULT_LOCALE
                                    ],
                                    'tenant' => [
                                        'id' => 'portal-authoring-client-id-local-dev-acc.nextgen-stack-local',
                                        'name' => 'portal-authoring-client-id-local-dev-acc.nextgen-stack-local'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                common_session_AnonymousSession::class,
                null,
                'guest',
                ['https://www.tao.lu/Ontologies/generis.rdf#AnonymousRole']
            ],
            'Superuser session' => [
                'dummy-user-pilot-token',
                [
                    [
                        'oat\tao\test\unit\helpers\TemplateMock::inc' => [
                            UserPilotTemplateHelper::USER_PILOT_TEMPLATE,
                            'tao',
                            [
                                'userpilot_data' => [
                                    'token' => 'dummy-user-pilot-token',
                                    'user' => [
                                        'id' =>
                                            'portal-authoring-client-id-local-dev-acc.nextgen-stack-local|'
                                            . 'https://backoffice.ngs.test/ontologies/tao.rdf#superUser',
                                        'name' => 'admin',
                                        'login' => 'admin',
                                        'email' => 'admin@taotesting.com',
                                        'roles' =>
                                            'https://www.tao.lu/Ontologies/TAO.rdf#GlobalManagerRole,'
                                            . 'https://www.tao.lu/Ontologies/generis.rdf#remoteProctoringManager,'
                                            . 'https://www.tao.lu/Ontologies/TAO.rdf#BackOfficeRole,'
                                            . 'https://www.tao.lu/Ontologies/TAO.rdf#BaseUserRole,'
                                            . 'https://www.tao.lu/Ontologies/generis.rdf#AnonymousRole,'
                                            . 'https://www.tao.lu/Ontologies/generis.rdf#GenerisRole,'
                                            . 'https://www.tao.lu/Ontologies/generis.rdf'
                                            . '#taoScoringServiceConnectManager,'
                                            . 'https://www.tao.lu/Ontologies/generis.rdf#taoDeliverConnectManager,'
                                            . 'https://www.tao.lu/Ontologies/generis.rdf#taoTaskQueueManager,'
                                            . 'https://www.tao.lu/Ontologies/generis.rdf#taoTestPreviewUILoaderManager,'
                                            . 'https://www.tao.lu/Ontologies/generis.rdf#ltiTestReviewManager,'
                                            . 'https://www.tao.lu/Ontologies/generis.rdf#taoCeManager,'
                                            . 'https://www.tao.lu/Ontologies/TAOProctor.rdf#TestCenterManager,'
                                            . 'https://www.tao.lu/Ontologies/generis.rdf#taoEventLogManager,'
                                            . 'https://www.tao.lu/Ontologies/taoFuncACL.rdf#FuncAclManagerRole,'
                                            . 'https://www.tao.lu/Ontologies/TAOLTI.rdf#LtiDeliveryProviderManagerRole,'
                                            . 'https://www.tao.lu/Ontologies/taoLti.rdf#LtiOutcomeUiManagerRole,'
                                            . 'https://www.tao.lu/Ontologies/TAOResult.rdf#ResultsManagerRole,'
                                            . 'https://www.tao.lu/Ontologies/generis.rdf#taoLtiConsumerManager,'
                                            . 'https://www.tao.lu/Ontologies/generis.rdf#taoDeliveryRdfManager,'
                                            . 'https://www.tao.lu/Ontologies/TAOMedia.rdf#MediaManagerRole,'
                                            . 'https://www.tao.lu/Ontologies/TAOTest.rdf#TaoQtiTestPreviewerRole,'
                                            . 'https://www.tao.lu/Ontologies/TAOTest.rdf#TaoQtiManagerRole,'
                                            . 'https://www.tao.lu/Ontologies/TAOItem.rdf#QTIManagerRole,'
                                            . 'https://www.tao.lu/Ontologies/generis.rdf#qtiItemPciManager,'
                                            . 'https://www.tao.lu/Ontologies/TAOTest.rdf#TestsManagerRole,'
                                            . 'https://www.tao.lu/Ontologies/TAOItem.rdf#ItemsManagerRole,'
                                            . 'https://www.tao.lu/Ontologies/TAOGroup.rdf#GroupsManagerRole,'
                                            . 'https://www.tao.lu/Ontologies/TAOSubject.rdf#SubjectsManagerRole,'
                                            . 'https://www.tao.lu/Ontologies/generis.rdf#taoBackOfficeManager,'
                                            . 'https://www.tao.lu/Ontologies/TAOResultServer.rdf#ResultServerRole,'
                                            . 'https://www.tao.lu/Ontologies/TAOLTI.rdf#LtiManagerRole,'
                                            . 'https://www.tao.lu/Ontologies/TAO.rdf#TaoManagerRole,'
                                            . 'https://www.tao.lu/Ontologies/TAO.rdf#SysAdminRole',
                                        'interface_language' => 'en-US'
                                    ],
                                    'tenant' => [
                                        'id' => 'portal-authoring-client-id-local-dev-acc.nextgen-stack-local',
                                        'name' => 'portal-authoring-client-id-local-dev-acc.nextgen-stack-local'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                common_session_Session::class,
                'https://backoffice.ngs.test/ontologies/tao.rdf#superUser',
                'admin',
                [
                    'https://www.tao.lu/Ontologies/TAO.rdf#GlobalManagerRole',
                    'https://www.tao.lu/Ontologies/generis.rdf#remoteProctoringManager',
                    'https://www.tao.lu/Ontologies/TAO.rdf#BackOfficeRole',
                    'https://www.tao.lu/Ontologies/TAO.rdf#BaseUserRole',
                    'https://www.tao.lu/Ontologies/generis.rdf#AnonymousRole',
                    'https://www.tao.lu/Ontologies/generis.rdf#GenerisRole',
                    'https://www.tao.lu/Ontologies/generis.rdf#taoScoringServiceConnectManager',
                    'https://www.tao.lu/Ontologies/generis.rdf#taoDeliverConnectManager',
                    'https://www.tao.lu/Ontologies/generis.rdf#taoTaskQueueManager',
                    'https://www.tao.lu/Ontologies/generis.rdf#taoTestPreviewUILoaderManager',
                    'https://www.tao.lu/Ontologies/generis.rdf#ltiTestReviewManager',
                    'https://www.tao.lu/Ontologies/generis.rdf#taoCeManager',
                    'https://www.tao.lu/Ontologies/TAOProctor.rdf#TestCenterManager',
                    'https://www.tao.lu/Ontologies/generis.rdf#taoEventLogManager',
                    'https://www.tao.lu/Ontologies/taoFuncACL.rdf#FuncAclManagerRole',
                    'https://www.tao.lu/Ontologies/TAOLTI.rdf#LtiDeliveryProviderManagerRole',
                    'https://www.tao.lu/Ontologies/taoLti.rdf#LtiOutcomeUiManagerRole',
                    'https://www.tao.lu/Ontologies/TAOResult.rdf#ResultsManagerRole',
                    'https://www.tao.lu/Ontologies/generis.rdf#taoLtiConsumerManager',
                    'https://www.tao.lu/Ontologies/generis.rdf#taoDeliveryRdfManager',
                    'https://www.tao.lu/Ontologies/TAOMedia.rdf#MediaManagerRole',
                    'https://www.tao.lu/Ontologies/TAOTest.rdf#TaoQtiTestPreviewerRole',
                    'https://www.tao.lu/Ontologies/TAOTest.rdf#TaoQtiManagerRole',
                    'https://www.tao.lu/Ontologies/TAOItem.rdf#QTIManagerRole',
                    'https://www.tao.lu/Ontologies/generis.rdf#qtiItemPciManager',
                    'https://www.tao.lu/Ontologies/TAOTest.rdf#TestsManagerRole',
                    'https://www.tao.lu/Ontologies/TAOItem.rdf#ItemsManagerRole',
                    'https://www.tao.lu/Ontologies/TAOGroup.rdf#GroupsManagerRole',
                    'https://www.tao.lu/Ontologies/TAOSubject.rdf#SubjectsManagerRole',
                    'https://www.tao.lu/Ontologies/generis.rdf#taoBackOfficeManager',
                    'https://www.tao.lu/Ontologies/TAOResultServer.rdf#ResultServerRole',
                    'https://www.tao.lu/Ontologies/TAOLTI.rdf#LtiManagerRole',
                    'https://www.tao.lu/Ontologies/TAO.rdf#TaoManagerRole',
                    'https://www.tao.lu/Ontologies/TAO.rdf#SysAdminRole'
                ],
                ['admin'],
                ['admin@taotesting.com']
            ],
        ];
    }
}
