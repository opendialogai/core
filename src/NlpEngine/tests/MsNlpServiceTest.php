<?php

namespace OpenDialogAi\NlpEngine\Tests;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7;
use OpenDialogAi\Core\NlpEngine\MicrosoftRepository\MsClient;
use OpenDialogAi\Core\NlpEngine\MicrosoftRepository\MsLanguageEntity;
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

    /** @var \Mockery\LegacyMockInterface|\Mockery\MockInterface|\OpenDialogAi\Core\NlpEngine\MicrosoftRepository\MsClient  */
    private $clientMock;

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
        $this->clientMock->shouldReceive('getLanguage')->once()->andReturn($this->getTestResponse());
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
    private function getTestResponse(): MsLanguageEntity
    {
        $stream = Psr7\stream_for(
            '{
            "documents": [
                {
                    "id": "1",
                    "detectedLanguages": [
                        {
                            "name": "English",
                            "iso6391Name": "en",
                            "score": 1.0
                        }
                    ]
                },
                {
                    "id": "2",
                    "detectedLanguages": [
                        {
                            "name": "Polish",
                            "iso6391Name": "pl",
                            "score": 1.0
                        }
                    ]
                },
                {
                    "id": "3",
                    "detectedLanguages": [
                        {
                            "name": "Russian",
                            "iso6391Name": "ru",
                            "score": 1.0
                        }
                    ]
                }
            ],
            "errors": []
        }'
        );
        return new MsLanguageEntity(new Response(200, ['Content-Type' => 'application/json'], $stream));
    }
}
