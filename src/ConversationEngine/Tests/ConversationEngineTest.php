<?php

namespace OpenDialogAi\ConversationEngine\Tests;

use OpenDialogAi\AttributeEngine\AttributeResolver\AttributeResolver;
use OpenDialogAi\AttributeEngine\Attributes\IntAttribute;
use OpenDialogAi\AttributeEngine\Attributes\StringAttribute;
use OpenDialogAi\AttributeEngine\CoreAttributes\UtteranceAttribute;
use OpenDialogAi\ConversationEngine\ConversationEngine;
use OpenDialogAi\ConversationEngine\ConversationEngineInterface;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\Core\Tests\Utils\UtteranceGenerator;

class ConversationEngineTest extends TestCase
{
    /* @var ConversationEngine */
    private $conversationEngine;

    /* @var UtteranceAttribute */
    private $utterance;

    public function setUp(): void
    {
        parent::setUp();
        /* @var AttributeResolver $attributeResolver */
        $attributeResolver = resolve(AttributeResolver::class);
        $attributes = [
            'test' => IntAttribute::class,
            'user_name' => StringAttribute::class,
            'user_email' => StringAttribute::class
        ];
        $attributeResolver->registerAttributes($attributes);

        $this->conversationEngine = resolve(ConversationEngineInterface::class);

        $this->utterance = UtteranceGenerator::generateChatOpenUtterance('hello_bot');
    }

}
