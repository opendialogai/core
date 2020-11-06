<?php

namespace OpenDialogAi\Core\Tests\Unit;

use OpenDialogAi\Core\Tests\TestCase;

class PackageTest extends TestCase
{
    public function testTest()
    {
        $this->assertEquals('TestDialog', config('opendialog.core.PACKAGE_NAME'));
    }
}
