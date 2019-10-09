<?php

namespace OpenDialogAi\ResponseEngine\Message;

interface ListMessage extends OpenDialogMessage
{
    const TYPE = 'list';

    /**
     * @param OpenDialogMessage $message
     * @return $this
     */
    public function addItem(OpenDialogMessage $message);

    /**
     * @param array $message
     * @return $this
     */
    public function addItems(array $messages);

    /**
     * @return array
     */
    public function getItems();

    /**
     * @param $viewType
     * @return $this
     */
    public function setViewType($viewType);

    /**
     * @return string
     */
    public function getViewType();

    /**
     * {@inheritDoc}
     */
    public function getData(): ?array;

    /**
     * @return array
     */
    public function getItemsArray();
}
