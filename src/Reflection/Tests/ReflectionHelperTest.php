<?php

namespace OpenDialogAi\Core\Reflection\Tests;

use OpenDialogAi\ActionEngine\Actions\ExampleAction;
use OpenDialogAi\ActionEngine\Service\ActionEngineInterface;
use OpenDialogAi\AttributeEngine\Attributes\StringAttribute;
use OpenDialogAi\AttributeEngine\AttributeTypeService\AttributeTypeServiceInterface;
use OpenDialogAi\AttributeEngine\Facades\AttributeResolver;
use OpenDialogAi\AttributeEngine\Tests\ExampleCustomAttributeType;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\Core\Reflection\Helper\ReflectionHelperInterface;
use OpenDialogAi\Core\ResponseEngine\Tests\Formatters\DummyFormatter;
use OpenDialogAi\Core\SensorEngine\Tests\Sensors\DummySensor;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\InterpreterEngine\Service\InterpreterServiceInterface;
use OpenDialogAi\InterpreterEngine\Tests\Interpreters\DummyInterpreter;
use OpenDialogAi\OperationEngine\Service\OperationServiceInterface;
use OpenDialogAi\OperationEngine\Tests\Operations\DummyOperation;
use OpenDialogAi\ResponseEngine\Service\ResponseEngineServiceInterface;
use OpenDialogAi\SensorEngine\Service\SensorServiceInterface;


class ReflectionHelperTest extends TestCase
{
    public function testGetAvailableActions()
    {
        $reflection = resolve(ReflectionHelperInterface::class)->getActionEngineReflection();
        resolve(ActionEngineInterface::class)->unSetAvailableActions();

        $actions = $reflection->getAvailableActions();
        $this->assertCount(0, $actions);

        $exampleAction = new ExampleAction();
        resolve(ActionEngineInterface::class)->registerAction($exampleAction);

        $actions = $reflection->getAvailableActions();
        $this->assertCount(1, $actions);
        $this->assertTrue($actions->hasKey($exampleAction::getName()));
        $this->assertEquals($exampleAction, $actions->get($exampleAction::getName()));
    }

    public function testGetAvailableAttributeTypes()
    {
        $reflection = resolve(ReflectionHelperInterface::class)->getAttributeEngineReflection();
        $attributeTypeService = resolve(AttributeTypeServiceInterface::class);

        $attributeTypes = $reflection->getAvailableAttributeTypes();

        $numberOfCoreAttributeTypes = count($attributeTypeService->getAvailableAttributeTypes());
        $this->assertCount($numberOfCoreAttributeTypes, $attributeTypes);

        $attributeTypeService->registerAttributeType(ExampleCustomAttributeType::class);

        $attributeTypes = $reflection->getAvailableAttributeTypes();
        $this->assertCount($numberOfCoreAttributeTypes + 1, $attributeTypes);

        $typeId = ExampleCustomAttributeType::getType();
        $this->assertTrue($attributeTypes->hasKey($typeId));
        $this->assertEquals($typeId, $attributeTypes->get($typeId));
    }

    public function testGetAvailableAttributes()
    {
        $reflection = resolve(ReflectionHelperInterface::class)->getAttributeEngineReflection();
        $attributes = $reflection->getAvailableAttributes();

        $numberOfCoreAttributes = count(AttributeResolver::getSupportedAttributes());
        $this->assertCount($numberOfCoreAttributes, $attributes);

        $attributeId = 'test_attribute';
        AttributeResolver::registerAttributes([
            $attributeId => StringAttribute::class
        ]);

        $attributes = $reflection->getAvailableAttributes();
        $this->assertCount($numberOfCoreAttributes + 1, $attributes);

        $this->assertTrue($attributes->hasKey($attributeId));
        $this->assertEquals(StringAttribute::class, $attributes->get($attributeId));
    }

    public function testGetAvailableContexts()
    {
        $reflection = resolve(ReflectionHelperInterface::class)->getContextEngineReflection();
        $contexts = $reflection->getAvailableContexts();

        $numberOfCoreContexts = count(ContextService::getContexts());
        $this->assertCount($numberOfCoreContexts, $contexts);

        $contextId = 'my_custom_context';
        $context = ContextService::createContext($contextId);

        $contexts = $reflection->getAvailableContexts();
        $this->assertCount($numberOfCoreContexts + 1, $contexts);

        $this->assertTrue($contexts->hasKey($contextId));
        $this->assertEquals($context, $contexts->get($contextId));
    }

