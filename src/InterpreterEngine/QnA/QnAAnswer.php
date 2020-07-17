<?php

namespace OpenDialogAi\InterpreterEngine\QnA;

class QnAAnswer
{
    private $questions;

    private $answer;

    private $score;

    private $id;

    private $source;

    private $metadata;

    private $prompts;

    /**
     * @param $questions
     * @param $answer
     * @param $score
     * @param $id
     * @param $source
     * @param $metadata
     * @param $prompts
     */
    public function __construct($questions, $answer, $score, $id, $source, $metadata, $prompts)
    {
        $this->questions = $questions;
        $this->answer = $answer;
        $this->score = $score;
        $this->id = $id;
        $this->source = $source;
        $this->metadata = $metadata;
        $this->prompts = $prompts;
    }

    public function getQuestions()
    {
        return $this->questions;
    }

    public function getAnswer()
    {
        return $this->answer;
    }

    public function getScore()
    {
        return $this->score;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getSource()
    {
        return $this->source;
    }

    public function getMetadata()
    {
        return $this->metadata;
    }

    public function getPrompts()
    {
        return $this->prompts;
    }
}
