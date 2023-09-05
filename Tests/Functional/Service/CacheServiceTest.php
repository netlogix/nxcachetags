<?php

declare(strict_types=1);

namespace Netlogix\Nxcachetags\Tests\Functional\Service;

use Netlogix\Nxcachetags\Service\CacheService;
use TYPO3\CMS\Core\Localization\Parser\XliffParser;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class CacheServiceTest extends FunctionalTestCase
{

    protected array $testExtensionsToLoad = ['typo3conf/ext/nxcachetags'];

    /**
     * @test
     * @return void
     */
    public function itReturnsContentFromClosure() {
        $subject = GeneralUtility::makeInstance(CacheService::class);

        $GLOBALS['TSFE'] = $this->getMockBuilder(TypoScriptFrontendController::class)
            ->disableOriginalConstructor()
            ->getMock();

        $GLOBALS['TSFE']->no_cache = true;

        $content = uniqid('content_');

        $res = $subject->render(function () use ($content): string {return $content;}, [], 0);

        self::assertEquals($content, $res);
    }
}
