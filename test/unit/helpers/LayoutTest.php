<?php

namespace oat\tao\test\unit\helpers;

use common_exception_Error;
use common_session_AnonymousSession;
use common_session_Session;
use oat\oatbox\user\User;
use oat\tao\helpers\Layout;
use PHPUnit\Framework\TestCase;

class LayoutTest extends TestCase
{
    private $sessionMock = null;

    protected function setUp(): void
    {
        $this->setEnv('NODE_ENV', 'production');

        Layout::setTemplate(TemplateMock::class);
    }

    protected function tearDown(): void
    {
        TemplateMock::resetCalls();
    }

    public function testGetAnalyticsCodeWithGaTag(): void
    {
        $this->setEnv('GA_TAG', 'dummy-ga-tag');

        Layout::getAnalyticsCode();

        self::assertSame(
            [
                [
                    'oat\tao\test\unit\helpers\TemplateMock::inc' => [
                        'blocks/analytics.tpl',
                        'tao',
                        [
                            'gaTag' => 'dummy-ga-tag',
                            'environment' => 'Production'
                        ]
                    ]
                ]
            ],
            TemplateMock::getCalls()
        );
    }

    public function testGetAnalyticsCodeWithoutGaTag(): void
    {
        $this->setEnv('GA_TAG', '');

        Layout::getAnalyticsCode();

        self::assertSame(
            [],
            TemplateMock::getCalls()
        );
    }

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
            $userMock->expects(self::once())->method('getIdentifier')->willReturn($userIdentifier);
            $userMock->expects(self::exactly(2))->method('getPropertyValues')->willReturnOnConsecutiveCalls($login, $email);

