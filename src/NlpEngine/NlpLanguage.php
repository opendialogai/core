<?php

namespace OpenDialogAi\NlpEngine;

class NlpLanguage
{
    /** @var string */
    private $input;

    /** @var string */
    private $languageName;

    /** @var string */
    private $isoName;

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
     * @return string
     */
    public function getLanguageName(): string
    {
        return $this->languageName;
    }

    /**
     * @param string $languageName
     */
    public function setLanguageName($languageName): void
    {
        $this->languageName = $languageName;
    }

    /**
     * @return string
     */
    public function getIsoName(): string
    {
        return $this->isoName;
    }

    /**
     * @param string $isoName
     */
    public function setIsoName($isoName): void
    {
        $this->isoName = $isoName;
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
    public function setScore($score): void
    {
        $this->score = $score;
    }
}
