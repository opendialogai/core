<?php

namespace OpenDialogAi\Core\Tests\Utils;

/**
 * To help with generating conditions yaml
 */
class ConditionsYamlGenerator
{
    private $conditions = [];

    /**
     * @param String  $attribute
     * @param Mixed  $value
     * @param String  $operation
     * @return ConditionsYamlGenerator
     */
    public function addCondition($attribute, $value = null, $operation = null)
    {
        $this->conditions[] = new Condition($attribute, $value, $operation);
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
    public $attribute;
    public $value;
    public $operation;

    /**
     * TextMessage constructor.
     * @param $text
     */
    public function __construct($attribute, $value, $operation)
    {
        $this->attribute = $attribute;
        $this->value = $value;
        $this->operation = $operation;
    }

    function getYaml()
    {
        $yaml = <<<EOT

- condition:
    attribute: {$this->attribute}
    operation: {$this->operation}
EOT;
        if ($this->value !== null) {
            $yaml .= "\n    value: {$this->value}";
        }

        return $yaml;
    }
}
