<?php

namespace OpenDialogAi\Core\Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use OpenDialogAi\Core\Controllers\OpenDialogController;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\Core\Tests\Utils\UtteranceGenerator;
use OpenDialogAi\Core\UserAttributes;

class UserAttributesCommandTest extends TestCase
{
    /**
     * @requires DGRAPH
     * @group skip
     */
    public function testCachingAttributes()
    {
        $this->activateConversation($this->conversation4());

        $utterance = UtteranceGenerator::generateTextUtterance('Test message');

        $controller = resolve(OpenDialogController::class);

        $controller->runConversation($utterance);

        Artisan::call('attributes:dump');

        $attributes = UserAttributes::all();

        $this->assertCount(1, $attributes->where('attribute', 'ei_type')->where('value', 'chatbot_user'));
    }
}
