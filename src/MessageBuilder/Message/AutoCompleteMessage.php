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

    public $placeholder;

    public $attributeName;

    /**
     * AutoCompleteMessage constructor.
     * @param $title
     * @param $endpointUrl
     * @param $queryParamName
     * @param $callback
     * @param $submitText
     * @param $placeholder
     * @param $attributeName
     * @param array $endpointParams
     */
    public function __construct(
        $title,
        $endpointUrl,
        $queryParamName,
        $callback,
        $submitText,
        $placeholder,
        $attributeName,
        $endpointParams = []
    ) {
        $this->title = $title;
        $this->endpointUrl =$endpointUrl;
        $this->queryParamName= $queryParamName;
        $this->callback = $callback;
        $this->submitText = $submitText;
        $this->placeholder = $placeholder;
        $this->attributeName = $attributeName;
        $this->endpointParams = $endpointParams;
    }

    public function getMarkUp()
    {
        return <<<EOT
<autocomplete-message>
    <title>$this->title</title>
    <callback>$this->callback</callback>
    <submit_text>$this->submitText</submit_text>
    <placeholder>$this->placeholder</placeholder>
    <attribute_name>$this->attributeName</attribute_name>
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
