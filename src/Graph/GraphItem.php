<?php


namespace OpenDialogAi\Core\Graph;

/**
 * Trait GraphItem
 * @package OpenDialog\Core\Graph
 *
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
        // If there is no UID generate a random one
        if (!isset($this->uid)) {
            return substr(
                str_shuffle('abcdefghijklmnopqrstuvwxz0987654321'),
                0,
                7
            );
        }
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
        // When we don't have a human readable ID we return the UID as a fallback.
        if (!isset($this->id)) {
            return $this->getUid();
        }
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
}
