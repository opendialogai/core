<?php

namespace OpenDialogAi\Core\ResponseEngine\Tests;

use OpenDialogAi\Core\ResponseEngine\Tests\Formatters\TestFormatter;
use OpenDialogAi\Core\ResponseEngine\Tests\Formatters\TestFormatter2;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\ResponseEngine\Service\ResponseEngineService;

class RegisteringFormattersTest extends TestCase
{
    public function testRegisteringSingleSensor()
    {
        /** @var ResponseEngineService $responseEngineService */
        $responseEngineService = $this->app->make(ResponseEngineService::class);
        $this->assertCount(1, $responseEngineService->getAvailableFormatters());

        $testFormatter = new TestFormatter();
        $responseEngineService->registerFormatter($testFormatter);

        $this->assertCount(2, $responseEngineService->getAvailableFormatters());
        $this->assertEquals($testFormatter, $responseEngineService->getFormatter(TestFormatter::getName()));
    }

    public function testRegisteringSingleSensorAlreadyRegistered()
    {
        $this->app['config']->set(
            'opendialog.response_engine.available_formatters',
            [TestFormatter::class]
        );

        /** @var ResponseEngineService $responseEngineService */
        $responseEngineService = $this->app->make(ResponseEngineService::class);

        $this->assertCount(1, $responseEngineService->getAvailableFormatters());

        $testSensor = new TestFormatter2();
        $responseEngineService->registerFormatter($testSensor);

        $this->assertCount(1, $responseEngineService->getAvailableFormatters());
        $this->assertEquals(TestFormatter::class, get_class($responseEngineService->getFormatter(TestFormatter::getName())));
    }

    public function testForcingSingleSensorAlreadyRegistered()
    {
        $this->app['config']->set(
            'opendialog.response_engine.available_formatters',
            [TestFormatter::class]
        );

        /** @var ResponseEngineService $responseEngineService */
        $responseEngineService = $this->app->make(ResponseEngineService::class);

        $this->assertCount(1, $responseEngineService->getAvailableFormatters());

        $responseEngineService->registerFormatter(new TestFormatter2(), true);

        $this->assertCount(1, $responseEngineService->getAvailableFormatters());
        $this->assertEquals(TestFormatter2::class, get_class($responseEngineService->getFormatter(TestFormatter::getName())));
    }
}
