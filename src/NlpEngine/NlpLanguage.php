<?php

namespace OpenDialogAi\Core\NlpEngine;

/**
 * Class NlpLanguage
 *
 * @package OpenDialogAi\Core\NlpEngine
 */
class NlpLanguage
{
    private $languageName;
    private $isoName;
    private $score;

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
