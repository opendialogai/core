<?php

namespace OpenDialogAi\Core\NlpEngine;

/**
 * Class NlpSentiment
 *
 * @package OpenDialogAi\Core\NlpEngine
 */
class NlpSentiment
{
    /** @var string */
    private $input;

    /** @var float */
    private $score;

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
     * @return float
     */
    public function getScore(): float
    {
        return $this->score;
    }

    /**
     * @param float $score
     */
    public function setScore(float $score): void
    {
        $this->score = $score;
    }
}
