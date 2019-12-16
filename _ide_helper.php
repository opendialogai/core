<?php
// @formatter:off

/**
 * A helper file for Laravel 5, to provide autocomplete information to your IDE
 * Generated for Laravel 5.8.15 on 2019-07-08 18:06:33.
 *
 * This file should not be included in your code, only analyzed by your IDE!
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 * @see https://github.com/barryvdh/laravel-ide-helper
 */

namespace Illuminate\Support\Facades {

    /**
     *
     *
     * @see \Illuminate\Contracts\Console\Kernel
     */
    class Artisan
    {
        
        /**
         * Run the console application.
         *
         * @param \Symfony\Component\Console\Input\InputInterface $input
         * @param \Symfony\Component\Console\Output\OutputInterface $output
         * @return int
         * @static
         */
        public static function handle($input, $output = null)
        {
            //Method inherited from \Illuminate\Foundation\Console\Kernel
            /** @var \Matthewbdaly\ArtisanStandalone\Console\Kernel $instance */
            return $instance->handle($input, $output);
        }
        
        /**
         * Terminate the application.
         *
         * @param \Symfony\Component\Console\Input\InputInterface $input
         * @param int $status
         * @return void
         * @static
         */
        public static function terminate($input, $status)
        {
            //Method inherited from \Illuminate\Foundation\Console\Kernel
            /** @var \Matthewbdaly\ArtisanStandalone\Console\Kernel $instance */
            $instance->terminate($input, $status);
        }
        
        /**
         * Register a Closure based command with the application.
         *
         * @param string $signature
         * @param \Closure $callback
         * @return \Illuminate\Foundation\Console\ClosureCommand
         * @static
         */
        public static function command($signature, $callback)
        {
            //Method inherited from \Illuminate\Foundation\Console\Kernel
            /** @var \Matthewbdaly\ArtisanStandalone\Console\Kernel $instance */
            return $instance->command($signature, $callback);
        }
        
        /**
         * Register the given command with the console application.
         *
         * @param \Symfony\Component\Console\Command\Command $command
         * @return void
         * @static
         */
        public static function registerCommand($command)
        {
            //Method inherited from \Illuminate\Foundation\Console\Kernel
            /** @var \Matthewbdaly\ArtisanStandalone\Console\Kernel $instance */
            $instance->registerCommand($command);
        }
        
        /**
         * Run an Artisan console command by name.
         *
         * @param string $command
         * @param array $parameters
         * @param \Symfony\Component\Console\Output\OutputInterface $outputBuffer
         * @return int
         * @throws \Symfony\Component\Console\Exception\CommandNotFoundException
         * @static
         */
        public static function call($command, $parameters = array(), $outputBuffer = null)
        {
            //Method inherited from \Illuminate\Foundation\Console\Kernel
            /** @var \Matthewbdaly\ArtisanStandalone\Console\Kernel $instance */
            return $instance->call($command, $parameters, $outputBuffer);
        }
        
        /**
         * Queue the given console command.
         *
         * @param string $command
         * @param array $parameters
         * @return \Illuminate\Foundation\Bus\PendingDispatch
         * @static
         */
        public static function queue($command, $parameters = array())
        {
            //Method inherited from \Illuminate\Foundation\Console\Kernel
            /** @var \Matthewbdaly\ArtisanStandalone\Console\Kernel $instance */
            return $instance->queue($command, $parameters);
        }
        
        /**
         * Get all of the commands registered with the console.
         *
         * @return array
         * @static
         */
        public static function all()
        {
            //Method inherited from \Illuminate\Foundation\Console\Kernel
            /** @var \Matthewbdaly\ArtisanStandalone\Console\Kernel $instance */
            return $instance->all();
        }
        
        /**
         * Get the output for the last run command.
         *
         * @return string
         * @static
         */
        public static function output()
        {
            //Method inherited from \Illuminate\Foundation\Console\Kernel
            /** @var \Matthewbdaly\ArtisanStandalone\Console\Kernel $instance */
            return $instance->output();
        }
        
        /**
         * Bootstrap the application for artisan commands.
         *
         * @return void
         * @static
         */
        public static function bootstrap()
        {
            //Method inherited from \Illuminate\Foundation\Console\Kernel
            /** @var \Matthewbdaly\ArtisanStandalone\Console\Kernel $instance */
            $instance->bootstrap();
        }
        
        /**
         * Set the Artisan application instance.
         *
         * @param \Illuminate\Console\Application $artisan
         * @return void
         * @static
         */
        public static function setArtisan($artisan)
        {
            //Method inherited from \Illuminate\Foundation\Console\Kernel
            /** @var \Matthewbdaly\ArtisanStandalone\Console\Kernel $instance */
            $instance->setArtisan($artisan);
        }
    }
 
}


namespace  {

    class Artisan extends \Illuminate\Support\Facades\Artisan
    {
    }
 
}
