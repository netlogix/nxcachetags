<?php

declare(strict_types=1);

namespace Netlogix\Nxcachetags\Tests\Functional\Service;

use Netlogix\Nxcachetags\Service\CacheService;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class CacheServiceTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = ['typo3conf/ext/nxcachetags'];

    /**
     * @test
     * @return void
     */
    public function itReturnsContentFromClosure() {
        $subject = GeneralUtility::makeInstance(ObjectManager::class)->get(CacheService::class);

        $GLOBALS['TSFE'] = $this->getMockBuilder(TypoScriptFrontendController::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['headerNoCache'])
            ->getMock();
        $GLOBALS['TSFE']->method('headerNoCache')->willReturn(true);

        $content = uniqid('content_');

        $res = $subject->render(function () use ($content): string {return $content;}, [], 0);

        self::assertEquals($content, $res);
    }
}
