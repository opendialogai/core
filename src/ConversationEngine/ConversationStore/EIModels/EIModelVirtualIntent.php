<?php


namespace OpenDialogAi\ConversationEngine\ConversationStore\EIModels;


use OpenDialogAi\Core\Conversation\Model;

class EIModelVirtualIntent extends EIModelBase
{
    private $id;
    private $uid;

    /**
     * This method should indicate whether the given response is valid for this EI Model. If it isn't then the `handle`
     * method will not be run.
     * @param array $response
     * @param $additionalParameter
     * @return bool
     */
    public static function validate(array $response, $additionalParameter = null): bool
    {
        return key_exists(Model::ID, $response) && parent::hasEIType($response, Model::VIRTUAL_INTENT);
    }

    /**
     * This method takes the response and uses it to set up the EI model's attributes.
     * @param array $response
     * @param $additionalParameter
     * @return EIModel
     */
    public static function handle(array $response, $additionalParameter = null): EIModel
    {
        $intent = new self();
        $intent->setId($response[Model::ID]);
        return $intent;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * @param mixed $id
     */
    public function setUid($id): void
    {
        $this->uid = $id;
    }
}
