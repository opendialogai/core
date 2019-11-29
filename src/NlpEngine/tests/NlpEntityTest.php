<?php

namespace OpenDialogAi\NlpEngine\Tests;

use OpenDialogAi\Core\NlpEngine\NlpEntities;
use OpenDialogAi\Core\NlpEngine\NlpEntity;
use OpenDialogAi\Core\NlpEngine\NlpEntityMatch;
use OpenDialogAi\Core\Tests\TestCase;

/**
 * Class NlpEntitiesTest
 *
 * @package OpenDialogAi\NlpEngine\Tests
 */
class NlpEntityTest extends TestCase
{
    private $nlpEntity;
    private $nlpEntities;
    private $nlpEntityMatch1;
    private $nlpEntityMatch2;

    public function setUp(): void
    {
        parent::setUp();
        $this->nlpEntity = new NlpEntity();
        $this->nlpEntity->setInput('I want to find books on travel by david attenborough');
        $this->nlpEntity->setName('david attenborough');
        $this->nlpEntity->setType('Person');

        $this->nlpEntityMatch1 = new NlpEntityMatch();
        $this->nlpEntityMatch1->setEntityTypeScore(0.7087167501449585);
        $this->nlpEntityMatch1->setText('david attenborough');
        $this->nlpEntityMatch1->setWikipediaScore(0.7087167501449585);

        $this->nlpEntityMatch2 = new NlpEntityMatch();
        $this->nlpEntityMatch2->setEntityTypeScore(0.7087167501449585);
        $this->nlpEntityMatch2->setText('david attenborough');
        $this->nlpEntityMatch2->setWikipediaScore(0.7087167501449585);

        $this->nlpEntity->addMatch($this->nlpEntityMatch1);
        $this->nlpEntity->addMatch($this->nlpEntityMatch2);

        $this->nlpEntities = new NlpEntities();
        $this->nlpEntities->setInput('I want to find books on travel by david attenborough');
        $this->nlpEntities->addEntities($this->nlpEntity);
    }

    public function testItsIntantiable()
    {
        $this->assertEquals($this->nlpEntity->getInput(), "I want to find books on travel by david attenborough");
        $this->assertEquals($this->nlpEntity->getName(), "david attenborough");
        $this->assertIsArray($this->nlpEntity->getMatches());
        $this->assertIsArray($this->nlpEntities->getEntities());
    }
}
