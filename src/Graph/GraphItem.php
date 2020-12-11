<?php

namespace OpenDialogAi\Core\Graph;

/**
 * A GraphItem is a node or an edge belonging to a Graph.
 */
trait GraphItem
{
    /* @var string $uid - a unique identifier for the item */
    protected $uid;

    /* @var string $id - a human friendly identifier for the item */
    protected $id;

    /** @var ?string $graph_type - the node type used by the graph */
    protected $graph_type = null;

    /**
     * @return string
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * @param string $uid
     * @return GraphItem
     */
    public function setUid($uid)
    {
        $this->uid = $uid;
        return $this;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return GraphItem
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return bool
     */
    public function idIsSet()
    {
        return isset($this->id);
    }

    /**
     * @return bool
     */
    public function uidIsSet()
    {
        return isset($this->uid);
    }

    /**
     * @return ?string
     */
    public function getGraphType(): ?string
    {
        return $this->graph_type;
    }

    /**
     * @param ?string $graph_type
     */
    public function setGraphType(?string $graph_type): void
    {
        $this->graph_type = $graph_type;
    }

    /**
     * @return bool
     */
    public function hasGraphType(): bool
    {
        return !is_null($this->graph_type);
    }
}
