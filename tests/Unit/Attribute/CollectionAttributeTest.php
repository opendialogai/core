<?php

namespace OpenDialogAi\Core\Tests\Unit\Attribute;

use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\Core\Attribute\ArrayAttribute;
use OpenDialogAi\Core\Attribute\CollectionAttribute;
use OpenDialogAi\Core\Attribute\IntAttribute;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\Core\Tests\Utils\MessageMarkUpGenerator;
use OpenDialogAi\OperationEngine\Service\OperationServiceInterface;
use OpenDialogAi\ResponseEngine\MessageTemplate;
use OpenDialogAi\ResponseEngine\OutgoingIntent;
use OpenDialogAi\ResponseEngine\Service\ResponseEngineService;

class CollectionAttributeTest extends TestCase
{
    public $setupWithDGraphInit = false;

    public function testCollectionNonArrayValue()
    {
        $value = "string";

        $collection = new CollectionAttribute('test', $value);

        $this->assertIsArray($collection->getValue());
    }

    public function testSetMultiDimensionArray()
    {
        $value = [
            ['name' => '1'],
            ['name' => '2']
        ];

        $collection = new CollectionAttribute('test', $value);

        $this->assertIsArray($collection->getValue());
        $this->assertEquals('1', $collection->getValue()[0]->name);
        $this->assertEquals('2', $collection->getValue()[1]->name);
    }

    public function testSetMultiDimensionArrayAttribute()
    {
        $value = [
            ['name' => '1'],
            ['name' => '2']
        ];

        $collection = new CollectionAttribute('test', $value);

        $this->assertIsArray($collection->getValue());
        $this->assertEquals('1', $collection->getValue()[0]->name);
        $this->assertEquals('2', $collection->getValue()[1]->name);
    }

    public function testArrayAttributeResponse()
    {
        $value = [
            ['name' => 'Number 1'],
            ['name' => 'Number 2']
        ];

        $collection = new ArrayAttribute('test', $value);
        ContextService::getSessionContext()->addAttribute($collection);

        $text = resolve(ResponseEngineService::class)->fillAttributes('{session.test[0][name]}');
        $this->assertEquals('Number 1', $text);

        $text = resolve(ResponseEngineService::class)->fillAttributes('{session.test[1][name]}');
        $this->assertEquals('Number 2', $text);
    }

    public function testExampleSearchResults()
    {
        $searchResults = json_decode($this->getSearchResult());
        $collection = new CollectionAttribute('results', $searchResults->results);
        $total = new IntAttribute('total', $searchResults->numberOfResults);
        $current = new IntAttribute('current', 0);

        $searchContext = ContextService::createContext('search');

        $searchContext->addAttribute($collection);
        $searchContext->addAttribute($total);
        $searchContext->addAttribute($current);

        $intent = OutgoingIntent::create(['name' => 'intent.test.search_results']);
        $this->generateSearchResultsMessage(0, 1, $intent);
        $this->generateSearchResultsMessage(1, 2, $intent);

        $responseEngineService = resolve(ResponseEngineService::class);
        $responseEngineService->setOperationService(resolve(OperationServiceInterface::class));

        $messages = $responseEngineService->getMessageForIntent('webchat', 'intent.test.search_results');
        $this->assertEquals($messages->getMessages()[0]->getText(), $searchResults->results[0]->{'dc:title'} . " - " . $searchResults->results[0]->{'OFS:matchingText'});

        $current->setValue(1);
        $searchContext->addAttribute($current);

        $messages = $responseEngineService->getMessageForIntent('webchat', 'intent.test.search_results');
        $this->assertEquals($messages->getMessages()[0]->getText(), $searchResults->results[1]->{'dc:title'} . " - " .  $searchResults->results[1]->{'OFS:matchingText'});
    }

    private function getSearchResult()
    {
        return <<< EOT
{
  "numberOfResults": 12,
  "results": [
    {
      "dc:title": "ATSA_B_A012.doc",
      "OFS:link": "http://10.68.5.212:3000/ES_docs/ES_folder/COIST_scenario/Background/ATSA_B_A012.doc",
      "OFS:matchingText": ". Rocket rails and watering cans were found. 107mm rockets were still in position and appeared to be pointing towards SRANJE..",
      "OFS:repoName": "unknown",
      "OFS:repoIndex": "coist1/doc",
      "dc:date": "2013-06-11T12:32:00Z"
    },
    {
      "dc:title": "ATSA_B_C015.doc",
      "OFS:link": "http://10.68.5.212:3000/ES_docs/ES_folder/COIST_scenario/Background/ATSA_B_C015.doc",
      "OFS:matchingText": "rockets and found some rocket rails combined with a water can with a hole inside in order to initiate the devices.&nbsp;Ptl..",
      "OFS:repoName": "unknown",
      "OFS:repoIndex": "coist1/doc",
      "dc:date": "2013-06-11T15:34:00Z"
    },
    {
      "dc:title": "Dummy Rocket Pod",
      "OFS:link": "http://sps2019/OpTORAL/Forms/DispForm.aspx?ID=1522",
      "OFS:matchingText": "nip data",
      "OFS:repoName": "sharepoint",
      "OFS:repoIndex": "",
      "dc:date": "2019-10-17T13:51:16.0000000Z"
    }
  ]
}
EOT;
    }

    /**
     * @param int $start
     * @param int $end
     * @param OutgoingIntent $outgoingIntent
     */
    private function generateSearchResultsMessage($start, $end, $outgoingIntent)
    {
        $generator = new MessageMarkUpGenerator();

        for ($i=$start;$i<=$end;$i++) {
            $generator->addTextMessage("{search.results[$i][dc:title]} - {search.results[$i][OFS:matchingText]}");
        }

        $conditions = <<< EOT
conditions:
    - condition:
        operation: eq
        attributes:
          attribute1: search.current 
        parameters:
          value: $start
EOT;

        MessageTemplate::create(
            [
                'name' => "$start - $end",
                'outgoing_intent_id' => $outgoingIntent->id,
                'message_markup' => $generator->getMarkUp(),
                'conditions' => $conditions
            ]
        );
    }
}
