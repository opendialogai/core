<?php
namespace OpenDialogAi\Core\Conversation;

class ConversationObject
{
    public const UNDEFINED = 'undefined';

    protected string $type = self::UNDEFINED;
    protected string $od_id = self::UNDEFINED;
    protected string $uid = self::UNDEFINED;
    protected string $name = self::UNDEFINED;
    protected string $description = self::UNDEFINED;

    public const DRAFT_STATUS = "DRAFT";
    public const PREVIEW_STATUS = "PREVIEW";
    public const LIVE_STATUS = "LIVE";

    protected ConditionCollection $conditions;
    protected BehaviorsCollection $behaviors;
    protected bool $active;
    protected string $status;

    public function __construct()
    {
        $this->conditions = new ConditionCollection();
        $this->behaviors = new BehaviorsCollection();
    }

    /**
     * The type of conversation object we are dealing with (one of Scenario, Conversation, Scene, etc)
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        // @todo Check that it is one of the permitted types.
        $this->type = $type;
    }

    /**
     * The id of the object. Not all objects have an id. An id would be something like 'action.core.Transform'
     * @return string
     */
    public function getODId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setODId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * The uid of an object is the unique id with which it is stored in persistence storage.
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
     * The human-friendly name of the object.
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * The human-friendly description of an object.
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * Indicates whether an object has conditions - not all objects do.
     * @return bool
     */
    public function hasConditions(): bool
    {
        if ($this->conditions->isEmpty()) {
            return false;
        }

        return true;
    }

    /**
     * Retrieves a collection of objects
     * @return ConditionCollection
     */
    public function getConditions(): ConditionCollection
    {
        return $this->conditions;
    }

    /**
     * Indicates whether an object is associates with behaviors.
     * @return bool
     */
    public function hasBehaviors(): bool
    {
        if ($this->behaviors->isEmpty()) {
            return false;
        }

        return true;
    }

    /**
     * Retrieves all behavior directives as an array.
     * @return array
     */
    public function getBehaviors(): BehaviorsCollection
    {
        return $this->behaviors;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @return bool
     */
    public function activate(): bool
    {
        $this->active = true;
        return $this->active;
    }

    /**
     * @return bool
     */
    public function deactivate(): bool
    {
        $this->active = false;
        return $this->active;
    }

    /**
     * @param $value
     */
    public function setStatus($value)
    {
        $this->status = $value;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }
}
