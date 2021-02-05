<?php

use OpenDialogAi\AttributeEngine\Attributes\ArrayAttribute;
use OpenDialogAi\AttributeEngine\Attributes\BooleanAttribute;
use OpenDialogAi\AttributeEngine\Attributes\FloatAttribute;
use OpenDialogAi\AttributeEngine\Attributes\IntAttribute;
use OpenDialogAi\AttributeEngine\Attributes\StringAttribute;
use OpenDialogAi\AttributeEngine\Attributes\TimestampAttribute;
use OpenDialogAi\AttributeEngine\Attributes\FormDataAttribute;
use OpenDialogAi\AttributeEngine\CoreAttributes\UserAttribute;
use OpenDialogAi\AttributeEngine\CoreAttributes\UtteranceAttribute;
use OpenDialogAi\Core\Conversation\Model;

return [
    'supported_attribute_types' => [
        //ArrayAttribute::class,
        BooleanAttribute::class,
        FloatAttribute::class,
        IntAttribute::class,
        StringAttribute::class,
        TimestampAttribute::class,
        UserAttribute::class,
        UtteranceAttribute::class,
        FormDataAttribute::class,
    ],

    'supported_attributes' => [
        'attribute_name'   => StringAttribute::class,
        'attribute_value' => StringAttribute::class,
        'callback_value' => StringAttribute::class,
        'context' => StringAttribute::class,
        'ei_type' => StringAttribute::class,
        'email' => StringAttribute::class,
        'external_id' => StringAttribute::class,
        'first_name' => StringAttribute::class,
        'full_name' => StringAttribute::class,
        'id' => StringAttribute::class,
        'last_name' => StringAttribute::class,
        'age' => IntAttribute::class,
        'name' => StringAttribute::class,
        'operation' => StringAttribute::class,
        'timestamp' => IntAttribute::class,
        'last_seen' => TimestampAttribute::class,
        'first_seen' => TimestampAttribute::class,
        'all' => StringAttribute::class,
        //'attributes' => ArrayAttribute::class,
        //'parameters' => ArrayAttribute::class,

        'qna_answer' => StringAttribute::class,
        'qna_prompt_0' => StringAttribute::class,
        'qna_prompt_1' => StringAttribute::class,
        'qna_prompt_2' => StringAttribute::class,
        'qna_prompt_3' => StringAttribute::class,
        'qna_prompt_4' => StringAttribute::class,

        'current_conversation' => StringAttribute::class,
        'current_scene' => StringAttribute::class,
        'current_intent' => StringAttribute::class,
        'interpreted_intent' => StringAttribute::class,
        //'next_intents' => ArrayAttribute::class,

        Model::CONVERSATION_STATUS => StringAttribute::class,
        Model::CONVERSATION_VERSION => IntAttribute::class,

        Model::USER_ATTRIBUTE_TYPE => StringAttribute::class,
        Model::USER_ATTRIBUTE_VALUE => StringAttribute::class,

        // Intents
        Model::ORDER => IntAttribute::class,
        Model::CONFIDENCE => FloatAttribute::class,
        Model::COMPLETES => BooleanAttribute::class,
        Model::REPEATING  => BooleanAttribute::class,

        // Utterances
        'utterance' => UtteranceAttribute::class,
        'utterance_platform' => StringAttribute::class,
        'utterance_type' => StringAttribute::class,
        'utterance_data' => FormDataAttribute::class,
        'utterance_form_data' => FormDataAttribute::class,
        'utterance_text' => StringAttribute::class,
        'callback_id'=> StringAttribute::class,
        'utterance_user_id' => StringAttribute::class,
        'utterance_user' => UserAttribute::class
    ],
];
