<?php

namespace OpenDialogAi\Core\Tests;

use OpenDialogAi\ActionEngine\ActionEngineServiceProvider;
use OpenDialogAi\ContextEngine\ContextEngineServiceProvider;
use OpenDialogAi\ConversationEngine\ConversationEngineServiceProvider;
use OpenDialogAi\InterpreterEngine\InterpreterEngineServiceProvider;
use OpenDialogAi\ConversationBuilder\ConversationBuilderServiceProvider;
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
  scenes:
    opening_scene:
      intents:
        - u: 
            i: hello_bot
            interpreter: hello_interpreter1
            action: register_hello
        - b: 
            i: hello_user
            action: register_hello
    scene2:
      intents:
        - u: 
            i: how_are_you
            interpreter: how_are_you_interpreter
            action: wave
        - b: 
            i: doing_dandy
            action: wave_back            
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
            i: hello_bot
            interpreter: hello_interpreter1
            action: register_hello
        - b: 
            i: hello_user
            action: register_hello
    scene2:
      intents:
        - u: 
            i: how_are_you
            interpreter: how_are_you_interpreter
            action: wave
        - b: 
            i: doing_dandy
            action: wave_back            
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
            i: hello_bot
            interpreter: hello_interpreter2
            action: register_hello
        - b: 
            i: hello_user
            action: register_hello
    scene2:
      intents:
        - u: 
            i: how_are_you
            interpreter: how_are_you_interpreter
            action: wave
        - b: 
            i: doing_dandy
            action: wave_back            
EOT;
    }
}
