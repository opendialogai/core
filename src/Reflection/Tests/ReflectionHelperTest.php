<?php

namespace OpenDialogAi\Core\Reflection\Tests;

use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use OpenDialogAi\ActionEngine\Actions\ExampleAction;
use OpenDialogAi\ActionEngine\Service\ActionEngineInterface;
use OpenDialogAi\AttributeEngine\AttributeResolver\AttributeDeclaration;
use OpenDialogAi\AttributeEngine\Attributes\StringAttribute;
use OpenDialogAi\AttributeEngine\AttributeTypeService\AttributeTypeServiceInterface;
use OpenDialogAi\AttributeEngine\Facades\AttributeResolver;
use OpenDialogAi\AttributeEngine\Tests\ExampleCustomAttributeType;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\Core\Components\ODComponentTypes;
use OpenDialogAi\Core\Reflection\Helper\ReflectionHelperInterface;
use OpenDialogAi\Core\ResponseEngine\Tests\Formatters\TestFormatter;
use OpenDialogAi\Core\SensorEngine\Tests\Sensors\DummySensor;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\InterpreterEngine\Service\InterpreterServiceInterface;
use OpenDialogAi\InterpreterEngine\Tests\Interpreters\DummyInterpreter;
use OpenDialogAi\OperationEngine\Operations\EquivalenceOperation;
use OpenDialogAi\OperationEngine\Service\OperationServiceInterface;
use OpenDialogAi\OperationEngine\Tests\Operations\DummyOperation;
use OpenDialogAi\ResponseEngine\Service\ResponseEngineServiceInterface;
use OpenDialogAi\SensorEngine\Service\SensorServiceInterface;

class ReflectionHelperTest extends TestCase
{
    use ArraySubsetAsserts;

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
        $this->assertTrue($actions->hasKey($exampleAction::getComponentId()));
        $this->assertEquals($exampleAction, $actions->get($exampleAction::getComponentId()));

        $this->assertArraySubset([
            'available_actions' => [
                $exampleAction::getComponentId() => [
                    'component_data' => [
                        'type' => 'action',
                        'source' => 'app',
                        'id' => $exampleAction::getComponentId(),
                        'name' => 'Example action',
                        'description' => 'Just an example action.',
                    ],
                    'action_data' => [
                        'required_attributes' => [
                            'first_name',
                            'last_name',
                        ],
                        'output_attributes' => [
                            'first_name',
                            'last_name',
                            'full_name',
                        ]
                    ]
                ]
            ]
        ], json_decode(json_encode($reflection), true));
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
        $this->assertEquals(ExampleCustomAttributeType::class, $attributeTypes->get($typeId));

        $this->assertArraySubset([
            'available_attribute_types' => [
                'attribute.core.string' => [
                    'component_data' => [
                        'type' => 'attribute_type',
                        'source' => 'core',
                        'id' => 'attribute.core.string',
                        'name' => StringAttribute::getComponentName(),
                        'description' => StringAttribute::getComponentDescription(),
                    ]
                ],
                $typeId => [
                    'component_data' => [
                        'type' => 'attribute_type',
                        'source' => 'app',
                        'id' => $typeId,
                        'name' => 'Example attribute type',
                        'description' => 'Just an example attribute type.',
                    ]
                ],
            ]
        ], json_decode(json_encode($reflection), true));
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

        /** @var AttributeDeclaration $attributeDeclaration */
        $attributeDeclaration = $attributes->get($attributeId);
        $this->assertEquals($attributeId, $attributeDeclaration->getAttributeId());
        $this->assertEquals(ODComponentTypes::APP_COMPONENT_SOURCE, $attributeDeclaration->getSource());
        $this->assertEquals(StringAttribute::class, $attributeDeclaration->getAttributeTypeClass());

        $this->assertArraySubset([
            'available_attributes' => [
                'last_seen' => [
                    'component_data' => [
                        'type' => 'attribute',
                        'source' => 'core',
                        'id' => 'last_seen',
                        'name' => null,
                        'description' => null,
                    ],
                    'attribute_data' => [
                        "type" => "attribute.core.timestamp",
                    ],
                ],
                $attributeId => [
                    'component_data' => [
                        'type' => 'attribute',
                        'source' => 'app',
                        'id' => $attributeId,
                        'name' => null,
                        'description' => null,
                    ],
                    'attribute_data' => [
                        "type" => "attribute.core.string",
                    ],
                ],
            ]
        ], json_decode(json_encode($reflection), true));
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

        $this->assertArraySubset([
            'available_contexts' => [
                'user' => [
                    'component_data' => [
                        'type' => 'context',
                        'source' => 'core',
                        'id' => 'user',
                        'name' => 'User',
                        'description' => 'A context for storing data about the user.',
                    ],
                    'context_data' => [
                        'attributesReadOnly' => false,
                    ],
                ],
                '_intent' => [
                    'component_data' => [
                        'type' => 'context',
                        'source' => 'core',
                        'id' => '_intent',
                        'name' => 'User',
                        'description' => 'A context managed by OpenDialog for storing data about each interpreted intent.',
                    ],
                    'context_data' => [
                        'attributesReadOnly' => true,
                    ],
                ],
                $contextId => [
                    'component_data' => [
                        'type' => 'context',
                        'source' => 'app',
                        'id' => $contextId,
                        'name' => null,
                        'description' => null,
                    ],
                    'context_data' => [
                        'attributesReadOnly' => false,
                    ],
                ],
            ]
        ], json_decode(json_encode($reflection), true));
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
        $this->assertInstanceOf(DummyInterpreter::class, $interpreters->get($interpreterId));

