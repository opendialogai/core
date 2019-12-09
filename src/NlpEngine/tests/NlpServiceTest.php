<?php

namespace OpenDialogAi\NlpEngine\Tests;

use OpenDialogAi\Core\NlpEngine\Service\MsNlpService;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\NlpEngine\Service\NlpServiceInterface;

class NlpServiceTest extends TestCase
{
    /** @var \OpenDialogAi\Core\NlpEngine\Service\MsNlpService */
    private $nlpService;

    public function setUp(): void
    {
        parent::setUp();
        $this->nlpService = app()->make(NlpServiceInterface::class, ['string' => $this->getTestStringForNlp()]);
    }

    public function testItsInstatiatesCorrectServiceClass()
    {
        $this->assertInstanceOf(MsNlpService::class, $this->nlpService);
    }

    /**
     * @return string
     */
    public function getTestStringForNlp(): string
    {
        return 'Hello World.';
    }
}
