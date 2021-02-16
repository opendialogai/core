<?php

namespace OpenDialogAi\ResponseEngine\Formatters;

use OpenDialogAi\Core\Components\Contracts\OpenDialogComponent;
use OpenDialogAi\Core\Components\ODComponent;
use OpenDialogAi\Core\Components\ODComponentTypes;

abstract class BaseMessageFormatter implements MessageFormatterInterface, OpenDialogComponent
{
    use ODComponent;

    protected static string $componentType = ODComponentTypes::FORMATTER_COMPONENT_TYPE;
    protected static string $componentSource = ODComponentTypes::APP_COMPONENT_SOURCE;
}
