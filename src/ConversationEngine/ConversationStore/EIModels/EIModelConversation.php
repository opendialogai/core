<?php


namespace OpenDialogAi\ConversationEngine\ConversationStore\EIModels;


use Ds\Set;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModelCreator;
use OpenDialogAi\Core\Conversation\Model;

class EIModelConversation extends EIModelBase
{
    private $id;
    private $uid;
    private $eiType;

    /* @var Set $conditions */
    private $conditions;

    /* @var Set $openingScenes */
    private $openingScenes;

    /* @var Set $scenes */
    private $scenes;

    /**
     * This method should indicate whether the given response is valid for this EI Model. If it isn't then the `handle`
     * method will not be run.
     * @param array $response
     * @param null $additionalParameter
     * @return bool
     */
    public static function validate(array $response, $additionalParameter = null): bool
    {
        return EIModelBase::hasEIType($response, Model::CONVERSATION_USER, Model::CONVERSATION_TEMPLATE);
    }

    /**
     * This method takes the response and uses it to set up the EI model's attributes.
     * @param array $response
     * @param null $additionalParameter
     * @return EIModel
     * @throws \Exception
     */
    public static function handle(array $response, $additionalParameter = null): EIModel
    {
        $eiModelCreator = app()->make(EIModelCreator::class);

        $conversation = new self();

        $conversation->setId($response[Model::ID]);
        $conversation->setUid($response[Model::UID]);
        $conversation->setEiType($response[Model::EI_TYPE]);

        if (isset($response[Model::HAS_CONDITION])) {
            $conversation->conditions = new Set();

            foreach ($response[Model::HAS_CONDITION] as $c) {
                $condition = $eiModelCreator->createEIModel(EIModelCondition::class, $c);

                if (isset($condition)) {
                    $conversation->conditions->add($condition);
                }
            }
        }

        if (isset($response[Model::HAS_OPENING_SCENE])) {
            $openingScenes = new Set();

            foreach ($response[Model::HAS_OPENING_SCENE] as $s) {
                $openingScene = $eiModelCreator->createEIModel(EIModelScene::class, $s, $response);
                $openingScenes->add($openingScene);
            }

            $conversation->setOpeningScenes($openingScenes);
        }

        if (isset($response[Model::HAS_SCENE])) {
            $scenes = new Set();

            foreach ($response[Model::HAS_SCENE] as $s) {
                $scene = $eiModelCreator->createEIModel(EIModelScene::class, $s, $response);
                $scenes->add($scene);
            }

            $conversation->setScenes($scenes);
        }

        return $conversation;
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
    public function getEiType()
    {
        return $this->eiType;
    }

    /**
     * @param mixed $eiType
     */
    public function setEiType($eiType): void
    {
        $this->eiType = $eiType;
    }

    /**
     * @return Set|null
     */
    public function getConditions(): ?Set
    {
        return $this->conditions;
    }

    /**
     * @return Set|null
     */
    public function getOpeningScenes(): ?Set
    {
        return $this->openingScenes;
    }

    /**
     * @param Set $openingScenes
     */
    public function setOpeningScenes(Set $openingScenes): void
    {
        $this->openingScenes = $openingScenes;
    }

    /**
     * @return Set|null
     */
    public function getScenes(): ?Set
    {
        return $this->scenes;
    }

    /**
     * @param Set $scenes
     */
    public function setScenes(Set $scenes): void
    {
        $this->scenes = $scenes;
    }
}
