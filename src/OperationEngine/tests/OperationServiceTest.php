<?php

namespace OpenDialogAi\Core\OperationEngine\OperationEngine\tests;

use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\OperationEngine\OperationInterface;
use OpenDialogAi\OperationEngine\Service\OperationServiceInterface;

class OperationServiceTest extends TestCase
{
    public function testAvailableOperations()
    {
        $operationName = 'dummy';
        $mockOperation = $this->createMockOperation($operationName);
        $this->registerOperation($mockOperation);

        $operationService = $this->getBoundOperationService();

        $operations = $operationService->getAvailableOperations();

        $this->assertCount(1, $operations);
        $this->assertContains($operationName, array_keys($operations));
    }

    public function testGetOperation()
    {
        $operationName = 'dummy';
        $mockOperation = $this->createMockOperation($operationName);
        $this->registerOperation($mockOperation);

        $operationService = $this->getBoundOperationService();

        $this->assertEquals($operationName, $operationService->getOperation($operationName)::getName());
    }

    private function registerOperation($mockOperation): void
    {
        $this->app['config']->set(
            'opendialog.operation_engine.available_operations', [
                get_class($mockOperation)
            ]);
    }

    /**
     * @param $operationName
     * @return \Mockery\MockInterface|OperationInterface
     */
    protected function createMockOperation($operationName)
    {
        $mockOperation = \Mockery::mock(OperationInterface::class);
        $mockOperation->shouldReceive('getName')->andReturn($operationName);

        return $mockOperation;
    }

    /**
     * @return OperationServiceInterface
     */
    private function getBoundOperationService(): OperationServiceInterface
    {
        $operationService = $this->app->make(OperationServiceInterface::class);
        return $operationService;
    }
}
