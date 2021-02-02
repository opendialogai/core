<?php


namespace OpenDialogAi\Core\Reflection\Reflections;


use Ds\Map;
use OpenDialogAi\ResponseEngine\Formatters\MessageFormatterInterface;

interface ResponseEngineReflectionInterface
{
    /**
     * @return Map|MessageFormatterInterface[]
     */
    public function getAvailableFormatters(): Map;
}
