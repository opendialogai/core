<?php

namespace OpenDialogAi\InterpreterEngine\QnA;

class QnAPrompt
{
    private $displayOrder;

    private $qnaId;

    private $displayText;

    /**
     * @param $displayOrder
     * @param $qnaId
     * @param $displayText
     */
    public function __construct($displayOrder, $qnaId, $displayText)
    {
        $this->displayOrder = $displayOrder;
        $this->qnaId = $qnaId;
        $this->displayText = $displayText;
    }

    public function getDisplayOrder()
    {
        return $this->displayOrder;
    }

    public function getQnaId()
    {
        return $this->qnaId;
    }

    public function getDisplayText()
    {
        return $this->displayText;
    }
}
