<?php


namespace OpenDialogAi\ConversationEngine\ConversationStore\EIModels;


use OpenDialogAi\Core\Conversation\Model;

class EIModelCondition extends EIModelBase
{
    private $id;
    private $uid;
    private $context;
    private $operation;
    private $attributes;
    private $parameters;

    /**
     * This method should indicate whether the given response is valid for this EI Model. If it isn't then the `handle`
     * method will not be run.
     * @param array $response
     * @param null $additionalParameter
     * @return bool
     */
    public static function validate(array $response, $additionalParameter = null): bool
    {
        return key_exists(Model::ID, $response)
            && key_exists(Model::UID, $response)
            && key_exists(Model::OPERATION, $response);
    }

    /**
     * This method takes the response and uses it to set up the EI model's attributes.
     * @param array $response
     * @param null $additionalParameter
     * @return EIModel
     */
    public static function handle(array $response, $additionalParameter = null): EIModel
    {
        $condition = new self();
        $condition->setUid($response[Model::UID]);
        $condition->setId($response[Model::ID]);
        $condition->setOperation($response[Model::OPERATION]);

        $parameters = [];
        if (isset($response[Model::PARAMETERS])) {
            $parameters = (array) json_decode(htmlspecialchars_decode($response[Model::PARAMETERS]));
        }
        $condition->setParameters($parameters);

        $attributes = [];
        if (isset($response[Model::ATTRIBUTES])) {
            $attributes = (array) json_decode(htmlspecialchars_decode($response[Model::ATTRIBUTES]));
        }
        $condition->setAttributes($attributes);

        return $condition;
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
     * @param mixed $uid
     */
    public function setUid($uid): void
    {
        $this->uid = $uid;
    }

    /**
     * @return mixed
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return string
     */
    public function getContextId() : string
    {
        return $this->getContext();
    }

    /**
     * @param mixed $context
     */
    public function setContext($context): void
    {
        $this->context = $context;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @return mixed
     */
    public function getOperation()
    {
        return $this->operation;
    }

    /**
     * @param mixed $operation
     */
    public function setOperation($operation): void
    {
        $this->operation = $operation;
    }

    /**
     * @param array $attributes
     */
    public function setAttributes(array $attributes): void
    {
        $this->attributes = $attributes;
    }

    /**
     * @param array $parameters
     */
    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }
}
