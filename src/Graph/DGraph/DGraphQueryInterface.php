<?php

namespace OpenDialogAi\Core\Graph\DGraph;

interface DGraphQueryInterface
{
    /**
     * Returns a string containing the query in DGraph syntax
     * @return string
     */
    public function prepare(): string;
}
