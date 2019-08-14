<?php

namespace OpenDialogAi\Core\ResponseEngine\tests;

use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\ResponseEngine\Service\ResponseEngineServiceInterface;

class ResponseEngineHTMLCharactersTest extends TestCase
{
    /**
     * @var ResponseEngineServiceInterface
     */
    private $responseEngineService;

    public function setUp(): void
    {
        parent::setUp();
        $this->responseEngineService = resolve(ResponseEngineServiceInterface::class);
    }

    public function testHtmlCharacterReplacement()
    {
        ContextService::saveAttribute('session.test1', "<option>&</option>");

        $message = "<message><text-message>{session.test1}</text-message></message>";

        $message = $this->responseEngineService->fillAttributes($message);

        $this->assertEquals('<message><text-message><option>&amp;</option></text-message></message>', $message);
    }
}
