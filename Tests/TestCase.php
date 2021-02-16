<?php

namespace OpenDialogAi\Core\Tests;

use Mockery;
use OpenDialogAi\ActionEngine\ActionEngineServiceProvider;
use OpenDialogAi\AttributeEngine\AttributeEngineServiceProvider;
use OpenDialogAi\AttributeEngine\CoreAttributes\UserAttribute;
use OpenDialogAi\AttributeEngine\CoreAttributes\UtteranceAttribute;
use OpenDialogAi\ContextEngine\ContextEngineServiceProvider;
use OpenDialogAi\ConversationEngine\ConversationEngineServiceProvider;
use OpenDialogAi\ConversationLog\ConversationLogServiceProvider;
use OpenDialogAi\Core\CoreServiceProvider;
use OpenDialogAi\Core\Reflection\ReflectionServiceProvider;
use OpenDialogAi\InterpreterEngine\InterpreterEngineServiceProvider;
use OpenDialogAi\InterpreterEngine\InterpreterInterface;
use OpenDialogAi\NlpEngine\NlpEngineServiceProvider;
use OpenDialogAi\OperationEngine\OperationEngineServiceProvider;
use OpenDialogAi\ResponseEngine\ResponseEngineServiceProvider;
use OpenDialogAi\SensorEngine\SensorEngineServiceProvider;

/**
 * Base TestCase class for setting up all package tests
 *
 * @method assertCount($expected, $actual)
 * @method assertEquals($expected, $actual)
 * @method assertContains($needle, $haystack)
 * @method assertNotNull($actual)
 * @method fail($message)
 */
class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if ($overwriteDgraphUrl = getenv("OVERWRITE_DGRAPH_URL")) {
            $this->app['config']->set('opendialog.core.DGRAPH_URL', $overwriteDgraphUrl);
        }

        if ($overwriteDgraphPort = getenv("OVERWRITE_DGRAPH_PORT")) {
            $this->app['config']->set('opendialog.core.DGRAPH_PORT', $overwriteDgraphPort);
        }

        if ($overwriteDgraphAPIKey = getenv("OVERWRITE_DGRAPH_PORT")) {
            $this->app['config']->set('opendialog.core.DGRAPH_API_KEY', $overwriteDgraphAPIKey);
        }


        if (!defined('LARAVEL_START')) {
            define('LARAVEL_START', microtime(true));
        }

        $this->artisan('migrate', [
            '--database' => 'testbench'
        ]);
    }

    /**
     * Sets a config value to the app
     *
     * @param $configName
     * @param $config
     */
    public function setConfigValue($configName, $config)
    {
        $this->app['config']->set($configName, $config);
    }

    public function getPackageProviders($app)
    {
        return [
            CoreServiceProvider::class,
            ActionEngineServiceProvider::class,
//            ConversationBuilderServiceProvider::class,
            ConversationEngineServiceProvider::class,
            ConversationLogServiceProvider::class,
            ResponseEngineServiceProvider::class,
            AttributeEngineServiceProvider::class,
            ContextEngineServiceProvider::class,
            InterpreterEngineServiceProvider::class,
            OperationEngineServiceProvider::class,
            SensorEngineServiceProvider::class,
            NlpEngineServiceProvider::class,
            ReflectionServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        # Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    /**
     * Register a single interpreter and default interpreter
     *
     * @param $interpreter
     * @param null $defaultInterpreter
     */
    protected function registerSingleInterpreter($interpreter, $defaultInterpreter = null): void
    {
        if ($defaultInterpreter === null) {
            $defaultInterpreter = $interpreter;
        }

        $this->app['config']->set(
            'opendialog.interpreter_engine.available_interpreters',
            [
            get_class($interpreter),
            get_class($defaultInterpreter)
            ]
        );

        $this->app['config']->set('opendialog.interpreter_engine.default_interpreter', $defaultInterpreter::getComponentId());
    }

    /**
     * @param $interpreters
     * @param null $defaultInterpreter If not sent, the first interpreter in the array will be used as default
     */
    protected function registerMultipleInterpreters($interpreters, $defaultInterpreter = null)
    {
        $classes = [];

        if ($defaultInterpreter === null) {
            $defaultInterpreter = $interpreters[0];
        } else {
            $classes[] = get_class($defaultInterpreter);
        }

        foreach ($interpreters as $interpreter) {
            $classes[] = get_class($interpreter);
        }

        $this->app['config']->set(
            'opendialog.interpreter_engine.available_interpreters',
            $classes
        );

        $this->app['config']->set('opendialog.interpreter_engine.default_interpreter', $defaultInterpreter::getName());
    }

    /**
     * Register a single interpreter and default interpreter
     *
     * @param $action
     */
    protected function registerSingleAction($action): void
    {
        $this->app['config']->set(
            'opendialog.action_engine.available_actions',
            [
                get_class($action),
            ]
        );
    }

    /**
     * @param $interpreterName
     * @return \Mockery\MockInterface|InterpreterInterface
     */
    protected function createMockInterpreter($interpreterName)
    {
        $mockInterpreter = Mockery::mock(InterpreterInterface::class);
        $mockInterpreter->shouldReceive('getComponentId')->andReturn($interpreterName);
        $mockInterpreter->shouldReceive('getComponentData')->andReturn([]);

        return $mockInterpreter;
    }

    /**
     * Sets an array of supported callbacks
     *
     * @param $callbacks
     */
    protected function setSupportedCallbacks($callbacks)
    {
        $this->app['config']->set('opendialog.interpreter_engine.supported_callbacks', $callbacks);
    }

    /**
     * Adds the custom attributes
     *
     * @param array $customAttributes
     */
    protected function setCustomAttributes(array $customAttributes)
    {
        $this->setConfigValue('opendialog.attribute_engine.custom_attributes', $customAttributes);
    }

    /**
     * Adds the custom attribute types
     *
     * @param array $customAttributeTypes
     */
    protected function setCustomAttributeTypes(array $customAttributeTypes)
    {
        $this->setConfigValue('opendialog.attribute_engine.custom_attribute_types', $customAttributeTypes);
    }

    protected function createWebchatMessageUtteranceAttribute(): UtteranceAttribute
    {
        $utterance = new UtteranceAttribute('utterance');

        $utteranceUser = new UserAttribute(UtteranceAttribute::UTTERANCE_USER);
        $utteranceUser->setUserAttribute(UserAttribute::FIRST_NAME, 'Jean-Luc');
        $utteranceUser->setUserAttribute(UserAttribute::LAST_NAME, 'Picard');
        $utteranceUser->setUserAttribute(UserAttribute::EMAIL, 'picard@enterprise.federation');

        return $utterance
            ->setPlatform(UtteranceAttribute::WEBCHAT_PLATFORM)
            ->setUtteranceAttribute(UtteranceAttribute::TYPE, UtteranceAttribute::WEBCHAT_MESSAGE)
            ->setUtteranceAttribute(UtteranceAttribute::UTTERANCE_TEXT, 'Hello')
            ->setUtteranceAttribute(UtteranceAttribute::UTTERANCE_USER_ID, '1234')
            ->setUtteranceAttribute(UtteranceAttribute::UTTERANCE_USER, $utteranceUser);
    }
}
