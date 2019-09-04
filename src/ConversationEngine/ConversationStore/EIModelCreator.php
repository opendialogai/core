<?php


namespace OpenDialogAi\ConversationEngine\ConversationStore;


use Exception;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModels\EIModel;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModels\EIModelBase;

class EIModelCreator
{
    /**
     * @param string $eiModelClass
     * @param array $response
     * @param $additionalParameter
     * @return EIModel
     * @throws Exception
     */
    public function createEIModel(string $eiModelClass, array $response, $additionalParameter = null): EIModel
    {
        if (class_exists($eiModelClass)) {
            if (is_subclass_of($eiModelClass, EIModelBase::class)) {
                /* @var EIModelBase $eiModelClass */
                if ($eiModelClass::validate($response, $additionalParameter)) {
                    return $eiModelClass::handle($response, $additionalParameter);
                } else {
                    throw new Exception(sprintf("Query response data is not valid for the given EI Model: '%s'.", $eiModelClass));
                }
            } else {
                throw new Exception(
                    sprintf("Trying to instantiate an EI Model that does not extend the EIModel class: '%s'.", $eiModelClass)
                );
            }
        } else {
            throw new Exception(sprintf("Trying to instantiate an EI Model that does not exist: '%s'.", $eiModelClass));
        }
    }
}
