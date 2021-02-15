<?php


namespace OpenDialogAi\AttributeEngine\Tests;

use OpenDialogAi\AttributeEngine\AttributeEngineServiceProvider;
use OpenDialogAi\AttributeEngine\Attributes\BasicCompositeAttribute;
use OpenDialogAi\AttributeEngine\Contracts\CompositeAttribute;
use OpenDialogAi\AttributeEngine\Contracts\ScalarAttribute;
use OpenDialogAi\AttributeEngine\Facades\AttributeResolver;
use OpenDialogAi\AttributeEngine\Util;
use OpenDialogAi\Core\CoreServiceProvider;
use OpenDialogAi\Core\Tests\TestCase;

class CompositeAttributeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setConfigValue(
            'opendialog.attribute_engine.custom_attribute_types',
            [ExampleCompositeAttribute::class]
        );

        $this->setConfigValue(
            'opendialog.attribute_engine.custom_attributes',
            ['test_composite_attribute' => BasicCompositeAttribute::class]
        );
    }

    public function getPackageProviders($app)
    {
        return [
            CoreServiceProvider::class,
            AttributeEngineServiceProvider::class,
        ];
    }

    public function setConfigValue($configName, $config)
    {
        $this->app['config']->set($configName, $config);
    }

    public function testRetrievalOfCompositeAttribute()
    {
        $utterance = AttributeResolver::getAttributeFor('utterance', null);
        $this->assertEquals('utterance', $utterance->getId());

        if ($utterance instanceof CompositeAttribute) {
            $utterance->addAttribute(AttributeResolver::getAttributeFor('name', 'john'));
        }

        $name = $utterance->getAttribute('name');
        $this->assertTrue($name instanceof ScalarAttribute);

        $this->assertEquals('john', $name->toString());
    }

    public function testBracketsMatcher()
    {
        $reference = 'user';
        $result = Util::parse($reference);
        $this->assertEquals(0, count($result));

        $reference = 'user[name]';
        $result = Util::parse($reference);
        $this->assertEquals(1, count($result));
        $this->assertEquals('name', $result[0]['value']);

        $reference = 'user[name[first_name]][some]';
        $result = Util::parse($reference);
        $this->assertEquals(2, count($result));

        $reference = 'user[name[first_name[alias]]';
        $result = Util::parse($reference);
    }
}
