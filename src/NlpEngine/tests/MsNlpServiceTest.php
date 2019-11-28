<?php

namespace OpenDialogAi\NlpEngine\Tests;

use OpenDialogAi\Core\NlpEngine\Client\MsClient;
use OpenDialogAi\Core\NlpEngine\Service\MsNlpService;
use OpenDialogAi\Core\Tests\TestCase;

/**
 * Class MsNlpServiceTest
 *
 * @package OpenDialogAi\NlpEngine\Tests
 */
class MsNlpServiceTest extends TestCase
{
    /** @var \OpenDialogAi\Core\NlpEngine\Service\MsNlpService */
    private $msNlpService;

    /** @var \Mockery\LegacyMockInterface|\Mockery\MockInterface  */
    private $guzzleClientMock;

    /** @var \Mockery\LegacyMockInterface|\Mockery\MockInterface|\OpenDialogAi\Core\NlpEngine\Client\MsClient  */
    private $clientMock;

    const LANG_STUB_RESPONSE = [
        [
            'name' => 'English',
            'iso6391Name' => 'en',
            'score' => 1.0,
        ]
    ];

    public function setUp(): void
    {
        parent::setUp();
        $this->guzzleClientMock = \Mockery::mock(app()->make('MsClient'));
        $this->app->instance('MsClient', $this->clientMock);
        $this->clientMock = \Mockery::mock(MsClient::class);
        $this->msNlpService = new MsNlpService($this->getTestStringForNlp(), $this->clientMock);
    }

    public function testItsInstatiatesCorrectServiceClass()
    {
        $this->assertInstanceOf(MsNlpService::class, $this->msNlpService);
    }

    public function testItGetsLanguageFromMs()
    {
        $this->clientMock->shouldReceive('getLanguage')->once()->andReturn(self::LANG_STUB_RESPONSE);
        $language = $this->msNlpService->getLanguage();

        $this->assertEquals($language->getLanguageName(), 'English');
        $this->assertEquals($language->getIsoName(), 'en');
        $this->assertEquals($language->getScore(), 1.0);
    }

    /**
     * @return string
     */
    public function getTestStringForNlp(): string
    {
        return 'Hello World.';
    }
}
