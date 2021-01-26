<?php

namespace OpenDialogAi\Core\Graph\Edge;

use Ds\Map;
use OpenDialogAi\AttributeEngine\HasAttributesTrait;
use OpenDialogAi\Core\Graph\GraphItem;
use OpenDialogAi\Core\Graph\Node\Node;

class Edge
{
    use GraphItem, HasAttributesTrait;

    /* @var Node $a - a node at one side of the edge */
    protected $a;

    /* @var Node $b - a node at the other side of the edge */
    protected $b;

    /**
     * @var Map
     */
    private $facets;

    /**
     * Edge constructor.
     * @param $id
     * @param Node $a
     * @param Node $b
     * @param array|null $facets
     */
    public function __construct($id, Node $a, Node $b, array $facets = null)
    {
        $this->id = $id;
        $this->a = $a;
        $this->b = $b;

        $this->attributes = new Map();
        $this->facets = new Map();

        if (!is_null($facets)) {
            $this->facets->putAll($facets);
        }
    }

    /**
     * @return bool
     */
    public function hasFacets(): bool
    {
        return !$this->facets->isEmpty();
    }

    /**
     * @return Map
     */
    public function getFacets(): Map
    {
        return $this->facets;
    }

    /**
     * @param $facetName
     * @param $facetValue
     */
    public function addFacet($facetName, $facetValue): void
    {
        $this->facets->put($facetName, $facetValue);
    }
}
