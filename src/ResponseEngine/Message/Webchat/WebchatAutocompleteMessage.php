<?php

namespace OpenDialogAi\ResponseEngine\Message\Webchat;

use OpenDialogAi\ResponseEngine\Message\AutocompleteMessage;

class WebchatAutocompleteMessage extends WebchatMessage implements AutocompleteMessage
{
    protected $messageType = self::TYPE;

    private $title;

    private $endpointUrl;

    private $endpointParams;

    private $queryParamName;

    private $callback;

    private $submitText;

    /**
     * @param $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @param $endpointUrl
     * @return $this
     */
    public function setEndpointUrl($endpointUrl)
    {
        $this->endpointUrl = $endpointUrl;
        return $this;
    }

    /**
     * @param $endpointParams
     * @return $this
     */
    public function setEndpointParams($endpointParams)
    {
        $this->endpointParams = $endpointParams;
        return $this;
    }

    /**
     * @param $queryParamName
     * @return $this
     */
    public function setQueryParamName($queryParamName)
    {
        $this->queryParamName = $queryParamName;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getEndpointUrl()
    {
        return $this->endpointUrl;
    }

    /**
     * @return array
     */
    public function getEndpointParams()
    {
        return $this->endpointParams;
    }

    /**
     * @return string
     */
    public function getQueryParamName()
    {
        return $this->queryParamName;
    }

    /**
     * @return string
     */
    public function getMessageType(): string
    {
        return $this->messageType;
    }

    /**
     * @param string $messageType
     * @return WebchatAutocompleteMessage
     */
    public function setMessageType(string $messageType): WebchatAutocompleteMessage
    {
        $this->messageType = $messageType;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * @param mixed $callback
     * @return WebchatAutocompleteMessage
     */
    public function setCallback($callback)
    {
        $this->callback = $callback;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSubmitText()
    {
        return $this->submitText;
    }

    /**
     * @param mixed $submitText
     * @return WebchatAutocompleteMessage
     */
    public function setSubmitText($submitText)
    {
        $this->submitText = $submitText;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getData(): ?array
    {
        return parent::getData() + [
            'title' => $this->getTitle(),
            'endpoint_url' => $this->getEndpointUrl(),
            'endpoint_params' => $this->getEndpointParams(),
            'query_param_name' => $this->getQueryParamName(),
            'callback' => $this->getCallback(),
            'submit_text' => $this->getSubmitText(),
        ];
    }
}
