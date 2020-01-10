<?php


namespace OpenDialogAi\Core\NlpEngine;


class NlpSummary
{
    /** @var string */
    private $input;

    /** @var string[] */
    private $sentences;

    /**
     * @return string
     */
    public function getInput(): string
    {
        return $this->input;
    }

    /**
     * @param string $input
     */
    public function setInput(string $input): void
    {
        $this->input = $input;
    }

    /**
     * @return array
     */
    public function getSentences(): array
    {
        return $this->sentences;
    }

    /**
     * @param string $sentence
     */
    public function addSentence(string $sentence): void
    {
        $this->sentences[] = $sentence;
    }
}
