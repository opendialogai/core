<?php

namespace OpenDialogAi\ResponseEngine\Message\Webchat;

use OpenDialogAi\ResponseEngine\Message\AutocompleteMessage;

class WebchatAutocompleteMessage extends WebchatMessage implements AutocompleteMessage
{
    private $title;

    private $endpointUrl;

    private $endpointParams;

    private $queryParamName;

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
     * {@inheritDoc}
     */
    public function getData(): ?array
    {
        return [
            'title' => $this->getTitle(),
            'endpoint_url' => $this->getEndpointUrl(),
            'endpoint_params' => $this->getEndpointParams(),
            'query_param_name' => $this->getQueryParamName(),
        ];
    }
}
