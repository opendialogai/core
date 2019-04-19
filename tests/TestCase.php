<?php

namespace OpenDialogAi\Core\Tests;

use OpenDialogAi\ActionEngine\ActionEngineServiceProvider;
use OpenDialogAi\ContextEngine\ContextEngineServiceProvider;
use OpenDialogAi\ConversationEngine\ConversationEngineServiceProvider;
use OpenDialogAi\InterpreterEngine\InterpreterEngineServiceProvider;
use OpenDialogAi\ConversationBuilder\ConversationBuilderServiceProvider;
use OpenDialogAi\ConversationLog\ConversationLogServiceProvider;
use OpenDialogAi\ResponseEngine\ResponseEngineServiceProvider;
use OpenDialogAi\SensorEngine\SensorEngineServiceProvider;
use OpenDialogAi\Core\CoreServiceProvider;

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
    protected function setUp() :void
    {
        parent::setUp();

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
            SensorEngineServiceProvider::class
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
        attribute: user.name
        operation: is_not_set
    - condition:
        attribute: user.test
        operation: gt
        value: 10
  scenes:
    opening_scene:
      intents:
        - u: 
            i: hello_bot
            interpreter: interpreter.core.callbackInterpreter
            action: action.core.example
        - b: 
            i: hello_user
            action: action.core.example
            scene: scene2
        - b: 
            i: hello_registered_user
            action: action.core.example
            scene: scene3
    scene2:
      intents:
        - u: 
            i: how_are_you
            interpreter: interpreter.core.callbackInterpreter
            confidence: 1
            action: action.core.example
        - b: 
            i: doing_dandy
            action: action.core.example
            completes: true 
    scene3:
      intents:
        - u:
            i: weather_question
            action: action.core.example
        - b:
            i: weather_answer    
        - u: 
            i: will_you_cope
            interpreter: interpreter.core.callbackInterpreter
            action: action.core.example
        - b: 
            i: doing_dandy
            action: action.core.example
            completes: true           
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
            action: action.core.example
        - b: 
            i: hello_user
            action: action.core.example
    scene2:
      intents:
        - u: 
            i: how_are_you
            interpreter: interpreter.core.callbackInterpreter
            action: action.core.example
        - b: 
            i: doing_dandy
            action: action.core.example 
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
            action: action.core.example
        - b: 
            i: hello_user
            action: action.core.example
    scene2:
      intents:
        - u: 
            i: how_are_you
            interpreter: interpreter.core.callbackInterpreter
            action: action.core.example
        - b: 
            i: doing_dandy
            action: action.core.example   
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
}
