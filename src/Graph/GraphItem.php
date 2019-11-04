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
}
