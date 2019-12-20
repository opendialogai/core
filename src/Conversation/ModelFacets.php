<?php

namespace OpenDialogAi\Core\Conversation;

/**
 * All the facets used in a conversation graph.
 */
class ModelFacets
{
    // General
    const CREATED_AT = 'created_at';
    const COUNT = 'count';

    /**
     * @param string $relationship
     * @param string $facetName
     * @return string
     */
    public static function facet(string $relationship, string $facetName): string
    {
        return sprintf('%s|%s', $relationship, $facetName);
    }
}
