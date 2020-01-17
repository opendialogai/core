<?php

namespace OpenDialogAi\NlpEngine;

class NlpEntityMatch
{
    /** @var float */
    private $wikipediaScore;

    /** @var float */
    private $entityTypeScore;

    /** @var string */
    private $text;

    /**
     * @return float
     */
    public function getWikipediaScore(): float
    {
        return $this->wikipediaScore;
    }

    /**
     * @param float $wikipediaScore
     */
    public function setWikipediaScore(float $wikipediaScore): void
    {
        $this->wikipediaScore = $wikipediaScore;
    }

    /**
     * @return float
     */
    public function getEntityTypeScore(): float
    {
        return $this->entityTypeScore;
    }

    /**
     * @param float $entityTypeScore
     */
    public function setEntityTypeScore(float $entityTypeScore): void
    {
        $this->entityTypeScore = $entityTypeScore;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @param string $text
     */
    public function setText(string $text): void
    {
        $this->text = $text;
    }
}
