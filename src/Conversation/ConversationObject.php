<?php

namespace OpenDialogAi\Core\Conversation;

use DateTime;

class ConversationObject
{
    public const UNDEFINED = 'undefined';

    public const UID = 'uid';
    public const OD_ID = 'odId';
    public const NAME = 'name';
    public const DESCRIPTION = 'description';
    public const INTERPRETER = 'interpreter';
    public const CREATED_AT = 'createdAt';
    public const UPDATED_AT = 'updatedAt';
    public const CONDITIONS = 'conditions';
    public const BEHAVIORS = 'behaviors';

    protected string $odId;
    protected string $uid;
    protected string $name;
    protected ?string $description = null;
    protected ConditionCollection $conditions;
    protected BehaviorsCollection $behaviors;
    protected ?string $interpreter = null;
    protected DateTime $createdAt;
    protected DateTime $updatedAt;


    public static function localFields() {
        return [
            self::UID,
            self::OD_ID,
            self::NAME,
            self::DESCRIPTION,
            self::INTERPRETER,
            self::CREATED_AT,
            self::UPDATED_AT,
            self::CONDITIONS => Condition::FIELDS,
            self::BEHAVIORS => Behavior::FIELDS
        ];
    }

    public function __construct(string $uid, string $odId, string $name, ?string $description, ConditionCollection $conditions,
        BehaviorsCollection  $behaviors, ?string $interpreter, DateTime $createdAt, DateTime  $updatedAt)
    {
        $this->uid = $uid;
        $this->odId = $odId;
        $this->name = $name;
        $this->description = $description;
        $this->conditions = $conditions;
        $this->behaviors = $behaviors;
        $this->interpreter = $interpreter;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    /**
     * The id of the object. Not all objects have an id. An id would be something like 'action.core.Transform'
     *
     * @return string
     */
    public function getOdId(): string
    {
        return $this->odId;
    }

    /**
     * @param  string  $odId
     */
    public function setOdId(string $odId): void
    {
        $this->odId = $odId;
    }

    /**
     * The uid of an object is the unique id with which it is stored in persistence storage.
     *
     * @return string
     */
    public function getUid(): ?string
    {
        return $this->uid;
    }

    /**
     * @param  string  $uid
     */
    public function setUid(string $uid): void
    {
        $this->uid = $uid;
    }

    /**
     * The human-friendly name of the object.
     *
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param  string  $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * The human-friendly description of an object.
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param  string  $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function setInterpreter(string $interpreter)
    {
        $this->interpreter = $interpreter;
    }

    /**
     * Indicates whether an object has conditions - not all objects do.
     *
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
     *
     * @return ConditionCollection
     */
    public function getConditions(): ConditionCollection
    {
        return $this->conditions;
    }

    /**
     * Indicates whether an object is associates with behaviors.
     *
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
     *
     * @return array
     */
    public function getBehaviors(): BehaviorsCollection
    {
        return $this->behaviors;
    }

    /**
     * Replaces all behaviors with a new set of behaviors
     *
     * @param  BehaviorsCollection  $behaviors
     */
    public function setBehaviors(BehaviorsCollection $behaviors)
    {
        $this->behaviors = $behaviors;
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
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param $value
     */
    public function setStatus($value)
    {
        $this->status = $value;
    }

    /**
     * @return DateTime
     */
    public function getUpdatedAt() {
        return $this->updatedAt;
    }

    /**
     * @param  DateTime  $value
     */
    public function setUpdatedAt(DateTime $value) {
        $this->updatedAt = $value;
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt() {
        return $this->createdAt;
    }

    /**
     * @param  DateTime  $value
     */
    public function setCreatedAt(DateTime $value) {
        $this->createdAt = $value;
    }
}
