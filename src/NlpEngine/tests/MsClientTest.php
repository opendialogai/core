<?php

namespace OpenDialogAi\NlpEngine\Tests;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7;
use OpenDialogAi\Core\NlpEngine\MicrosoftRepository\MsClient;
use OpenDialogAi\Core\Tests\TestCase;

/**
 * Class MsClientTest
 *
 * @package OpenDialogAi\NlpEngine\Tests
 */
class MsClientTest extends TestCase
{
    private $msClient;

    /** @var \Mockery\LegacyMockInterface|\Mockery\MockInterface  */
    private $guzzleClientMock;

    public function setUp(): void
    {
        parent::setUp();
        $this->guzzleClientMock = \Mockery::mock(app()->make('MsClient'));
        $this->app->instance('MsClient', $this->guzzleClientMock);
        $this->msClient = new MsClient();
    }

    public function testItGetsLanguageFromMs()
    {
        $this->guzzleClientMock->shouldReceive('post')->once()->andReturn($this->getTestResponse());

        $msLanguage = $this->msClient->getLanguage($this->getTestStringForNlp(), 'GB');

        $this->assertEquals($msLanguage->getName(), 'English');
        $this->assertEquals($msLanguage->getIsoName(), 'en');
        $this->assertEquals($msLanguage->getScore(), 1.0);
    }

    public function testItGetsSentimentFromMs()
    {
        $this->guzzleClientMock->shouldReceive('post')->once()->andReturn($this->getSentimentTestResponse());

        $nlpSentiment = $this->msClient->getSentiment($this->getTestStringForNlp(), 'en');

        $this->assertEquals($nlpSentiment->getInput(), 'Hello World.');
        $this->assertEquals($nlpSentiment->getScore(), 0.7443314790725708);
    }

    private function getTestResponse(): Response
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
        return new Response(200, ['Content-Type' => 'application/json'], $stream);
    }

    private function getSentimentTestResponse(): Response
    {
        $stream = Psr7\stream_for(
            '{
            "documents": [
                {
                    "id": "1",
                    "score": 0.7443314790725708
                },
                {
                    "id": "2",
                    "score": 0.188551127910614
                },
                {
                    "id": "3",
                    "score": 0.18014723062515259
                },
                {
                    "id": "4",
                    "score": 0.90277755260467529
                },
                {
                    "id": "5",
                    "score": 0.98837846517562866
                }
            ],
            "errors": []
        }'
        );
        return new Response(200, ['Content-Type' => 'application/json'], $stream);
    }

    /**
     * @return string
     */
    public function getTestStringForNlp(): string
    {
        return 'Hello World.';
    }
}
