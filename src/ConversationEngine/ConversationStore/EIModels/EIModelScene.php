<?php


namespace OpenDialogAi\ConversationEngine\ConversationStore\EIModels;


use Ds\Set;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModelCreator;
use OpenDialogAi\Core\Conversation\Model;

class EIModelScene extends EIModelBase
{
    private $id;
    private $uid;
    private $userUid;
    private $botUid;

    /* @var Set $userSaysIntents */
    private $userSaysIntents;

    /* @var Set $userSaysAcrossScenesIntents */
    private $userSaysAcrossScenesIntents;

    /* @var Set $botSaysIntents */
    private $botSaysIntents;

    /* @var Set $botSaysAcrossScenesIntents */
    private $botSaysAcrossScenesIntents;

    /**
     * This method should indicate whether the given response is valid for this EI Model. If it isn't then the `handle`
     * method will not be run.
     * @param array $response
     * @param $additionalParameter
     * @return bool
     */
    public static function validate(array $response, $additionalParameter = null): bool
    {
        if (key_exists(Model::ID, $response)) {
            return true;
        } else {
            Log::error('Trying to create scene with no id', $response);
            return false;
        }
    }

    /**
     * This method takes the response and uses it to set up the EI model's attributes.
     * @param array $response
     * @param $additionalParameter
     * @return EIModel
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \Exception
     */
    public static function handle(array $response, $additionalParameter = null): EIModel
    {
        $eiModelCreator = app()->make(EIModelCreator::class);

        $scene = new self();
        $scene->setId($response[Model::ID]);
        $scene->setUid($response[Model::UID]);

        $scene->userSaysIntents = new Set();
        $scene->userSaysAcrossScenesIntents = new Set();
        $scene->botSaysIntents = new Set();
        $scene->botSaysAcrossScenesIntents = new Set();

        if ($response[Model::HAS_USER_PARTICIPANT]) {
            $scene->setUserUid($response[Model::HAS_USER_PARTICIPANT][0][Model::UID]);
            self::handleIntents($response, $additionalParameter, $eiModelCreator, $scene,
                Model::HAS_USER_PARTICIPANT, Model::SAYS);
            self::handleIntents($response, $additionalParameter, $eiModelCreator, $scene,
                Model::HAS_USER_PARTICIPANT, Model::SAYS_ACROSS_SCENES);
        }

        if ($response[Model::HAS_BOT_PARTICIPANT]) {
            $scene->setBotUid($response[Model::HAS_BOT_PARTICIPANT][0][Model::UID]);
            self::handleIntents($response, $additionalParameter, $eiModelCreator, $scene,
                Model::HAS_BOT_PARTICIPANT, Model::SAYS);
            self::handleIntents($response, $additionalParameter, $eiModelCreator, $scene,
                Model::HAS_BOT_PARTICIPANT, Model::SAYS_ACROSS_SCENES);
        }

        return $scene;
    }

    /**
     * @param array $response
     * @param array $conversation
     * @param EIModelCreator $eiModelCreator
     * @param EIModelScene $scene
     * @param string $participant
     * @param string $says
     * @throws \Exception
     */
    private static function handleIntents(
        array $response,
        array $conversation,
        EIModelCreator $eiModelCreator,
        EIModelScene $scene,
        string $participant,
        string $says
    ): void {
        if (isset($response[$participant][0][$says])) {
            foreach ($response[$participant][0][$says] as $intent) {
                /* @var EIModelIntent $intentModel */
                $intentModel = $eiModelCreator->createEIModel(EIModelIntent::class, $conversation, $intent);

                if ($participant == Model::HAS_USER_PARTICIPANT) {
                    if ($says == Model::SAYS_ACROSS_SCENES) {
                        $scene->setUserSaysAcrossScenesIntent($intentModel);
                    } else {
                        $scene->setUserSaysIntent($intentModel);
                    }
                }

                if ($participant == Model::HAS_BOT_PARTICIPANT) {
                    if ($says == Model::SAYS_ACROSS_SCENES) {
                        $scene->setBotSaysAcrossScenesIntent($intentModel);
                    } else {
                        $scene->setBotSaysIntent($intentModel);
                    }
                }
            }
        }
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getUid(): string
    {
        return $this->uid;
    }

    /**
     * @param string $uid
     */
    public function setUid(string $uid): void
    {
        $this->uid = $uid;
    }

    /**
     * @return mixed
     */
    public function getUserUid()
    {
        return $this->userUid;
    }

    /**
     * @param mixed $userUid
     */
    public function setUserUid($userUid): void
    {
        $this->userUid = $userUid;
    }

    /**
     * @return mixed
     */
    public function getBotUid()
    {
        return $this->botUid;
    }

    /**
     * @param mixed $botUid
     */
    public function setBotUid($botUid): void
    {
        $this->botUid = $botUid;
    }

    /**
     * @return Set
     */
    public function getUserSaysIntents(): Set
    {
        return $this->userSaysIntents;
    }

    /**
     * @param EIModelIntent $userSaysIntent
     */
    public function setUserSaysIntent(EIModelIntent $userSaysIntent): void
    {
        $this->userSaysIntents->add($userSaysIntent);
    }

    /**
     * @return Set
     */
    public function getUserSaysAcrossScenesIntents(): Set
    {
        return $this->userSaysAcrossScenesIntents;
    }

    /**
     * @param EIModelIntent $userSaysAcrossScenesIntent
     */
    public function setUserSaysAcrossScenesIntent(EIModelIntent $userSaysAcrossScenesIntent): void
    {
        $this->userSaysAcrossScenesIntents->add($userSaysAcrossScenesIntent);
    }

    /**
     * @return Set
     */
    public function getAllUserIntents(): Set
    {
        return $this->getUserSaysIntents()->merge($this->getUserSaysAcrossScenesIntents())->sorted([EIModelScene::class, 'sort']);
    }

    /**
     * @return Set
     */
    public function getBotSaysIntents(): Set
    {
        return $this->botSaysIntents;
    }

    /**
     * @param EIModelIntent $botSaysIntents
     */
    public function setBotSaysIntent(EIModelIntent $botSaysIntents): void
    {
        $this->botSaysIntents->add($botSaysIntents);
    }

    /**
     * @return Set
     */
    public function getBotSaysAcrossScenesIntents(): Set
    {
        return $this->botSaysAcrossScenesIntents;
    }

    /**
     * @param EIModelIntent $botSaysAcrossScenesIntents
     */
    public function setBotSaysAcrossScenesIntent(EIModelIntent $botSaysAcrossScenesIntents): void
    {
        $this->botSaysAcrossScenesIntents->add($botSaysAcrossScenesIntents);
    }

    /**
     * @return Set
     */
    public function getAllBotIntents(): Set
    {
        return $this->getBotSaysIntents()->merge($this->getBotSaysAcrossScenesIntents())->sorted([EIModelScene::class, 'sort']);
    }

    /**
     * @return Set
     */
    public function getIntents(): Set
    {
        return $this->getAllUserIntents()->merge($this->getAllBotIntents())->sorted([EIModelScene::class, 'sort']);
    }

    /**
     * @param EIModelIntent $a
     * @param EIModelIntent $b
     * @return bool
     */
    public static function sort(EIModelIntent $a, EIModelIntent $b): bool
    {
        return $a->getOrder() > $b->getOrder();
    }
}
