<?php

namespace OpenDialogAi\NlpEngine\Tests;

use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\NlpEngine\NlpLanguage;

class NlpLanguageTest extends TestCase
{
    /** @var NlpLanguage */
    private $nlpLanguage;

    public function setUp(): void
    {
        parent::setUp();
        $this->nlpLanguage = new NlpLanguage();
        $this->nlpLanguage->setInput("Hello world.");
        $this->nlpLanguage->setLanguageName('English');
        $this->nlpLanguage->setIsoName('en');
        $this->nlpLanguage->setScore(1.0);
    }

    public function testItsIntantiable()
    {
        $this->assertEquals($this->nlpLanguage->getInput(), "Hello world.");
        $this->assertEquals($this->nlpLanguage->getLanguageName(), 'English');
        $this->assertEquals($this->nlpLanguage->getIsoName(), 'en');
        $this->assertEquals($this->nlpLanguage->getScore(), 1.0);
    }
}