            $this->sessionMock = $this->createMock($sessionMockClass);
            $this->sessionMock->expects(self::once())->method('getUser')->willReturn($userMock);
            $this->sessionMock->expects(self::once())->method('getUserLabel')->willReturn($userLabel);
            $this->sessionMock->expects(self::once())->method('getUserRoles')->willReturn($userRole);
        }

        Layout::setSession($this->sessionMock);
        Layout::getUserPilotCode();

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
                            'blocks/userpilot.tpl',
                            'tao',
                            [
                                'userpilot_data' => [
                                    'token' => 'dummy-user-pilot-token',
                                    'user' => [
                                        'id' => 'N/A',
                                        'name' => 'guest',
                                        'login' => 'N/A',
                                        'email' => 'N/A',
                                        'roles' => 'http://www.tao.lu/Ontologies/generis.rdf#AnonymousRole',
                                        'interface_language' => 'en-US'
                                    ],
                                    'tenant' => [
                                        'id' => 'N/A',
                                        'name' => 'N/A'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                common_session_AnonymousSession::class,
                null,
                'guest',
                ['http://www.tao.lu/Ontologies/generis.rdf#AnonymousRole']
            ],
            'Superuser session' => [
                'dummy-user-pilot-token',
                [
                    [
                        'oat\tao\test\unit\helpers\TemplateMock::inc' => [
                            'blocks/userpilot.tpl',
                            'tao',
                            [
                                'userpilot_data' => [
                                    'token' => 'dummy-user-pilot-token',
                                    'user' => [
                                        'id' => 'http://backoffice.docker.localhost/ontologies/tao.rdf#superUser',
                                        'name' => 'admin',
                                        'login' => 'admin',
                                        'email' => 'admin@taotesting.com',
                                        'roles' =>
                                            'http://www.tao.lu/Ontologies/TAO.rdf#GlobalManagerRole,'
                                            . 'http://www.tao.lu/Ontologies/generis.rdf#remoteProctoringManager,'
                                            . 'http://www.tao.lu/Ontologies/TAO.rdf#BackOfficeRole,'
                                            . 'http://www.tao.lu/Ontologies/TAO.rdf#BaseUserRole,'
                                            . 'http://www.tao.lu/Ontologies/generis.rdf#AnonymousRole,'
                                            . 'http://www.tao.lu/Ontologies/generis.rdf#GenerisRole,'
                                            . 'http://www.tao.lu/Ontologies/generis.rdf#taoScoringServiceConnectManager,'
                                            . 'http://www.tao.lu/Ontologies/generis.rdf#taoDeliverConnectManager,'
                                            . 'http://www.tao.lu/Ontologies/generis.rdf#taoTaskQueueManager,'
                                            . 'http://www.tao.lu/Ontologies/generis.rdf#taoTestPreviewUILoaderManager,'
                                            . 'http://www.tao.lu/Ontologies/generis.rdf#ltiTestReviewManager,'
                                            . 'http://www.tao.lu/Ontologies/generis.rdf#taoCeManager,'
                                            . 'http://www.tao.lu/Ontologies/TAOProctor.rdf#TestCenterManager,'
                                            . 'http://www.tao.lu/Ontologies/generis.rdf#taoEventLogManager,'
                                            . 'http://www.tao.lu/Ontologies/taoFuncACL.rdf#FuncAclManagerRole,'
                                            . 'http://www.tao.lu/Ontologies/TAOLTI.rdf#LtiDeliveryProviderManagerRole,'
                                            . 'http://www.tao.lu/Ontologies/taoLti.rdf#LtiOutcomeUiManagerRole,'
                                            . 'http://www.tao.lu/Ontologies/TAOResult.rdf#ResultsManagerRole,'
                                            . 'http://www.tao.lu/Ontologies/generis.rdf#taoLtiConsumerManager,'
                                            . 'http://www.tao.lu/Ontologies/generis.rdf#taoDeliveryRdfManager,'
                                            . 'http://www.tao.lu/Ontologies/TAOMedia.rdf#MediaManagerRole,'
                                            . 'http://www.tao.lu/Ontologies/TAOTest.rdf#TaoQtiTestPreviewerRole,'
                                            . 'http://www.tao.lu/Ontologies/TAOTest.rdf#TaoQtiManagerRole,'
                                            . 'http://www.tao.lu/Ontologies/TAOItem.rdf#QTIManagerRole,'
                                            . 'http://www.tao.lu/Ontologies/generis.rdf#qtiItemPciManager,'
                                            . 'http://www.tao.lu/Ontologies/TAOTest.rdf#TestsManagerRole,'
                                            . 'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemsManagerRole,'
                                            . 'http://www.tao.lu/Ontologies/TAOGroup.rdf#GroupsManagerRole,'
                                            . 'http://www.tao.lu/Ontologies/TAOSubject.rdf#SubjectsManagerRole,'
                                            . 'http://www.tao.lu/Ontologies/generis.rdf#taoBackOfficeManager,'
                                            . 'http://www.tao.lu/Ontologies/TAOResultServer.rdf#ResultServerRole,'
                                            . 'http://www.tao.lu/Ontologies/TAOLTI.rdf#LtiManagerRole,'
                                            . 'http://www.tao.lu/Ontologies/TAO.rdf#TaoManagerRole,'
                                            . 'http://www.tao.lu/Ontologies/TAO.rdf#SysAdminRole',
                                        'interface_language' => 'en-US'
                                    ],
                                    'tenant' => [
                                        'id' => 'N/A',
                                        'name' => 'N/A'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                common_session_Session::class,
                'http://backoffice.docker.localhost/ontologies/tao.rdf#superUser',
                'admin',
                [
                    'http://www.tao.lu/Ontologies/TAO.rdf#GlobalManagerRole',
                    'http://www.tao.lu/Ontologies/generis.rdf#remoteProctoringManager',
                    'http://www.tao.lu/Ontologies/TAO.rdf#BackOfficeRole',
                    'http://www.tao.lu/Ontologies/TAO.rdf#BaseUserRole',
                    'http://www.tao.lu/Ontologies/generis.rdf#AnonymousRole',
                    'http://www.tao.lu/Ontologies/generis.rdf#GenerisRole',
                    'http://www.tao.lu/Ontologies/generis.rdf#taoScoringServiceConnectManager',
                    'http://www.tao.lu/Ontologies/generis.rdf#taoDeliverConnectManager',
                    'http://www.tao.lu/Ontologies/generis.rdf#taoTaskQueueManager',
                    'http://www.tao.lu/Ontologies/generis.rdf#taoTestPreviewUILoaderManager',
                    'http://www.tao.lu/Ontologies/generis.rdf#ltiTestReviewManager',
                    'http://www.tao.lu/Ontologies/generis.rdf#taoCeManager',
                    'http://www.tao.lu/Ontologies/TAOProctor.rdf#TestCenterManager',
                    'http://www.tao.lu/Ontologies/generis.rdf#taoEventLogManager',
                    'http://www.tao.lu/Ontologies/taoFuncACL.rdf#FuncAclManagerRole',
                    'http://www.tao.lu/Ontologies/TAOLTI.rdf#LtiDeliveryProviderManagerRole',
                    'http://www.tao.lu/Ontologies/taoLti.rdf#LtiOutcomeUiManagerRole',
                    'http://www.tao.lu/Ontologies/TAOResult.rdf#ResultsManagerRole',
                    'http://www.tao.lu/Ontologies/generis.rdf#taoLtiConsumerManager',
                    'http://www.tao.lu/Ontologies/generis.rdf#taoDeliveryRdfManager',
                    'http://www.tao.lu/Ontologies/TAOMedia.rdf#MediaManagerRole',
                    'http://www.tao.lu/Ontologies/TAOTest.rdf#TaoQtiTestPreviewerRole',
                    'http://www.tao.lu/Ontologies/TAOTest.rdf#TaoQtiManagerRole',
                    'http://www.tao.lu/Ontologies/TAOItem.rdf#QTIManagerRole',
                    'http://www.tao.lu/Ontologies/generis.rdf#qtiItemPciManager',
                    'http://www.tao.lu/Ontologies/TAOTest.rdf#TestsManagerRole',
                    'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemsManagerRole',
                    'http://www.tao.lu/Ontologies/TAOGroup.rdf#GroupsManagerRole',
                    'http://www.tao.lu/Ontologies/TAOSubject.rdf#SubjectsManagerRole',
                    'http://www.tao.lu/Ontologies/generis.rdf#taoBackOfficeManager',
                    'http://www.tao.lu/Ontologies/TAOResultServer.rdf#ResultServerRole',
                    'http://www.tao.lu/Ontologies/TAOLTI.rdf#LtiManagerRole',
                    'http://www.tao.lu/Ontologies/TAO.rdf#TaoManagerRole',
                    'http://www.tao.lu/Ontologies/TAO.rdf#SysAdminRole'
                ],
                ['admin'],
                ['admin@taotesting.com']
            ],
        ];
    }

    private function setEnv($key, $value): void
    {
        putenv("$key=$value");
        $_ENV[$key] = $value;
    }
}
