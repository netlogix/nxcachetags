<?php

declare(strict_types=1);

namespace Netlogix\Nxcachetags\Tests\Unit\Service;

use Netlogix\Nxcachetags\Service\UserToHashBaseService;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class UserToHashBaseServiceTest extends UnitTestCase
{

    protected UserToHashBaseService $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new UserToHashBaseService();
    }

    /**
     * @test
     *
     * @return void
     */
    public function itDoesNotAddHashIfNotEnabled() {
        $tsfeMock = $this->getMockBuilder(TypoScriptFrontendController::class)
            ->disableOriginalConstructor()
            ->getMock();
        $tsfeMock->page['nxcachetags_cacheperuser'] = 0;

        $params = [];
        $this->subject->createHashBase($params, $tsfeMock);

        self::assertEmpty($params);
    }

    /**
     * @test
     *
     * @return void
     */
    public function itAddsDummyUserParamIfEnabledButNoUserIsActive() {
        $tsfeMock = $this->getMockBuilder(TypoScriptFrontendController::class)
            ->disableOriginalConstructor()
            ->getMock();
        $tsfeMock->page['nxcachetags_cacheperuser'] = 1;

        $params = [];
        $this->subject->createHashBase($params, $tsfeMock);

        self::assertNotEmpty($params);
        self::assertTrue(isset($params['hashParameters']['Nxcachetags\\UserToHashBaseService']));
        self::assertEquals(0, $params['hashParameters']['Nxcachetags\\UserToHashBaseService']);
    }

    /**
     * @test
     *
     * @return void
     */
    public function itAddsUserParamIfEnabledAndUserIsActive() {
        $userId = rand(1,999);

        $tsfeMock = $this->getMockBuilder(TypoScriptFrontendController::class)
            ->disableOriginalConstructor()
            ->getMock();
        $tsfeMock->page['nxcachetags_cacheperuser'] = 1;
        $tsfeMock->fe_user = new \stdClass();
        $tsfeMock->fe_user->user['uid'] = $userId;

        $params = [];
        $this->subject->createHashBase($params, $tsfeMock);

        self::assertNotEmpty($params);
        self::assertTrue(isset($params['hashParameters']['Nxcachetags\\UserToHashBaseService']));
        self::assertEquals($userId, $params['hashParameters']['Nxcachetags\\UserToHashBaseService']);
    }
}
