<?php


namespace OpenDialogAi\Core\Components\Contracts;

interface OpenDialogComponent
{
    /**
     * Returns an object containing relevant information about the component.
     *
     * @return OpenDialogComponentData
     */
    public static function getComponentData(): OpenDialogComponentData;

    /**
     * The human-readable name of the OpenDialog component.
     *
     * @return string|null
     */
    public static function getComponentName(): ?string;

    /**
     * The human-readable description of the OpenDialog component.
     */
    public static function getComponentDescription(): ?string;

    /**
     * The type of the OpenDialog component (eg. 'action', 'interpreter', etc).
     */
    public static function getComponentType(): string;

    /**
     * The source of the OpenDialog component which records where the component was registered (eg. 'core', 'app', 'custom').
     * For now this is a static method meaning the source is predetermined prior to run-time.
     * TODO: In the future this could be an instance method but it would require all components to be instantiated at
     *  registration which does not currently consistently happen across all component types.
     */
    public static function getComponentSource(): string;

    /**
     * The ID of the OpenDialog component. Depending on the component type this may be a namespaced component ID (such as
     * 'interpreter.core.callback' interpreter) or a regular ID (such as 'eq' operation)
     */
    public static function getComponentId(): string;
}
