<?php

namespace OpenDialogAi\InterpreterEngine\Interpreters;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\AttributeEngine\Facades\AttributeResolver;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Utterances\UtteranceInterface;
use OpenDialogAi\InterpreterEngine\BaseInterpreter;
use OpenDialogAi\InterpreterEngine\QnA\QnAClient;
use OpenDialogAi\InterpreterEngine\QnA\QnARequestFailedException;
use OpenDialogAi\InterpreterEngine\QnA\QnAResponse;

class QnAInterpreter extends BaseInterpreter
{
    protected static ?string $componentId = 'interpreter.core.qna';

    /** @var QnAClient */
    private $client;

    /**
     * QnAInterpreter constructor.
     * @throws BindingResolutionException
     */
    public function __construct()
    {
        $this->client = app()->make(QnAClient::class);
    }

    /**
     * @inheritdoc
     */
    public function interpret(UtteranceInterface $utterance): array
    {
        try {
            $qnaResponse = $this->client->queryQnA($utterance->getText());
            $intent = $this->createOdIntent($qnaResponse);
        } catch (QnARequestFailedException $e) {
            Log::warning(sprintf('QnA interpreter failed at a QnA client level with message %s', $e->getMessage()));
            $intent = new NoMatchIntent();
        }

        return [$intent];
    }

    /**
     * Creates an @param QnAResponse $response
     * @return NoMatchIntent|Intent
     * @see Intent from the QnA response. If there is no intent in the response, a default NO_MATCH intent
     * is returned
     *
     */
    protected function createOdIntent(QnAResponse $response)
    {
        $intent = new NoMatchIntent();

        if (!empty($response->getAnswers())) {
            foreach ($response->getAnswers() as $answer) {
                if ($answer->getId() >= 0) {
                    $attribute = AttributeResolver::getAttributeFor('qna_answer', $answer->getAnswer());

                    $intent = new QnAQuestionMatchedIntent();
                    $intent->setConfidence($answer->getScore() / 100);
                    $intent->addAttribute($attribute);

                    $i = 0;
                    foreach ($answer->getPrompts() as $prompt) {
                        $promptAttribute = AttributeResolver::getAttributeFor(
                            'qna_prompt_' . $i,
                            $prompt->getDisplayText()
                        );
                        $intent->addAttribute($promptAttribute);

                        $i++;
                    }

                    return $intent;
                }
            }
        }

        return $intent;
    }
}