    public function testGetAvailableInterpreters()
    {
        $reflection = resolve(ReflectionHelperInterface::class)->getInterpreterEngineReflection();
        $interpreters = $reflection->getAvailableInterpreters();

        $interpreterService = resolve(InterpreterServiceInterface::class);
        $numberOfCoreInterpreters = count($interpreterService->getAvailableInterpreters());
        $this->assertCount($numberOfCoreInterpreters, $interpreters);

        $interpreterId = DummyInterpreter::getName();
        $interpreterService->registerAvailableInterpreters([
            DummyInterpreter::class
        ]);

        $interpreters = $reflection->getAvailableInterpreters();
        $this->assertCount($numberOfCoreInterpreters + 1, $interpreters);

        $this->assertTrue($interpreters->hasKey($interpreterId));
        $this->assertEquals(DummyInterpreter::class, $interpreters->get($interpreterId));
    }

    public function testGetInterpreterEngineConfiguration()
    {
        $reflection = resolve(ReflectionHelperInterface::class)->getInterpreterEngineReflection();
        $configuration = $reflection->getEngineConfiguration();

        $defaultInterpreter = config('opendialog.interpreter_engine.default_interpreter');
        $defaultCacheTime = config('opendialog.interpreter_engine.default_cache_time');
        $supportedCallbacks = config('opendialog.interpreter_engine.supported_callbacks');

        $this->assertEquals($defaultInterpreter, $configuration->getDefaultInterpreter());
        $this->assertEquals($defaultCacheTime, $configuration->getDefaultCacheTime());
        $this->assertEquals($supportedCallbacks, $configuration->getSupportedCallbacks()->toArray());
        $this->assertJsonStringEqualsJsonString(json_encode([
            "default_interpreter" => $defaultInterpreter,
            "default_cache_time" => $defaultCacheTime,
            "supported_callbacks" => $supportedCallbacks,
        ]), json_encode($configuration));

        config(['opendialog.interpreter_engine.default_interpreter' => 'test_interpreter']);
        config(['opendialog.interpreter_engine.default_cache_time' => 0]);
        config(['opendialog.interpreter_engine.supported_callbacks' => []]);

        $this->assertEquals('test_interpreter', $configuration->getDefaultInterpreter());
        $this->assertEquals(0, $configuration->getDefaultCacheTime());
        $this->assertEmpty($configuration->getSupportedCallbacks()->toArray());
        $this->assertJsonStringEqualsJsonString(json_encode([
            "default_interpreter" => 'test_interpreter',
            "default_cache_time" => 0,
            "supported_callbacks" => [],
        ]), json_encode($configuration));
    }

    public function testGetAvailableOperations()
    {
        $reflection = resolve(ReflectionHelperInterface::class)->getOperationEngineReflection();
        $operations = $reflection->getAvailableOperations();

        $operationService = resolve(OperationServiceInterface::class);
        $numberOfCoreOperations = count($operationService->getAvailableOperations());
        $this->assertCount($numberOfCoreOperations, $operations);

        $operationId = DummyOperation::getName();
        $operationService->registerAvailableOperations([
            DummyOperation::class
        ]);

        $operations = $reflection->getAvailableOperations();
        $this->assertCount($numberOfCoreOperations + 1, $operations);

        $this->assertTrue($operations->hasKey($operationId));
        $this->assertEquals(DummyOperation::class, $operations->get($operationId));
    }

    public function testGetAvailableFormatters()
    {
        $reflection = resolve(ReflectionHelperInterface::class)->getResponseEngineReflection();
        $formatters = $reflection->getAvailableFormatters();

        $responseEngineService = resolve(ResponseEngineServiceInterface::class);
        $numberOfCoreFormatters = count($responseEngineService->getAvailableFormatters());
        $this->assertCount($numberOfCoreFormatters, $formatters);

        $formatter = new DummyFormatter();
        $responseEngineService->registerFormatter($formatter);

        $formatters = $reflection->getAvailableFormatters();
        $this->assertCount($numberOfCoreFormatters + 1, $formatters);

        $this->assertTrue($formatters->hasKey($formatter::getName()));
        $this->assertEquals($formatter, $formatters->get($formatter::getName()));
    }

    public function testGetAvailableSensors()
    {
        $reflection = resolve(ReflectionHelperInterface::class)->getSensorEngineReflection();
        $sensors = $reflection->getAvailableSensors();

        $sensorEngineService = resolve(SensorServiceInterface::class);
        $numberOfCoreSensors = count($sensorEngineService->getAvailableSensors());
        $this->assertCount($numberOfCoreSensors, $sensors);

        $sensor = new DummySensor();
        $sensorEngineService->registerSensor($sensor);

        $sensors = $reflection->getAvailableSensors();
        $this->assertCount($numberOfCoreSensors + 1, $sensors);

        $this->assertTrue($sensors->hasKey($sensor::getName()));
        $this->assertEquals($sensor, $sensors->get($sensor::getName()));
    }
}

