<?php

namespace OpenDialogAi\NlpEngine\Tests;

use OpenDialogAi\Core\NlpEngine\NlpLanguage;
use OpenDialogAi\Core\Tests\TestCase;

/**
 * Class NlpEntitiesTest
 *
 * @package OpenDialogAi\NlpEngine\Tests
 */
class NlpLanguageTest extends TestCase
{
    /** @var NlpLanguage */
    private $nlpLanguage;

    public function setUp(): void
    {
        parent::setUp();
        $this->nlpLanguage = new NlpLanguage();
        $this->nlpLanguage->setLanguageName('English');
        $this->nlpLanguage->setIsoName('en');
        $this->nlpLanguage->setScore(1.0);
    }

    public function testItsIntantiable()
    {
        $this->assertEquals($this->nlpLanguage->getLanguageName(), 'English');
        $this->assertEquals($this->nlpLanguage->getIsoName(), 'en');
        $this->assertEquals($this->nlpLanguage->getScore(), 1.0);
    }
}
