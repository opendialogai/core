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
     *
     * @param  string           $odId
     * @param  Collection|null  $inputAttributes
     * @param  Collection|null  $outputAttributes
     */
    public function __construct(string $odId, ?Collection $inputAttributes = null, ?Collection $outputAttributes = null)
    {
        $this->odId = $odId;
        $this->inputAttributes = $inputAttributes ?? collect();
        $this->outputAttributes = $outputAttributes ?? collect();
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
