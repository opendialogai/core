<?php

namespace OpenDialogAi\Core\Conversation;

use DateTime;
use OpenDialogAi\Core\Conversation\Exceptions\InsufficientHydrationException;

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

    protected ?string $odId = null;
    protected ?string $uid = null;
    protected ?string $name = null;
    protected ?string $description = null;
    protected ?ConditionCollection $conditions = null;
    protected ?BehaviorsCollection $behaviors = null;
    protected ?string $interpreter = null;
    protected ?DateTime $createdAt = null;
    protected ?DateTime $updatedAt = null;

    public function __construct()
    {
        $this->conditions = new ConditionCollection();
        $this->behaviors = new BehaviorsCollection();
    }

    public static function allFields()
    {
        return [
            self::UID, self::OD_ID, self::NAME, self::DESCRIPTION, self::CONDITIONS, self::BEHAVIORS, self::INTERPRETER,
            self::CREATED_AT, self::UPDATED_AT,
        ];
    }

    /**
     * The id of the object.
     * A null value indicates 'not hydrated'
     * A '' value indicates 'none'
     * Any other value should be an object id E.g 'welcome_conversation'
     *
     * @return string
     */
    public function getOdId(): string
    {
        if($this->odId === null) {
            throw new InsufficientHydrationException("Cannot getOdId(). Value is not set!");
        }
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
     * A null value indicates 'not hydrated'
     * Any other value indicates a uid (E.g 0x0001)
     *
     * @return string
     */
    public function getUid(): ?string
    {
        if($this->uid === null) {
            throw new InsufficientHydrationException("Cannot getUid(). Value is not set!");
        }
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
     * A null value indicates 'not hydrated'
     * Any other value indicates a set name.
     *
     * @return string
     */
    public function getName(): string
    {
        if($this->name === null) {
            throw new InsufficientHydrationException("Cannot getName(). Value is not set!");
        }
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
     * A null value indicates 'not hydrated'
     * A '' value indicates empty
     * Any other value indicates a set description.
     *
     * @return string
     */
    public function getDescription(): string
    {
        if($this->description === null) {
            throw new InsufficientHydrationException("Cannot getDescription(). Value is not set!");
        }
        return $this->description;
    }

    /**
     * @param  string  $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * The interpreter specified by this object.
     * A null value indicates 'not hydrated'
     * A '' value indicates 'none'
     * Any other value is considered interpreter id (E.g `interpreter.core.callback`)
     * @return string
     */
    public function getInterpreter(): string {
        if($this->interpreter === null) {
            throw new InsufficientHydrationException("Cannot getName(). Value is not set!");
        }
        return $this->interpreter;
    }

    public function setInterpreter(string $interpreter): void
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
        if($this->conditions === null) {
            throw new InsufficientHydrationException("Cannot call hasConditions(). Value is not set!");
        }
        if ($this->conditions->isEmpty()) {
            return false;
        }

        return true;
    }

    /**
     * Retrieves a collection of objects
     * A null value indicates 'not hydrated'
     * Any other value indicates a collection of conditions.
     *
     * @return ConditionCollection
     */
    public function getConditions(): ?ConditionCollection
    {
        if($this->conditions === null) {
            throw new InsufficientHydrationException("Cannot getConditions(). Value is not set!");
        }
        return $this->conditions;
    }

    /**
     * @param ConditionCollection $conditions
     * @return void
     */
    public function setConditions(ConditionCollection $conditions): void
    {
        $this->conditions = $conditions;
    }

    /**
     * Indicates whether an object is associates with behaviors.
     *
     * @return bool
     */
    public function hasBehaviors(): bool
    {
        if($this->behaviors === null) {
            throw new InsufficientHydrationException("Cannot hasBehaviors(). Value is not set!");
        }
        if ($this->behaviors->isEmpty()) {
            return false;
        }

        return true;
    }

    /**
     * Retrieves all behavior directives as an array.
     * @return BehaviorsCollection
     */
    public function getBehaviors(): BehaviorsCollection
    {
        if($this->behaviors === null) {
            throw new InsufficientHydrationException("Cannot getBehaviors(). Value is not set!");
        }
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
     * @return DateTime
     */
    public function getUpdatedAt() : DateTime
    {
        if($this->updatedAt === null) {
            throw new InsufficientHydrationException("Cannot getUpdatedAt(). Value is not set!");
        }
        return $this->updatedAt;
    }

    /**
     * @param  DateTime  $value
     */
    public function setUpdatedAt(DateTime $value)
    {
        $this->updatedAt = $value;
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt(): DateTime
    {
        if($this->createdAt === null) {
            throw new InsufficientHydrationException("Cannot getCreatedAt(). Value is not set!");
        }
        return $this->createdAt;
    }

    /**
     * @param  DateTime  $value
     */
    public function setCreatedAt(DateTime $value)
    {
        $this->createdAt = $value;
    }

    /**
     * Returns array containing the names of all hydrated (non-null) fields.
     * @return array
     */
    public function hydratedFields(): array {
        return array_filter(static::allFields(), fn($field) => $this->$field !== null);
    }
}
