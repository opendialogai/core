<?php

namespace OpenDialogAi\MessageBuilder\Message;

class AutoCompleteMessage
{
    public $title;
    public $endpointUrl;
    public $endpointParams = array();
    public $queryParamName;


    /**
     * AutoCompleteMessage constructor.
     * @param $title
     * @param $endpointUrl
     * @param $endpointParams
     */
    public function __construct($title, $endpointUrl, $queryParamName, $endpointParams = array())
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
            {$this->getParamsString()}
        </params>
        <query-param-name>$this->queryParamName<query-param-name>
    </options-endpoint>
</autocomplete-message>
EOT;
    }

    /**
     * @return string
     */
    public function getParamsString()
    {
        $params_string = "";
        foreach ($this->endpointParams as $key => $value) {
            $params_string .= '<param name="' . $key . '" value="' . $value . '"/>\n';
        }
        return $params_string;
    }
}
