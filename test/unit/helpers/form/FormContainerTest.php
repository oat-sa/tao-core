<?php declare(strict_types=1);

namespace oat\tao\helpers\test\unit\helpers\form;

use common_cache_Cache;
use common_Exception;
use common_persistence_InMemoryKvDriver;
use common_persistence_KeyValuePersistence;
use oat\generis\persistence\PersistenceManager;
use oat\generis\test\GenerisTestCase;
use oat\oatbox\service\ServiceManager;
use oat\tao\helpers\form\WidgetRegistry;
use oat\tao\model\security\xsrf\Token;
use oat\tao\model\security\xsrf\TokenService;
use oat\tao\model\security\xsrf\TokenStore;
use oat\tao\model\security\xsrf\TokenStoreKeyValue;
use oat\tao\model\service\ApplicationService;
use oat\tao\test\Asset\helper\form\FormContainerStub;
use Psr\Log\NullLogger;
use tao_helpers_form_elements_xhtml_Textbox;
use tao_helpers_form_FormElement;

class FormContainerTest extends GenerisTestCase
{
    private const PERSISTENCE_KEY = 'test';

    private const TOKEN_VALUE = 'token';

    /**
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function setUp(): void
    {
        $config = new common_persistence_KeyValuePersistence([], new common_persistence_InMemoryKvDriver());
        $config->set(ApplicationService::SERVICE_ID, $this->createApplicationServiceTestDouble());
        $config->set(PersistenceManager::SERVICE_ID, $this->createPersistenceManagerTestDouble());
        $config->set(TokenService::SERVICE_ID, $this->createTokenServiceTestDouble());
        $config->set(common_cache_Cache::SERVICE_ID, $this->createCacheTestDouble());

        ServiceManager::setServiceManager(new ServiceManager($config));

        $this->initPersistence();
    }

    /**
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function initPersistence(): void
    {
        /** @var common_persistence_KeyValuePersistence $persistence */
        $persistence = ServiceManager::getServiceManager()
                                     ->get(PersistenceManager::SERVICE_ID)
                                     ->getPersistenceById(self::PERSISTENCE_KEY);

