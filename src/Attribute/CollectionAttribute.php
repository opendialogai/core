<?php

namespace OpenDialogAi\Core\Attribute;

use Illuminate\Support\Facades\Log;

/**
 * Generic collection attributes expect the data given to be in an array
 */
class CollectionAttribute extends AbstractAttribute
{
    /**
     * @var array
     */
    protected $value = [];

    public function __construct($id, $value)
    {
        parent::__construct($id, AbstractAttribute::COLLECTION, []);
        $this->setValue($value);
    }

    /**
     * @param array $value
     */
    public function setValue($value): void
    {
        if (!is_array($value)) {
            Log::warning('Trying to set a non array value to a collection attribute');
            $this->value[0] = $value;
        } else {
            $this->value = $value;
        }
    }

    public function getValue()
    {
        return $this->value;
    }

    public function toString(): string
    {

    }
}