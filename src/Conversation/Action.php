<?php


namespace OpenDialogAi\Core\Conversation;

use Illuminate\Support\Collection;

class Action
{
    private string $odId;
    private Collection $inputAttributes;
    private Collection $outputAttributes;

    /**
     * Action constructor.
     * @param string $odId
     * @param Collection $inputAttributes
     * @param Collection $outputAttributes
     */
    public function __construct(string $odId, Collection $inputAttributes, Collection $outputAttributes)
    {
        $this->odId = $odId;
        $this->inputAttributes = $inputAttributes;
        $this->outputAttributes = $outputAttributes;
    }

    /**
     * @return string
     */
    public function getOdId(): string
    {
        return $this->odId;
    }

    /**
     * @return Collection
     */
    public function getInputAttributes(): Collection
    {
        return $this->inputAttributes;
    }

    /**
     * @return Collection
     */
    public function getOutputAttributes(): Collection
    {
        return $this->outputAttributes;
    }
}
