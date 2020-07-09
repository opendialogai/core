<?php

namespace OpenDialogAi\InterpreterEngine\QnA;

class QnAResponse
{
    private $answers;

    public function __construct($response)
    {
        $this->answers = [];

        if (isset($response->answers)) {
            foreach ($response->answers as $answer) {
                $prompts = [];
                foreach ($answer->context->prompts as $prompt) {
                    $prompts[] = new QnAPrompt(
                        $prompt->displayOrder,
                        $prompt->qnaId,
                        $prompt->displayText
                    );
                }

                $this->answers[] = new QnAAnswer(
                    $answer->questions,
                    $answer->answer,
                    $answer->score,
                    $answer->id,
                    $answer->source,
                    $answer->metadata,
                    $prompts
                );
            }
        }
    }

    public function getAnswers(): array
    {
        return $this->answers;
    }
}
