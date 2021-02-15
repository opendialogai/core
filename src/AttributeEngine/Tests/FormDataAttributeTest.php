<?php

namespace OpenDialogAi\AttributeEngine\Tests;

use OpenDialogAi\AttributeEngine\Attributes\FormDataAttribute;
use OpenDialogAi\AttributeEngine\AttributeValues\SerializedArrayAttributeValue;

class FormDataAttributeTest extends \Orchestra\Testbench\TestCase
{
    public function testRawValueSetting()
    {
        $data = [
            'name' => 'value',
            'text' => 'name: value'
        ];

        $attribute = new FormDataAttribute('testFloat', new SerializedArrayAttributeValue($data));
        $form = $attribute->getAttributeValue()->getTypedValue();

        $this->assertEquals($data['name'], $form['name']);
        $this->assertEquals($data['text'], $form['text']);

        $fromRawAttribute = new FormDataAttribute('testFloat', $data);
        $form = $fromRawAttribute->getAttributeValue()->getTypedValue();

        $this->assertEquals($data['name'], $form['name']);
        $this->assertEquals($data['text'], $form['text']);
    }
}
