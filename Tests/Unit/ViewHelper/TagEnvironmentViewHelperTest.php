<?php
declare(strict_types=1);

namespace Netlogix\Nxcachetags\Tests\Unit\ViewHelper;

use Netlogix\Nxcachetags\Service\CacheTagService;
use Netlogix\Nxcachetags\Service\MinimalLifetimeService;
use Netlogix\Nxcachetags\ViewHelpers\TagEnvironmentViewHelper;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class TagEnvironmentViewHelperTest extends UnitTestCase
{
    /**
     * @test
     * @return void
     */
    public function itRenders(): void
    {
        $cacheTagServiceMock = $this->getMockBuilder(CacheTagService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $minimalLifetimeServiceMock = $this->getMockBuilder(MinimalLifetimeService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cacheTagServiceMock->expects($this->atLeastOnce())
            ->method('identifyCacheTagForObject')
            ->willReturn(['cacheTag1', 'cacheTag2']);

        $cacheTagServiceMock->expects($this->atLeastOnce())
            ->method('addEnvironmentCacheTags');

        $minimalLifetimeServiceMock->expects($this->atLeastOnce())
            ->method('findMinimalLifetime')
            ->willReturn(300);

        $tagEnvironmentViewHelper = new TagEnvironmentViewHelper();
        $tagEnvironmentViewHelper->injectCacheTagService($cacheTagServiceMock);
        $tagEnvironmentViewHelper->injectMinimalLifetimeService($minimalLifetimeServiceMock);
        $tagEnvironmentViewHelper->initializeArguments();

        $arguments = [
            'objectOrCacheTag' => '',
            'lifetime' => 3600,
            'lifetimeSource' => ['', ''],
        ];

        $tagEnvironmentViewHelper->setArguments($arguments);
        $tagEnvironmentViewHelper->render();
      }

    /**
     * @test
     * @return void
     */
    public function itRendersChildrenIfObjectOrCacheTagIsNull(): void
    {
        $cacheTagServiceMock = $this->getMockBuilder(CacheTagService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $minimalLifetimeServiceMock = $this->getMockBuilder(MinimalLifetimeService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderingContextMock = $this->getMockBuilder(\TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $abstractNodeMock = $this->getMockBuilder(\TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode::class)
            ->disableOriginalConstructor()
            ->getMock();

        //renderChildren() calls evaluateChildNodes() internally
        $abstractNodeMock->expects($this->atLeastOnce())
            ->method("evaluateChildNodes")
            ->with($renderingContextMock);

        $tagEnvironmentViewHelper = new TagEnvironmentViewHelper();
        $tagEnvironmentViewHelper->injectCacheTagService($cacheTagServiceMock);
        $tagEnvironmentViewHelper->injectMinimalLifetimeService($minimalLifetimeServiceMock);
        $tagEnvironmentViewHelper->setViewHelperNode($abstractNodeMock);
        $tagEnvironmentViewHelper->setRenderingContext($renderingContextMock);
        $arguments = [
            'objectOrCacheTag' => null,
            'lifetime' => 3600,
            'lifetimeSource' => ['', ''],
        ];

        $tagEnvironmentViewHelper->setArguments($arguments);
        $tagEnvironmentViewHelper->render();
      }
}