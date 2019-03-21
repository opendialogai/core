<?php


namespace OpenDialogAi\Core\Tests\Unit\Graph\DGraph;


use OpenDialogAi\Core\Graph\DGraph\DGraphClient;
use OpenDialogAi\Core\Graph\DGraph\DGraphQuery;
use OpenDialogAi\Core\Tests\TestCase;

class DGraphTest extends TestCase
{
    const DGRAPH_URL = 'http://10.0.2.2';
    const DGRAPH_PORT = '8080';

    public function testDGraph()
    {
        $dGraph = new DGraphClient(self::DGRAPH_URL, self::DGRAPH_PORT);
        $this->assertTrue(true);

        $query = new DGraphQuery();
        $query->allofterms('ei_type', ['Participant'])
            ->setQueryGraph([
                'uid',
                'speaks' => [
                    'uid',
                    'speaks' => [
                        'uid',
                        'ei_type'
                    ]
                ],
            ]);

        $dGraph->query($query);
    }


}