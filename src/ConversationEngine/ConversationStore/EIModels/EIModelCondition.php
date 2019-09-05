<?php


namespace OpenDialogAi\ConversationEngine\ConversationStore\EIModels;


use OpenDialogAi\ContextEngine\Facades\AttributeResolver as AttributeResolverFacade;
use OpenDialogAi\Core\Attribute\AttributeInterface;
use OpenDialogAi\Core\Conversation\Model;

class EIModelCondition extends EIModelBase
{
    private $id;
    private $uid;
    private $context;
    private $attributeName;
    private $attributeValue;
    private $operation;
    private $attribute;

    /**
     * This method should indicate whether the given response is valid for this EI Model. If it isn't then the `handle`
     * method will not be run.
     * @param array $response
     * @param null $additionalParameter
     * @return bool
     */
    public static function validate(array $response, $additionalParameter = null): bool
    {
        return key_exists(Model::ID, $response);
    }

    /**
     * This method takes the response and uses it to set up the EI model's attributes.
     * @param array $response
     * @param null $additionalParameter
     * @return EIModel
     */
    public static function handle(array $response, $additionalParameter = null): EIModel
    {
        $attributeName = $response[Model::ATTRIBUTE_NAME];

        if (array_key_exists($attributeName, AttributeResolverFacade::getSupportedAttributes())) {
            $attributeValue = $response[Model::ATTRIBUTE_VALUE] === ''
                ? null
                : $response[Model::ATTRIBUTE_VALUE];

            $condition = new self();

            $condition->setUid($response[Model::UID]);
            $condition->setId($response[Model::ID]);
            $condition->setContext($response[Model::CONTEXT]);
            $condition->setOperation($response[Model::OPERATION]);

            $attribute = AttributeResolverFacade::getAttributeFor($attributeName, $attributeValue);
            $condition->setAttribute($attribute);

            return $condition;
        }

        return null;
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
     * @param mixed $context
     */
    public function setContext($context): void
    {
        $this->context = $context;
    }

    /**
     * @return mixed
     */
    public function getAttributeName()
    {
        return $this->attributeName;
    }

    /**
     * @return mixed
     */
    public function getAttributeValue()
    {
        return $this->attributeValue;
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
     * @return AttributeInterface|null
     */
    public function getAttribute(): ?AttributeInterface
    {
        return $this->attribute;
    }

    /**
     * @param AttributeInterface $attribute
     */
    public function setAttribute(AttributeInterface $attribute): void
    {
        $this->attribute = $attribute;
    }
}