        $persistence->set(
            '_' . TokenStoreKeyValue::TOKENS_STORAGE_KEY,
            json_encode(
                [
                    TokenService::FORM_POOL => [
                        Token::TOKEN_KEY     => self::TOKEN_VALUE,
                        Token::TIMESTAMP_KEY => microtime(true),
                    ],
                ]
            )
        );
    }

    /**
     * @dataProvider dataProvider
     *
     * @param string $expected
     * @param array $options
     * @param tao_helpers_form_FormElement ...$elements
     *
     * @throws common_Exception
     */
    public function testFormRender(
        string $expected,
        array $options = [],
        tao_helpers_form_FormElement ...$elements
    ): void {
        $sut = new FormContainerStub([], $options, ...$elements);

        $this->assertXmlStringEqualsXmlString(
            $expected,
            $sut
                ->getForm()
                ->render()
        );
    }

    public function dataProvider(): array
    {
        $token = self::TOKEN_VALUE;

        return [
            'Simple form'                       => [
                'expected' => <<<HTML
<div class='xhtml_form'>
    <form method='post' id='test' name='test' action=''>
    <input type='hidden' class='global' name='test_sent' value='1'/>
        <div class='form-toolbar'>
            <button type='submit' name='Save' id='Save' class='form-submitter btn-success small' value="Save">
                <span class="icon-save"></span> Save</button>
        </div>
    </form>
</div>
HTML
            ],
            'UI input form' => [
                <<<HTML
<div class='xhtml_form'>
    <form method='post' id='test' name='test' action=''>
    <input type='hidden' class='global' name='test_sent' value='1'/>
    <div>
        <input id="test" name="test" type="text" value=""/>
    </div>
    <input id="X-CSRF-Token" name="X-CSRF-Token" type="hidden" value="$token"/>
        <div class='form-toolbar'>
            <button type='submit' name='Save' id='Save' class='form-submitter btn-success small' value="Save">
                <span class="icon-save"></span> Save</button>
        </div>
    </form>
</div>
HTML
                ,
                'options' => [
                    FormContainerStub::CSRF_PROTECTION_OPTION => true,
                ],
                new tao_helpers_form_elements_xhtml_Textbox('test'),
            ],
            'CSRF form'                         => [
                <<<HTML
<div class='xhtml_form'>
    <form method='post' id='test' name='test' action=''>
    <input type='hidden' class='global' name='test_sent' value='1'/>
    <input id="X-CSRF-Token" name="X-CSRF-Token" type="hidden" value="$token"/>
        <div class='form-toolbar'>
            <button type='submit' name='Save' id='Save' class='form-submitter btn-success small' value="Save">
                <span class="icon-save"></span> Save</button>
        </div>
    </form>
</div>
HTML
                ,
                'options' => [
                    FormContainerStub::CSRF_PROTECTION_OPTION => true,
                ],
            ],
            'Disabled form'                     => [
                <<<HTML
<div class='xhtml_form'>
    <form method='post' id='test' name='test' action=''>
    <input type='hidden' class='global' name='test_sent' value='1'/>
        <div class='form-toolbar'>
            <button disabled="disabled" type='submit' name='Save' id='Save' class='form-submitter btn-success small' value="Save">
                <span class="icon-save"></span> Save</button>
        </div>
    </form>
</div>
HTML
                ,
                'options' => [
                    FormContainerStub::IS_DISABLED => true,
                ],
            ],
            'Disabled CSRF form'                => [
                <<<HTML
<div class='xhtml_form'>
    <form method='post' id='test' name='test' action=''>
    <input type='hidden' class='global' name='test_sent' value='1'/>
    <input id="X-CSRF-Token" name="X-CSRF-Token" type="hidden" value="$token"/>
        <div class='form-toolbar'>
            <button disabled="disabled" type='submit' name='Save' id='Save' class='form-submitter btn-success small' value="Save">
                <span class="icon-save"></span> Save</button>
        </div>
    </form>
</div>
HTML
                ,
                'options' => [
                    FormContainerStub::CSRF_PROTECTION_OPTION => true,
                    FormContainerStub::IS_DISABLED            => true,
                ],
            ],
            'Disabled CSRF form with UI inputs' => [
                <<<HTML
<div class='xhtml_form'>
    <form method='post' id='test' name='test' action=''>
    <input type='hidden' class='global' name='test_sent' value='1'/>
    <div>
        <input disabled="disabled" id="test" name="test" type="text" value=""/>
    </div>
    <input id="X-CSRF-Token" name="X-CSRF-Token" type="hidden" value="$token"/>
        <div class='form-toolbar'>
            <button disabled="disabled" type='submit' name='Save' id='Save' class='form-submitter btn-success small' value="Save">
                <span class="icon-save"></span> Save</button>
        </div>
    </form>
</div>
HTML
                ,
                'options' => [
                    FormContainerStub::CSRF_PROTECTION_OPTION => true,
                    FormContainerStub::IS_DISABLED            => true,
                ],
                new tao_helpers_form_elements_xhtml_Textbox('test'),
            ],
        ];
    }

    private function createApplicationServiceTestDouble(): ApplicationService
    {
        return $this->prophesize(ApplicationService::class)
                    ->reveal();
    }

    private function createPersistenceManagerTestDouble(): PersistenceManager
    {
        return new PersistenceManager(
            [
                PersistenceManager::OPTION_PERSISTENCES => [
                    self::PERSISTENCE_KEY => [
                        'driver' => 'no_storage',
                    ],
                ],
            ]
        );
    }

    private function createTokenServiceTestDouble(): TokenService
    {
        $service = new TokenService(
            [
                TokenService::OPTION_STORE => $this->createTokenStoreTestDouble(),
            ]
        );

        $service->setLogger(new NullLogger());

        return $service;
    }

    private function createTokenStoreTestDouble(): TokenStore
    {
        return new TokenStoreKeyValue(
            [
                TokenStoreKeyValue::OPTION_PERSISTENCE => self::PERSISTENCE_KEY,
            ]
        );
    }

    private function createCacheTestDouble(): common_cache_Cache
    {
        $cacheMock = $this
            ->getMockBuilder(common_cache_Cache::class)
            ->getMock();

        $cacheMock
            ->method('get')
            ->with(WidgetRegistry::CACHE_KEY)
            ->willReturn([]);

        return $cacheMock;
    }
}
