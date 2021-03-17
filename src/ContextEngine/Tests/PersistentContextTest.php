<?php

namespace OpenDialogAi\ContextEngine\Tests;

use Mockery\MockInterface;
use OpenDialogAi\AttributeEngine\AttributeBag\BasicAttributeBag;
use OpenDialogAi\AttributeEngine\Attributes\StringAttribute;
use OpenDialogAi\ContextEngine\DataClients\GraphAttributeDataClient;
use OpenDialogAi\ContextEngine\Exceptions\CouldNotLoadAttributeException;
use OpenDialogAi\ContextEngine\Tests\Contexts\ExamplePersistentContext;
use OpenDialogAi\Core\Tests\TestCase;

class PersistentContextTest extends TestCase
{
    /**
     * @throws CouldNotLoadAttributeException
     */
    public function testPersistentContextLazyLoading()
    {
        /** @var GraphAttributeDataClient $mockedClient */
        $mockedClient = $this->mock(GraphAttributeDataClient::class, function (MockInterface $mock) {
            $mock->shouldReceive('loadAttribute')
                ->twice()
                ->andReturn(
                    new StringAttribute('example', 'original'),
                    new StringAttribute('example', 'updated'),
                );
        });

        $context = new ExamplePersistentContext($mockedClient);
        $context->setUserId('user123');

        // The first time should retrieve and store the attribute
        $first = $context->getAttribute('example');
        $this->assertTrue($context->hasAttribute('example'));
        $this->assertEquals('original', $first->getValue());

        // The second time should only access the in-memory store of the attribute and not re-call the client
        $second = $context->getAttribute('example');
        $this->assertTrue($context->hasAttribute('example'));
        $this->assertEquals('original', $second->getValue());

        // The third time should re-call the client as we asked for a refresh
        $third = $context->getAttribute('example', true);
        $this->assertTrue($context->hasAttribute('example'));
        $this->assertEquals('updated', $third->getValue());
    }

    /**
     * @throws CouldNotLoadAttributeException
     */
    public function testPersistentContextLoading()
    {
        /** @var GraphAttributeDataClient $mockedClient */
        $mockedClient = $this->mock(GraphAttributeDataClient::class, function (MockInterface $mock) {
            $original = new BasicAttributeBag();
            $original->addAttribute(new StringAttribute('hello'));
            $original->addAttribute(new StringAttribute('world'));

            $updated = new BasicAttributeBag();
            $updated->addAttribute(new StringAttribute('updated'));

            $mock->shouldReceive('loadAttributes')
                ->twice()
                ->andReturn(
                    $original,
                    $updated
                );
        });

        $context = new ExamplePersistentContext($mockedClient);
        $context->setUserId('user123');

        // The first time should retrieve and store the attributes
        $first = $context->getAttributes();
        $this->assertCount(2, $first);

        // The second time should only access the in-memory store of the attribute and not re-call the client
        $second = $context->getAttributes();
        $this->assertCount(2, $second);

        // The third time should re-call the client as we asked for a refresh
        $third = $context->getAttributes(true);
        $this->assertCount(1, $third);
    }
}
