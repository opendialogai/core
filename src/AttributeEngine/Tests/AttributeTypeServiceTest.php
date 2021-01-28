<?php


namespace OpenDialogAi\AttributeEngine\Tests;


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
use OpenDialogAi\Core\Tests\TestCase;

class AttributeTypeServiceTest extends TestCase
{
    public function testCoreAttributeTypesAreRegistered()
    {
        $attributeTypeService = resolve(AttributeTypeServiceInterface::class);
        $this->assertGreaterThan(0, count($attributeTypeService->getAvailableAttributeTypes()));

        $this->assertTrue($attributeTypeService->isAttributeTypeAvailable(ArrayAttribute::$type));
        $this->assertEquals(ArrayAttribute::class, $attributeTypeService->getAttributeTypeClass(ArrayAttribute::$type));

        $this->assertTrue($attributeTypeService->isAttributeTypeAvailable(BooleanAttribute::$type));
        $this->assertEquals(BooleanAttribute::class, $attributeTypeService->getAttributeTypeClass(BooleanAttribute::$type));

        $this->assertTrue($attributeTypeService->isAttributeTypeAvailable(FloatAttribute::$type));
        $this->assertEquals(FloatAttribute::class, $attributeTypeService->getAttributeTypeClass(FloatAttribute::$type));

        $this->assertTrue($attributeTypeService->isAttributeTypeAvailable(IntAttribute::$type));
        $this->assertEquals(IntAttribute::class, $attributeTypeService->getAttributeTypeClass(IntAttribute::$type));

        $this->assertTrue($attributeTypeService->isAttributeTypeAvailable(StringAttribute::$type));
        $this->assertEquals(StringAttribute::class, $attributeTypeService->getAttributeTypeClass(StringAttribute::$type));

        $this->assertTrue($attributeTypeService->isAttributeTypeAvailable(TimestampAttribute::$type));
        $this->assertEquals(TimestampAttribute::class, $attributeTypeService->getAttributeTypeClass(TimestampAttribute::$type));
    }

    public function testGetUnregisteredAttributeType()
    {
        $attributeTypeService = resolve(AttributeTypeServiceInterface::class);

        // attribute.app.custom hasn't been registered so when we try to get the attribute type class
        // we should get this exception
        $this->expectException(AttributeTypeNotRegisteredException::class);
        $this->assertFalse($attributeTypeService->isAttributeTypeAvailable(ExampleCustomAttributeType::$type));

        $attributeTypeService->getAttributeTypeClass(ExampleCustomAttributeType::$type);
    }

    public function testRegisterCustomAttributeType()
    {
        $attributeTypeService = resolve(AttributeTypeServiceInterface::class);

        $attributeTypeService->registerAttributeType(ExampleCustomAttributeType::class);

        $this->assertTrue($attributeTypeService->isAttributeTypeAvailable(ExampleCustomAttributeType::$type));
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
