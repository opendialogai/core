<?php


namespace OpenDialogAi\Core\NlpEngine\Service;


use OpenDialogAi\Core\NlpEngine\Exceptions\NlpProviderNotRegisteredException;
use OpenDialogAi\NlpEngine\Providers\NlpProviderInterface;

/**
 * Deals with registering and exposing registered NLP providers
 */
interface NlpServiceInterface
{
    /**
     * @param NlpProviderInterface[] $providers
     */
    public function registerAvailableProviders(array $providers);

    /**
     * @param $providerName
     * @return bool
     */
    public function isProviderAvailable($providerName): bool;

    /**
     * @return NlpProviderInterface[]
     */
    public function getAvailableProviders(): array;

    /**
     * @param $providerName
     * @return NlpProviderInterface
     * @throws NlpProviderNotRegisteredException
     */
    public function getProvider($providerName): NlpProviderInterface;
}
