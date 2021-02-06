<?php


namespace OpenDialogAi\AttributeEngine\Tests;

use OpenDialogAi\AttributeEngine\AttributeEngineServiceProvider;
use OpenDialogAi\AttributeEngine\Attributes\ArrayAttribute;
use OpenDialogAi\AttributeEngine\Attributes\BooleanAttribute;
use OpenDialogAi\AttributeEngine\Attributes\FloatAttribute;
use OpenDialogAi\AttributeEngine\Attributes\IntAttribute;
use OpenDialogAi\AttributeEngine\Attributes\StringAttribute;
use OpenDialogAi\AttributeEngine\Attributes\TimestampAttribute;
use OpenDialogAi\AttributeEngine\AttributeTypeService\AttributeTypeServiceInterface;
use OpenDialogAi\AttributeEngine\Exceptions\AttributeTypeAlreadyRegisteredException;
use OpenDialogAi\AttributeEngine\Exceptions\AttributeTypeInvalidException;
use OpenDialogAi\AttributeEngine\Exceptions\AttributeTypeNotRegisteredException;

class AttributeTypeServiceTest extends \Orchestra\Testbench\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function getPackageProviders($app)
    {
        return [
            AttributeEngineServiceProvider::class,
        ];
    }

    public function setConfigValue($configName, $config)
    {
        $this->app['config']->set($configName, $config);
    }

    public function testCoreAttributeTypesAreRegistered()
    {
        $attributeTypeService = resolve(AttributeTypeServiceInterface::class);
        $this->assertGreaterThan(0, count($attributeTypeService->getAvailableAttributeTypes()));

        //$this->assertTrue($attributeTypeService->isAttributeTypeAvailable(ArrayAttribute::$type));
        //$this->assertEquals(ArrayAttribute::class, $attributeTypeService->getAttributeTypeClass(ArrayAttribute::$type));

        $this->assertTrue($attributeTypeService->isAttributeTypeAvailable(BooleanAttribute::getComponentId()));
        $this->assertEquals(BooleanAttribute::class, $attributeTypeService->getAttributeTypeClass(BooleanAttribute::getComponentId()));

        $this->assertTrue($attributeTypeService->isAttributeTypeAvailable(FloatAttribute::getComponentId()));
        $this->assertEquals(FloatAttribute::class, $attributeTypeService->getAttributeTypeClass(FloatAttribute::getComponentId()));

        $this->assertTrue($attributeTypeService->isAttributeTypeAvailable(IntAttribute::getComponentId()));
        $this->assertEquals(IntAttribute::class, $attributeTypeService->getAttributeTypeClass(IntAttribute::getComponentId()));

        $this->assertTrue($attributeTypeService->isAttributeTypeAvailable(StringAttribute::getComponentId()));
        $this->assertEquals(StringAttribute::class, $attributeTypeService->getAttributeTypeClass(StringAttribute::getComponentId()));

        $this->assertTrue($attributeTypeService->isAttributeTypeAvailable(TimestampAttribute::getComponentId()));
        $this->assertEquals(TimestampAttribute::class, $attributeTypeService->getAttributeTypeClass(TimestampAttribute::getComponentId()));
    }

    public function testGetUnregisteredAttributeType()
    {
        $attributeTypeService = resolve(AttributeTypeServiceInterface::class);

        // attribute.app.custom hasn't been registered so when we try to get the attribute type class
        // we should get this exception
        $this->expectException(AttributeTypeNotRegisteredException::class);
        $this->assertFalse($attributeTypeService->isAttributeTypeAvailable(ExampleCustomAttributeType::getComponentId()));

        $attributeTypeService->getAttributeTypeClass(ExampleCustomAttributeType::getComponentId());
    }

    public function testRegisterCustomAttributeType()
    {
        $attributeTypeService = resolve(AttributeTypeServiceInterface::class);

        $attributeTypeService->registerAttributeType(ExampleCustomAttributeType::class);

        $this->assertTrue($attributeTypeService->isAttributeTypeAvailable(ExampleCustomAttributeType::getComponentId()));
    }

    public function testRegisterCustomAttributeTypeWithUsedId()
    {
        $attributeTypeService = resolve(AttributeTypeServiceInterface::class);

        // attribute.core.string is already used by StringAttribute, so when we try to register
        // ExampleCustomAttributeWithUsedName (which has the same ID) we should get this exception
        $this->expectException(AttributeTypeAlreadyRegisteredException::class);
        $attributeTypeService->registerAttributeType(ExampleCustomAttributeTypeWithUsedName::class);
    }

    public function testRegisterCustomAttributeTypeWithoutImplementingInterface()
    {
        $attributeTypeService = resolve(AttributeTypeServiceInterface::class);

        // ExampleCustomAttributeTypeWithoutImplements doesn't implement AttributeInterface so we should get this exception
        $this->expectException(AttributeTypeInvalidException::class);
        $attributeTypeService->registerAttributeType(ExampleCustomAttributeTypeWithoutImplements::class);
    }
}
