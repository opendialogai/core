<?php

namespace OpenDialogAi\NlpEngine\Tests;

use Exception;
use Mockery;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\NlpEngine\Exceptions\NlpProviderMethodNotSupportedException;
use OpenDialogAi\NlpEngine\MicrosoftRepository\MsClient;
use OpenDialogAi\NlpEngine\NlpEntities;
use OpenDialogAi\NlpEngine\NlpLanguage;
use OpenDialogAi\NlpEngine\NlpSentiment;
use OpenDialogAi\NlpEngine\Providers\MsNlpProvider;
use OpenDialogAi\NlpEngine\Service\NlpServiceInterface;

class MsNlpProviderTest extends TestCase
{
    /** @var \OpenDialogAi\NlpEngine\Providers\MsNlpProvider */
    private $msNlpProvider;

    /** @var \Mockery\LegacyMockInterface|\Mockery\MockInterface|\OpenDialogAi\NlpEngine\MicrosoftRepository\MsClient  */
    private $clientMock;

    public function setUp(): void
    {
        parent::setUp();

        $msClient = Mockery::mock(MsClient::class);

        // Overwrite service provider binding to use the mocked MsClient instead
        app()->singleton(MsClient::class, function () use ($msClient) {
            return $msClient;
        });

        $this->clientMock = $msClient;

        try {
            $this->msNlpProvider = resolve(NlpServiceInterface::class)->getProvider(MsNlpProvider::getName());
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testItsInstatiatesCorrectProviderClass()
    {
        $this->assertInstanceOf(MsNlpProvider::class, $this->msNlpProvider);
    }

    public function testItGetsLanguageFromMs()
    {
        $this->clientMock->shouldReceive('getLanguage')->once()->andReturn($this->getTestResponse());

        try {
            $language = $this->msNlpProvider->getLanguage($this->getTestStringForNlp());
        } catch (NlpProviderMethodNotSupportedException $e) {
            $this->fail($e->getMessage());
        }

        $this->assertEquals($language->getInput(), $this->getTestStringForNlp());
        $this->assertEquals($language->getLanguageName(), 'English');
        $this->assertEquals($language->getIsoName(), 'en');
        $this->assertEquals($language->getScore(), 1.0);
    }

    public function testItGetsSentimentFromMs()
    {
        $this->clientMock->shouldReceive('getSentiment')->once()->andReturn($this->getSentimentTestResponse());

        try {
            $nlpSentiment = $this->msNlpProvider->getSentiment($this->getTestStringForNlp());
        } catch (NlpProviderMethodNotSupportedException $e) {
            $this->fail($e->getMessage());
        }

        $this->assertEquals($nlpSentiment->getInput(), $this->getTestStringForNlp());
        $this->assertEquals($nlpSentiment->getScore(), 0.98837846517562866);
    }

    public function testItGetsEntitiesFromMs()
    {
        $this->clientMock->shouldReceive('getEntities')->once()->andReturn($this->getEntitiesTestResponse());

        try {
            $nlpEntities = $this->msNlpProvider->getEntities($this->getTestStringForNlp());
        } catch (NlpProviderMethodNotSupportedException $e) {
            $this->fail($e->getMessage());
        }

        $this->assertInstanceOf(NlpEntities::class, $nlpEntities);
    }

    /**
     * @return string
     */
    public function getTestStringForNlp(): string
    {
        return 'Hello World.';
    }

    /**
     * @return \OpenDialogAi\NlpEngine\NlpLanguage
     */
    private function getTestResponse(): NlpLanguage
    {
        $nplLanguage = new NlpLanguage();
        $nplLanguage->setInput($this->getTestStringForNlp());
        $nplLanguage->setLanguageName('English');
        $nplLanguage->setIsoName('en');
        $nplLanguage->setScore(1.0);
        return $nplLanguage;
    }

    /**
     * @return \OpenDialogAi\NlpEngine\NlpSentiment
     */
    private function getSentimentTestResponse(): NlpSentiment
    {
        $nlpSentiment = new NlpSentiment();
        $nlpSentiment->setScore(0.98837846517562866);
        $nlpSentiment->setInput($this->getTestStringForNlp());

        return $nlpSentiment;
    }

    /**
     * @return \OpenDialogAi\NlpEngine\NlpEntities
     */
    private function getEntitiesTestResponse(): NlpEntities
    {
        return new NlpEntities();
    }
}
