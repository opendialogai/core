<?php

namespace OpenDialogAi\MessageBuilder\Message;

class AutoCompleteMessage
{
    public $title;

    public $endpointUrl;

    public $endpointParams = [];

    public $queryParamName;

    /**
     * AutoCompleteMessage constructor.
     * @param $title
     * @param $endpointUrl
     * @param $endpointParams
     */
    public function __construct($title, $endpointUrl, $queryParamName, $endpointParams = [])
    {
        $this->title = $title;
        $this->endpointUrl =$endpointUrl;
        $this->queryParamName= $queryParamName;
        $this->endpointParams = $endpointParams;
    }

    public function getMarkUp()
    {
        return <<<EOT
<autocomplete-message>
    <title>$this->title</title>
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
