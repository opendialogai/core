<?php

namespace OpenDialogAi\Core\NlpEngine\Tests;

use OpenDialogAi\Core\NlpEngine\Tests\Providers\BadlyNamedProvider;
use OpenDialogAi\Core\NlpEngine\Tests\Providers\NoMethods;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\NlpEngine\Exceptions\NlpProviderMethodNotSupportedException;
use OpenDialogAi\NlpEngine\Exceptions\NlpProviderNotRegisteredException;
use OpenDialogAi\NlpEngine\Providers\MsNlpProvider;
use OpenDialogAi\NlpEngine\Service\NlpServiceInterface;

class NlpServiceTest extends TestCase
{
    public function testGettingProvider()
    {
        $this->assertNotNull(resolve(NlpServiceInterface::class)->getProvider(MsNlpProvider::getName()));
    }

    public function testGettingUnBoundProvider()
    {
        $this->expectException(NlpProviderNotRegisteredException::class);
        resolve(NlpServiceInterface::class)->getProvider('unbound');
    }

    public function testBindingBadName()
    {
        $this->app['config']->set('opendialog.nlp_engine.available_nlp_providers', [BadlyNamedProvider::class]);
        $this->expectException(NlpProviderNotRegisteredException::class);
        resolve(NlpServiceInterface::class)->getProvider(BadlyNamedProvider::getName());
    }

    public function testMethodNotInUse()
    {
        $this->app['config']->set('opendialog.nlp_engine.available_nlp_providers', [NoMethods::class]);
        $this->expectException(NlpProviderMethodNotSupportedException::class);
        resolve(NlpServiceInterface::class)->getProvider(NoMethods::getName())->getEntities('');
    }
}
