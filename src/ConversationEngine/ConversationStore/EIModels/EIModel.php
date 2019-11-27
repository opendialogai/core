<?php

namespace OpenDialogAi\ConversationEngine\ConversationStore\EIModels;

interface EIModel
{
    /**
     * This method should indicate whether the given response is valid for this EI Model. If it isn't then the `handle`
     * method will not be run.
     * @param array $response
     * @param $additionalParameter
     * @return bool
     */
    public static function validate(array $response, $additionalParameter = null): bool;

    /**
     * This method takes the response and uses it to set up the EI model's attributes.
     * @param array $response
     * @param $additionalParameter
     * @return self
     */
    public static function handle(array $response, $additionalParameter = null): self;
}
