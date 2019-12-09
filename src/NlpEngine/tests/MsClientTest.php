<?php

namespace OpenDialogAi\NlpEngine\Tests;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7;
use OpenDialogAi\Core\NlpEngine\MicrosoftRepository\MsClient;
use OpenDialogAi\Core\Tests\TestCase;

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
        $this->guzzleClientMock->shouldReceive('post')->once()->andReturn($this->getLanguageTestResponse());

        $nlpLanguage = $this->msClient->getLanguage($this->getTestStringForNlp(), 'GB');

        $this->assertEquals($nlpLanguage->getLanguageName(), 'English');
        $this->assertEquals($nlpLanguage->getIsoName(), 'en');
        $this->assertEquals($nlpLanguage->getScore(), 1.0);
        $this->assertEquals($nlpLanguage->getInput(), $this->getTestStringForNlp());
    }

    public function testItGetsSentimentFromMs()
    {
        $this->guzzleClientMock->shouldReceive('post')->once()->andReturn($this->getSentimentTestResponse());

        $nlpSentiment = $this->msClient->getSentiment($this->getTestStringForNlp(), 'en');

        $this->assertEquals($nlpSentiment->getInput(), 'Hello World.');
        $this->assertEquals($nlpSentiment->getScore(), 0.7443314790725708);
    }

    public function testItGetsEntitiesFromMs()
    {
        $input = 'I want to find books by David Attenborough or George Orwell about south america';
        $this->guzzleClientMock->shouldReceive('post')->once()->andReturn($this->getEntitiesTestResponse());

        $nlpEntities = $this->msClient->getEntities($input, 'en');

        $this->assertEquals($nlpEntities->getInput(), $input);
        $this->assertEquals($nlpEntities->getEntities()[0]->getType(), 'Person');
        $this->assertEquals($nlpEntities->getEntities()[0]->getMatches()[0]->getText(), 'David Attenborough');
        $this->assertEquals($nlpEntities->getEntities()[1]->getType(), 'Person');
        $this->assertEquals($nlpEntities->getEntities()[1]->getMatches()[0]->getText(), 'George Orwell');
        $this->assertEquals($nlpEntities->getEntities()[2]->getType(), 'Location');
        $this->assertEquals($nlpEntities->getEntities()[2]->getMatches()[0]->getText(), 'south america');
    }

    private function getLanguageTestResponse(): Response
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

    private function getEntitiesTestResponse(): Response
    {
        $stream = Psr7\stream_for(
            '{
                "documents": [
                    {
                        "id": "3",
                        "entities": [
                            {
                                "name": "David Attenborough",
                                "matches": [
                                    {
                                        "wikipediaScore": 0.68555693838080911,
                                        "entityTypeScore": 0.99963384866714478,
                                        "text": "David Attenborough",
                                        "offset": 24,
                                        "length": 18
                                    }
                                ],
                                "wikipediaLanguage": "en",
                                "wikipediaId": "David Attenborough",
                                "wikipediaUrl": "https://en.wikipedia.org/wiki/David_Attenborough",
                                "bingId": "2b443cec-2dd3-13e6-08dd-f61878c7dbcc",
                                "type": "Person"
                            },
                            {
                                "name": "George Orwell",
                                "matches": [
                                    {
                                        "wikipediaScore": 0.73567867663170428,
                                        "entityTypeScore": 0.999786376953125,
                                        "text": "George Orwell",
                                        "offset": 46,
                                        "length": 13
                                    }
                                ],
                                "wikipediaLanguage": "en",
                                "wikipediaId": "George Orwell",
                                "wikipediaUrl": "https://en.wikipedia.org/wiki/George_Orwell",
                                "bingId": "e4ab7b93-d59e-70d7-e3ca-0cefb38bdf27",
                                "type": "Person"
                            },
                            {
                                "name": "south america",
                                "matches": [
                                    {
                                        "entityTypeScore": 0.999702513217926,
                                        "text": "south america",
                                        "offset": 66,
                                        "length": 13
                                    }
                                ],
                                "type": "Location"
                            }
                        ]
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
