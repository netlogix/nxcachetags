<?php

declare(strict_types=1);

namespace Netlogix\Nxcachetags\Tests\Unit\Hooks;

use Netlogix\Nxcachetags\Hooks\DataHandler;
use Nimut\TestingFramework\TestCase\UnitTestCase;

class DataHandlerTest extends UnitTestCase
{

    /**
     * @test
     *
     * @return void
     */
    public function itDetectsCliRequestAsNotRelevant()
    {
        $subject = $this->getAccessibleMock(DataHandler::class, ['dummy']);
        // this test is a bit wonky but gets us to 100% coverage
        self::assertFalse($subject->_call('isRelevantRequestType'));
    }
}