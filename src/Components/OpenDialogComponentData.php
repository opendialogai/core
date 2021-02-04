<?php


namespace OpenDialogAi\Core\Components;


class OpenDialogComponentData
{
    public ?string $name;
    public ?string $description;
    public string $type;
    public string $source;
    public string $id;

    /**
     * OpenDialogComponentData constructor.
     * @param string $type
     * @param string $source
     * @param string $id
     * @param string|null $name
     * @param string|null $description
     */
    public function __construct(string $type, string $source, string $id, ?string $name = null, ?string $description = null)
    {
        $this->type = $type;
        $this->source = $source;
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
    }
}
