<?php

namespace OpenDialogAi\MessageBuilder\Message;

class AutoCompleteMessage
{
    public $title;

    public $endpointUrl;

    public $endpointParams = [];

    public $queryParamName;

    public $callback;

    public $submitText;

    /**
     * AutoCompleteMessage constructor.
     * @param $title
     * @param $endpointUrl
     * @param $queryParamName
     * @param array $endpointParams
     * @param $callback
     * @param $submitText
     */
    public function __construct($title, $endpointUrl, $queryParamName, $callback, $submitText, $endpointParams = [])
    {
        $this->title = $title;
        $this->endpointUrl =$endpointUrl;
        $this->queryParamName= $queryParamName;
        $this->endpointParams = $endpointParams;
        $this->callback = $callback;
        $this->submitText = $submitText;
    }

    public function getMarkUp()
    {
        return <<<EOT
<autocomplete-message>
    <title>$this->title</title>
    <callback>$this->callback</callback>
    <submit_text>$this->submitText</submit_text>
    <options-endpoint>
        <url>$this->endpointUrl</url>
        <params>
            {$this->getParams()}
        </params>
        <query-param-name>$this->queryParamName<query-param-name>
    </options-endpoint>
</autocomplete-message>
EOT;
    }

    /**
     * @return string
     */
    protected function getParams()
    {
        $params = "";
        foreach ($this->endpointParams as $key => $value) {
            $params .= '<param name="' . $key . '" value="' . $value . '"/>\n';
        }
        return $params;
    }
}
