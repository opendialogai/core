<?php

namespace OpenDialogAi\Core\Tests\Utils;

/**
 * To help with generating conditions yaml
 */
class ConditionsYamlGenerator
{
    private $conditions = [];

    /**
     * @param array  $attributes
     * @param Mixed  $parameters
     * @param String  $operation
     * @return ConditionsYamlGenerator
     */
    public function addCondition($attributes, $parameters = null, $operation = null)
    {
        $this->conditions[] = new Condition($attributes, $parameters, $operation);
        return $this;
    }

    public function getYaml()
    {
        $yaml = "---\nconditions:";

        foreach ($this->conditions as $condition) {
            $yaml .= $condition->getYaml();
        }

        return $yaml;
    }
}

class Condition
{
    public $attributes;
    public $parameters;
    public $operation;

    /**
     * TextMessage constructor.
     * @param $text
     */
    public function __construct($attributes, $parameters, $operation)
    {
        $this->attributes = $attributes;
        $this->parameters = $parameters;
        $this->operation = $operation;
    }

    function getYaml()
    {
        $yaml = <<<EOT

- condition:
    operation: {$this->operation}
    attributes:
EOT;
        foreach ($this->attributes as $id => $attribute) {
            $yaml .= "\n      {$id}: {$attribute}";
        }

        $yaml .= "\n    parameters:";
        foreach ($this->parameters as $id => $parameter) {
            $yaml .= "\n      {$id}: {$parameter}";
        }

        return $yaml;
    }
}
