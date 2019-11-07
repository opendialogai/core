<?php

namespace OpenDialogAi\Core\Tests;

use Exception;
use Mockery;
use OpenDialogAi\ActionEngine\ActionEngineServiceProvider;
use OpenDialogAi\ContextEngine\ContextEngineServiceProvider;
use OpenDialogAi\ConversationBuilder\Conversation;
use OpenDialogAi\ConversationBuilder\ConversationBuilderServiceProvider;
use OpenDialogAi\ConversationEngine\ConversationEngineServiceProvider;
use OpenDialogAi\ConversationLog\ConversationLogServiceProvider;
use OpenDialogAi\Core\CoreServiceProvider;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;
use OpenDialogAi\InterpreterEngine\InterpreterEngineServiceProvider;
use OpenDialogAi\InterpreterEngine\InterpreterInterface;
use OpenDialogAi\OperationEngine\OperationEngineServiceProvider;
use OpenDialogAi\ResponseEngine\ResponseEngineServiceProvider;
use OpenDialogAi\SensorEngine\SensorEngineServiceProvider;
use Symfony\Component\Yaml\Yaml;

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
    /**
     * @var bool Whether DGraph has been initialised or not
     */
    private $dgraphInitialised = false;

    protected function setUp(): void
    {
        parent::setUp();

        try {
            $env = parse_ini_file(__DIR__ . '/../.env');
            if (isset($env['DGRAPH_URL'])) {
                $this->app['config']->set('opendialog.core.DGRAPH_URL', $env['DGRAPH_URL']);
            }
        } catch (Exception $e) {
            //
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
            ConversationBuilderServiceProvider::class,
            ConversationEngineServiceProvider::class,
            ConversationLogServiceProvider::class,
            ResponseEngineServiceProvider::class,
            ContextEngineServiceProvider::class,
            InterpreterEngineServiceProvider::class,
            OperationEngineServiceProvider::class,
            SensorEngineServiceProvider::class,
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

    protected function conversation1()
    {
        return <<<EOT
conversation:
  id: hello_bot_world
  conditions:
    - condition:
        operation: is_not_set
        attributes:
          username: user.name
    - condition:
        operation: gt
        attributes:
          usertest: user.test
        parameters:
          value: 10
  scenes:
    opening_scene:
      intents:
        - u:
            i: hello_bot
            interpreter: interpreter.core.callbackInterpreter
            action:
              id: action.core.example
              input_attributes:
                - user.first_name
                - user.last_name
        - b: 
            i: hello_user
            action:
              id: action.core.example
              input_attributes:
                - user.first_name
                - user.last_name
            scene: scene2
        - b:
            i: hello_registered_user
            action:
              id: action.core.example
              input_attributes:
                - user.first_name
                - user.last_name
            scene: scene3
    scene2:
      intents:
        - u:
            i: how_are_you
            interpreter: interpreter.core.callbackInterpreter
            confidence: 1
            action:
              id: action.core.example
              input_attributes:
                - user.first_name
                - user.last_name
              output_attributes:
                - user.first_name
                - session.last_name
        - b: 
            i: doing_dandy
            action:
              id: action.core.example
              input_attributes:
                - user.first_name
                - user.last_name
            completes: true
    scene3:
      intents:
        - u:
            i: weather_question
            action:
              id: action.core.example
              input_attributes:
                - user.first_name
                - user.last_name
        - b:
            i: weather_answer
        - u: 
            i: will_you_cope
            interpreter: interpreter.core.callbackInterpreter
            action:
              id: action.core.example
              input_attributes:
                - user.first_name
                - user.last_name
        - b: 
            i: doing_dandy
            action:
              id: action.core.example
              input_attributes:
                - user.first_name
                - user.last_name
            completes: true
    scene4:
      intents:
        - b:
            i: intent.core.example
        - u:
            i: intent.core.example2
            interpreter: interpreter.core.callbackInterpreter
            expected_attributes:
              - id: user.name
            scene: scene3
EOT;
    }

    protected function conversation2()
    {
        return <<<EOT
conversation:
  id: hello_bot_world2
  scenes:
    opening_scene:
      intents:
        - u: 
            i: howdy_bot
            interpreter: interpreter.core.callbackInterpreter
            action:
              id: action.core.example
              input_attributes:
                - user.first_name
                - user.last_name
        - b: 
            i: hello_user
            action:
              id: action.core.example
              input_attributes:
                - user.first_name
                - user.last_name
    scene2:
      intents:
        - u: 
            i: how_are_you
            interpreter: interpreter.core.callbackInterpreter
            action:
              id: action.core.example
              input_attributes:
                - user.first_name
                - user.last_name
        - b: 
            i: doing_dandy
            action:
              id: action.core.example
              input_attributes:
                - user.first_name
                - user.last_name
            completes: true           
EOT;
    }

    protected function conversation3()
    {
        return <<<EOT
conversation:
  id: hello_bot_world3
  scenes:
    opening_scene:
      intents:
        - u: 
            i: top_of_the_morning_bot
            interpreter: interpreter.core.callbackInterpreter
            action:
              id: action.core.example
              input_attributes:
                - user.first_name
                - user.last_name
        - b: 
            i: hello_user
            action:
              id: action.core.example
              input_attributes:
                - user.first_name
                - user.last_name
    scene2:
      intents:
        - u: 
            i: how_are_you
            interpreter: interpreter.core.callbackInterpreter
            action:
              id: action.core.example
              input_attributes:
                - user.first_name
                - user.last_name
        - b: 
            i: doing_dandy
            action:
              id: action.core.example
              input_attributes:
                - user.first_name
                - user.last_name
            completes: true
EOT;
    }

    protected function conversation4()
    {
        return <<<EOT
conversation:
  id: no_match_conversation
  scenes:
    opening_scene:
      intents:
        - u:
            i: intent.core.NoMatch
        - b:
            i: intent.core.NoMatchResponse
            completes: true
EOT;
    }

    /**
     * Returns the no match conversation
     *
     * @return string
     */
    protected function noMatchConversation()
    {
        return $this->conversation4();
    }

    protected function initDDgraph(): void
    {
        if (!$this->dgraphInitialised) {
            /** @var DGraphClient $client */
            $client = $this->app->make(DGraphClient::class);
            $client->dropSchema();
            $client->initSchema();
            $this->dgraphInitialised = true;
        }
    }

    /**
     * Activate the given conversation YAML and assert that it activates successfully.
     */
    protected function activateConversation($conversationYaml): void
    {
        if (!$this->dgraphInitialised) {
            $this->initDDgraph();
        }

        $name = Yaml::parse($conversationYaml)['conversation']['id'];

        /** @var Conversation $conversation */
        $conversation = Conversation::create(['name' => $name, 'model' => $conversationYaml]);
        $conversation->save();
        $conversationModel = $conversation->buildConversation();

        $this->assertTrue($conversation->activateConversation($conversationModel));
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

        $this->app['config']->set('opendialog.interpreter_engine.default_interpreter', $defaultInterpreter::getName());
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
        $mockInterpreter->shouldReceive('getName')->andReturn($interpreterName);

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
     * Adds the custom attributes and unsets the ContextService to unbind from the service layer
     *
     * @param array $customAttribute
     */
    protected function setCustomAttributes(array $customAttribute)
    {
        $this->setConfigValue('opendialog.context_engine.custom_attributes', $customAttribute);
    }
}
