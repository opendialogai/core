<?php

namespace OpenDialogAi\Core\Tests;

use Exception;
use Mockery;
use OpenDialogAi\ActionEngine\ActionEngineServiceProvider;
use OpenDialogAi\AttributeEngine\AttributeEngineServiceProvider;
use OpenDialogAi\ContextEngine\ContextEngineServiceProvider;
use OpenDialogAi\ConversationBuilder\Conversation;
use OpenDialogAi\ConversationBuilder\ConversationBuilderServiceProvider;
use OpenDialogAi\ConversationEngine\ConversationEngineServiceProvider;
use OpenDialogAi\ConversationEngine\ConversationStore\DGraphConversationStore;
use OpenDialogAi\ConversationLog\ConversationLogServiceProvider;
use OpenDialogAi\Core\Conversation\Conversation as ConversationNode;
use OpenDialogAi\Core\CoreServiceProvider;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;
use OpenDialogAi\Core\Graph\GraphServiceProvider;
use OpenDialogAi\InterpreterEngine\InterpreterEngineServiceProvider;
use OpenDialogAi\InterpreterEngine\InterpreterInterface;
use OpenDialogAi\NlpEngine\NlpEngineServiceProvider;
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

        if ($overwriteDgraphUrl = getenv("OVERWRITE_DGRAPH_URL")) {
            $this->app['config']->set('opendialog.core.DGRAPH_URL', $overwriteDgraphUrl);
        }

        if ($overwriteDgraphPort = getenv("OVERWRITE_DGRAPH_PORT")) {
            $this->app['config']->set('opendialog.core.DGRAPH_PORT', $overwriteDgraphPort);
        }

        $this->checkRequirements();

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
            GraphServiceProvider::class,
            ActionEngineServiceProvider::class,
            ConversationBuilderServiceProvider::class,
            ConversationEngineServiceProvider::class,
            ConversationLogServiceProvider::class,
            ResponseEngineServiceProvider::class,
            AttributeEngineServiceProvider::class,
            ContextEngineServiceProvider::class,
            InterpreterEngineServiceProvider::class,
            OperationEngineServiceProvider::class,
            SensorEngineServiceProvider::class,
            NlpEngineServiceProvider::class
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
            action: action.core.example
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

    protected function conversationWithManyOpeningIntents()
    {
        return <<<EOT
conversation:
  id: many_opening_intents
  scenes:
    opening_scene:
      intents:
        - u:
            i: intent.core.opening_1
        - u:
            i: intent.core.opening_2
        - u:
            i: intent.core.opening_3
        - b:
            i: intent.core.ask_name
        - u:
            i: intent.core.send_name
        - b:
            i: intent.core.response
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

    /**
     * Checks whether the test has the appropriate requirements to run
     */
    private function checkRequirements()
    {
        $annotations = $this->getAnnotations();

        foreach (array('class', 'method') as $depth) {
            if (empty($annotations[$depth]['requires'])) {
                continue;
            }

            $requires = array_flip($annotations[$depth]['requires']);

            if (isset($requires['DGRAPH'])) {
                if ($this->isRunningDDgraph()) {
                    $this->initDDgraph();
                } else {
                    $this->markTestSkipped('Test requires DGraph to be running');
                }
            }
        }
    }

    protected function isRunningDDgraph(): bool
    {
        /** @var DGraphClient $client */
        $client = $this->app->make(DGraphClient::class);
        return $client->isConnected();
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
    protected function activateConversation($conversationYaml): ConversationNode
    {
        $name = Yaml::parse($conversationYaml)['conversation']['id'];

        /** @var Conversation $conversation */
        $conversation = Conversation::create(['name' => $name, 'model' => $conversationYaml]);
        $conversation->save();

        $this->assertTrue($conversation->activateConversation());

        $dGraphConversationStore = resolve(DGraphConversationStore::class);

        return $dGraphConversationStore->getConversationConverter()->convertConversation(
            $dGraphConversationStore->getEIModelConversationTemplate($name)
        );
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

    protected function conversationWithSceneConditions()
    {
        return <<< EOT
conversation:
  id: with_scene_conditions
  scenes:
    opening_scene:
      intents:
        - u:
            i: opening_user_s1
            interpreter: interpreter.core.callbackInterpreter
            scene: scene1
        - u:
            i: opening_user_s2
            interpreter: interpreter.core.callbackInterpreter
            scene: scene2
        - u:
            i: opening_user_none
            interpreter: interpreter.core.callbackInterpreter
        - b: 
            i: opening_bot_response
        - u:
            i: opening_user_s3
            interpreter: interpreter.core.callbackInterpreter
            scene: scene3
        - u:
            i: opening_user_none2
            interpreter: interpreter.core.callbackInterpreter
        - b: 
            i: opening_bot_complete
            completes: true
    scene1:
      conditions:
        - condition:
            operation: is_not_set
            attributes:
              attribute1: user.user_email
      intents:
        - b: 
            i: scene1_bot
            completes: true
    scene2:
      conditions:
        - condition:
            operation: eq
            attributes:
              attribute1: user.user_name
            parameters:
              value: test_user
      intents:
        - b: 
            i: scene2_bot
            completes: true
    scene3:
      conditions:
        - condition:
            operation: eq
            attributes:
              attribute1: user.user_name
            parameters:
              value: test_user2
      intents:
        - b: 
            i: scene3_bot
            completes: true
EOT;
    }

    /**
     * @return ConversationNode
     */
    public function createConversationWithManyIntentsWithSameId(): ConversationNode
    {
        $conversationMarkup = $this->getMarkupForManyIntentConversation();

        try {
            return $this->activateConversation($conversationMarkup);
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @return string
     */
    public function getMarkupForManyIntentConversation(): string
    {
        $conversationMarkup =
            /** @lang yaml */
            <<<EOT
conversation:
  id: rock_paper_scissors
  scenes:
    opening_scene:
      intents:
        - u:
            i: intent.app.play_game
        - b:
            i: intent.app.init_game
        - u:
            i: intent.app.send_choice
            expected_attributes:
                - id: user.user_choice
        - b:
            i: intent.app.round_2
        - u:
            i: intent.app.send_choice
            expected_attributes:
                - id: user.user_choice
        - b:
            i: intent.app.final_round
        - u:
            i: intent.app.send_choice
            expected_attributes:
                - id: user.user_choice
            conditions:
                - condition:
                    operation: eq
                    attributes:
                        attribute1: user.game_result
                    parameters:
                        value: BOT_WINS
            scene: bot_won
        - u:
            i: intent.app.send_choice
            expected_attributes:
                - id: user.user_choice
        - b:
            i: intent.app.you_won
            completes: true
    bot_won:
      intents:
        - b:
            i: intent.app.you_lost
            completes: true
EOT;
        return $conversationMarkup;
    }

    /**
     * @return ConversationNode
     */
    public function createConversationWithVirtualIntent(): ConversationNode
    {
        $conversationMarkup = $this->getMarkupForConversationWithVirtualIntent();

        try {
            return $this->activateConversation($conversationMarkup);
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @return ConversationNode
     */
    public function createConversationWithMultipleVirtualIntents(): ConversationNode
    {
        $conversationMarkup = $this->getMarkupForConversationWithMultipleVirtualIntents();

        try {
            return $this->activateConversation($conversationMarkup);
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @return ConversationNode
     */
    public function createConversationWithRepeatingIntent(): ConversationNode
    {
        $conversationMarkup = $this->getMarkupForConversationWithRepeatingIntent();

        try {
            return $this->activateConversation($conversationMarkup);
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @return ConversationNode
     */
    public function createConversationWithRepeatingIntentCrossScene(): ConversationNode
    {
        $conversationMarkup = $this->getMarkupForConversationWithRepeatingIntentCrossScene();

        try {
            return $this->activateConversation($conversationMarkup);
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    public function getMarkupForConversationWithVirtualIntent(): string
    {
        /** @lang yaml */
        return <<<EOT
conversation:
  id: with_virtual_intent
  scenes:
    opening_scene:
      intents:
          - u:
              i: intent.app.welcome
          - b:
              i: intent.app.welcomeResponse
              u_virtual:
                i: intent.app.continue
          - u:
              i: intent.app.continue
          - b:
              i: intent.app.endResponse
              completes: true
EOT;
    }

    public function getMarkupForConversationWithMultipleVirtualIntents(): string
    {
        /** @lang yaml */
        return <<<EOT
conversation:
  id: with_virtual_intents
  scenes:
    opening_scene:
      intents:
          - u:
              i: intent.app.welcome
          - b:
              i: intent.app.welcomeResponse
              u_virtual:
                i: intent.app.continue
              scene: next_scene
    next_scene:
      intents:
          - u:
              i: intent.app.continue
              conditions:
                - condition:
                    operation: is_set
                    attributes:
                      attribute: user.test
              scene: test_scene
          - u:
              i: intent.app.continue
              conditions:
                - condition:
                    operation: is_not_set
                    attributes:
                      attribute: user.test
          - b:
              i: intent.app.continueResponse
              u_virtual:
                i: intent.app.continue
          - u:
              i: intent.app.continue
          - b:
              i: intent.app.nextResponse
              completes: true
    test_scene:
      intents:
          - u:
              i: intent.app.continue
          - b:
              i: intent.app.testResponse
              completes: true
EOT;
    }

    public function getMarkupForConversationWithRepeatingIntent(): string
    {
        /** @lang yaml */
        return <<<EOT
conversation:
  id: with_repeating_intent
  scenes:
    opening_scene:
      intents:
          - u:
              i: intent.app.welcome
          - b:
              i: intent.app.welcomeResponse
          - u:
              i: intent.app.question
              repeating: true
          - u:
              i: intent.app.questionStop
              scene: stop_scene
          - b:
              i: intent.app.questionResponse
              completes: true
    stop_scene:
      intents:
          - b:
              i: intent.app.endResponse
              completes: true
EOT;
    }

    public function getMarkupForConversationWithRepeatingIntentCrossScene(): string
    {
        /** @lang yaml */
        return <<<EOT
conversation:
  id: with_repeating_intent
  scenes:
    opening_scene:
      intents:
          - u:
              i: intent.app.welcome
          - b:
              i: intent.app.welcomeResponse
          - u:
              i: intent.app.question
              repeating: true
              scene: next_scene
          - u:
              i: intent.app.questionStop
              scene: stop_scene
    next_scene:
      intents:
          - b:
              i: intent.app.questionResponse
              completes: true
    stop_scene:
      intents:
          - b:
              i: intent.app.endResponse
              completes: true
EOT;
    }
}
