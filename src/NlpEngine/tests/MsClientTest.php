<?php

namespace OpenDialogAi\NlpEngine\Tests;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7;
use OpenDialogAi\Core\NlpEngine\Client\MsClient;
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

        $response = $this->msClient->getLanguage($this->getTestStringForNlp(), 'GB');

        $this->assertEquals($response, [
            [
                'name' => 'English',
                'iso6391Name' => 'en',
                'score' => 1.0,
            ]
        ]);
    }

    private function getTestResponse()
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
}