        $this->assertArraySubset([
            'available_interpreters' => [
                'interpreter.core.callback' => [
                    'component_data' => [
                        'type' => 'interpreter',
                        'source' => 'core',
                        'id' => 'interpreter.core.callback',
                        'name' => 'Callback',
                        'description' => 'An interpreter for directly matching intent names.',
                    ],
                ],
                $interpreterId => [
                    'component_data' => [
                        'type' => 'interpreter',
                        'source' => 'app',
                        'id' => $interpreterId,
                        'name' => null,
                        'description' => null,
                    ],
                ],
            ]
        ], json_decode(json_encode($reflection), true));
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

        $this->assertArraySubset([
            'engine_configuration' => [
                "default_interpreter" => 'test_interpreter',
                "default_cache_time" => 0,
                "supported_callbacks" => [],
            ]
        ], json_decode(json_encode($reflection), true));
    }

    public function testGetAvailableOperations()
    {
        $reflection = resolve(ReflectionHelperInterface::class)->getOperationEngineReflection();
        $operations = $reflection->getAvailableOperations();

        $operationService = resolve(OperationServiceInterface::class);
        $numberOfCoreOperations = count($operationService->getAvailableOperations());
        $this->assertCount($numberOfCoreOperations, $operations);

        $operationId = DummyOperation::getComponentId();
        $operationService->registerAvailableOperations([
            DummyOperation::class
        ]);

        $operations = $reflection->getAvailableOperations();
        $this->assertCount($numberOfCoreOperations + 1, $operations);

        $this->assertTrue($operations->hasKey($operationId));
        $this->assertInstanceOf(DummyOperation::class, $operations->get($operationId));

        $this->assertArraySubset([
            'available_operations' => [
                'eq' => [
                    'component_data' => [
                        'type' => 'operation',
                        'source' => 'core',
                        'id' => 'eq',
                        'name' => EquivalenceOperation::getComponentName(),
                        'description' => EquivalenceOperation::getComponentDescription(),
                    ],
                    'operation_data' => [
                        'attributes' => [
                            'attribute' => [
                                'required' => true,
                            ]
                        ],
                        'parameters' => [
                            'value' => [
                                'required' => true,
                            ]
                        ],
                    ]
                ],
                $operationId => [
                    'component_data' => [
                        'type' => 'operation',
                        'source' => 'app',
                        'id' => $operationId,
                        'name' => 'Example operation',
                        'description' => 'Just an example operation.',
                    ],
                    'operation_data' => [
                        'attributes' => [],
                        'parameters' => [],
                    ]
                ],
            ]
        ], json_decode(json_encode($reflection), true));
    }

    public function testGetAvailableFormatters()
    {
        $reflection = resolve(ReflectionHelperInterface::class)->getResponseEngineReflection();
        $formatters = $reflection->getAvailableFormatters();

        $responseEngineService = resolve(ResponseEngineServiceInterface::class);
        $numberOfCoreFormatters = count($responseEngineService->getAvailableFormatters());
        $this->assertCount($numberOfCoreFormatters, $formatters);

        $formatter = new TestFormatter();
        $responseEngineService->registerFormatter($formatter);

        $formatters = $reflection->getAvailableFormatters();
        $this->assertCount($numberOfCoreFormatters + 1, $formatters);

        $this->assertTrue($formatters->hasKey($formatter::getName()));
        $this->assertEquals($formatter, $formatters->get($formatter::getName()));

        $this->assertArraySubset([
            'available_formatters' => [
                'formatter.core.webchat' => [
                    'component_data' => [
                        'type' => 'formatter',
                        'source' => 'core',
                        'id' => 'formatter.core.webchat',
                        'name' => 'Webchat',
                        'description' => 'A formatter for sending messages to OpenDialog Webchat.',
                    ],
                    'formatter_data' => [
                        'supported_message_types' => [
                            'attribute-message',
                            'button-message',
                            'hand-to-system-message',
                            'image-message',
                            'list-message',
                            'text-message',
                            'rich-message',
                            'form-message',
                            'fp-form-message',
                            'fp-rich-message',
                            'long-text-message',
                            'empty-message',
                            'cta-message',
                            'meta-message',
                            'autocomplete-message',
                            'date-picker-message',
                        ],
                    ]
                ],
                $formatter::getName() => [
                    'component_data' => [
                        'type' => 'formatter',
                        'source' => 'app',
                        'id' => $formatter::getName(),
                        'name' => 'Example formatter',
                        'description' => 'Just an example formatter.',
                    ],
                    'formatter_data' => [
                        'supported_message_types' => [
                            'text-message'
                        ]
                    ]
                ],
            ]
        ], json_decode(json_encode($reflection), true));
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

        $this->assertArraySubset([
            'available_sensors' => [
                'sensor.core.webchat' => [
                    'component_data' => [
                        'type' => 'sensor',
                        'source' => 'core',
                        'id' => 'sensor.core.webchat',
                        'name' => 'Webchat',
                        'description' => 'A sensor for receiving messages from OpenDialog Webchat.',
                    ],
                    'sensor_data' => [
                        'supported_utterance_types' => [
                            'chat_open',
                            'text',
                            'trigger',
                            'button_response',
                            'url_click',
                            'longtext_response',
                            'form_response',
                        ],
                    ]
                ],
                $sensor::getName() => [
                    'component_data' => [
                        'type' => 'sensor',
                        'source' => 'app',
                        'id' => $sensor::getName(),
                        'name' => 'Example sensor',
                        'description' => 'Just an example sensor.',
                    ],
                    'sensor_data' => [
                        'supported_utterance_types' => [
                            'text'
                        ]
                    ]
                ],
            ]
        ], json_decode(json_encode($reflection), true));
    }
}
