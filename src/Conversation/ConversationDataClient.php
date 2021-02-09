<?php


namespace OpenDialogAi\Core\Conversation;

use Illuminate\Support\Facades\Http;

/**
 * Draft Conversation Client
 * @package OpenDialogAi\Core\Conversation
 */
class ConversationDataClient
{
    protected string $url;
    protected string $port;
    protected string $api;

    public function __construct($url, $port, $api)
    {
        $this->url = $url;
        $this->port = $port;
        $this->api = $api;
    }

    public function exampleGQLQuery()
    {
        return $array =  [
            "query" => "
query Scenarios {
  queryScenario {
   name
   conversations {
     name
   }
 }
}",
        ];

    }

    public function query()
    {
        $response = Http::baseUrl($this->url)
            ->post('graphql',
                $this->exampleGQLQuery());

        return($response->json());
    }

    public function tempSetDummyData($scenario)
    {

    }

    public function getDummyData($scenario)
    {

    }
}
