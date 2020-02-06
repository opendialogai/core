<?php


namespace OpenDialogAi\NlpEngine\Service;


use Illuminate\Support\Facades\Log;
use OpenDialogAi\Core\Exceptions\NameNotSetException;
use OpenDialogAi\NlpEngine\Exceptions\NlpProviderNotRegisteredException;
use OpenDialogAi\NlpEngine\Providers\NlpProviderInterface;

class NlpService implements NlpServiceInterface
{
    private $validNamePattern = "/^nlp_provider\.[A-Za-z]*\.[A-Za-z_]*$/";

    /**
     * @var NlpProviderInterface[]
     */
    private $availableProviders = [];

    /**
     * @inheritDoc
     */
    public function registerAvailableProviders(array $providers)
    {
        /** @var NlpProviderInterface $provider */
        foreach ($providers as $provider) {
            try {
                $name = $provider::getName();

                if ($this->isValidName($name)) {
                    $this->availableProviders[$name] = new $provider();
                } else {
                    Log::warning(
                        sprintf("Not adding NLP provider with name %s. Name is in wrong format", $name)
                    );
                }
            } catch (NameNotSetException $e) {
                Log::warning(
                    sprintf("Not adding NLP provider %s. It has not defined a name", $provider)
                );
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function isProviderAvailable($providerName): bool
    {
        if (in_array($providerName, array_keys($this->getAvailableProviders()))) {
            Log::debug(sprintf("NLP provider with name %s is available", $providerName));
            return true;
        }

        Log::debug(sprintf("NLP provider with name %s is not available", $providerName));
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getAvailableProviders(): array
    {
        return $this->availableProviders;
    }

    /**
     * @inheritDoc
     */
    public function getProvider($providerName): NlpProviderInterface
    {
        if ($this->isProviderAvailable($providerName)) {
            Log::debug(sprintf("Getting NLP provider with name %s", $providerName));
            return $this->availableProviders[$providerName];
        }

        throw new NlpProviderNotRegisteredException("NLP provider with name $providerName is not available");
    }

    /**
     * Checks if the name of the provider is in the right format
     *
     * @param string $name
     * @return bool
     */
    private function isValidName(string $name): bool
    {
        return preg_match($this->validNamePattern, $name) === 1;
    }
}
