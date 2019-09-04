<?php


namespace OpenDialogAi\ConversationEngine\ConversationStore\EIModels;


use OpenDialogAi\Core\Conversation\Model;

abstract class EIModelBase implements EIModel
{
    /**
     * This method should indicate whether the given response is valid for this EI Model. If it isn't then the `handle`
     * method will not be run.
     * @param array $response
     * @param $additionalParameter
     * @return bool
     */
    public abstract static function validate(array $response, $additionalParameter = null): bool;

    /**
     * This method takes the response and uses it to set up the EI model's attributes.
     * @param array $response
     * @param $additionalParameter
     * @return EIModel
     */
    public abstract static function handle(array $response, $additionalParameter = null): EIModel;

    /**
     * This method returns true if the response has no data or if the data is has is of the desired EI type
     * @param array $response
     * @param string ...$eiTypes
     * @return bool
     */
    public static function hasEIType(array $response, string ...$eiTypes): bool
    {
        if (!is_null($response) && count($response) > 0) {
            return key_exists(Model::EI_TYPE, $response) && in_array($response[Model::EI_TYPE], $eiTypes);
        } else {
            return true;
        }
    }
}
