<?php
namespace OpenDialogAi\Core\Conversation\Contracts;

interface ConversationObject
{
    /**
     * The type of conversation object we are dealing with (one of Scenario, Conversation, Scene, etc)
     * @return string
     */
    public function getType(): string;

    /**
     * @param string $type
     */
    public function setType(string $type): void;

    /**
     * The id of the object. Not all objects have an id.
     * @return string
     */
    public function getId(): string;

    /**
     * @param string $id
     */
    public function setId(string $id): void;

    /**
     * The uid of an object is the unique id with which it is stored in persistence storage.
     * @return string
     */
    public function getUid(): string;

    /**
     * @param string $uid
     */
    public function setUid(string $uid): void;

    /**
     * The human-friendly name of the object.
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $name
     */
    public function setName(string $name): void;

    /**
     * The human-friendly description of an object.
     * @return string
     */
    public function getDescription(): string;

    /**
     * @param string $description
     */
    public function setDescription(string $description): void;

    /**
     * Indicates whether an object has conditions - not all objects do.
     * @return bool
     */
    public function hasConditions(): bool;

    /**
     * Retrieves a collection of objects
     * @return ObjectCollection
     */
    public function getConditions(): ObjectCollection;

    /**
     * Indicates whether an object is associates with behaviors.
     * @return bool
     */
    public function hasBehaviors(): bool;

    /**
     * Retrieves all behavior directives as an array.
     * @return array
     */
    public function getBehaviors(): array;
}
