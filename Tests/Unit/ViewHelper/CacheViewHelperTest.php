<?php
declare(strict_types=1);

namespace Netlogix\Nxcachetags\Tests\Unit\ViewHelper;

use Netlogix\Nxcachetags\Service\CacheService;
use Netlogix\Nxcachetags\Service\RenderingContextIdentificationService;
use Netlogix\Nxcachetags\ViewHelpers\CacheViewHelper;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

class CacheViewHelperTest extends UnitTestCase
{
    /**
     * @test
     * @return void
     */
    public function itRendersWithArguments(): void
    {
        $cacheServiceMock = $this->getMockBuilder(CacheService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cacheServiceMock->expects($this->once())
            ->method('render');

        $renderingContextIdentificationServiceMock = $this->createMock(RenderingContextIdentificationService::class);

        $cacheViewHelper = new CacheViewHelper();
        $cacheViewHelper->injectCacheService($cacheServiceMock);
        $cacheViewHelper->injectRenderingContextIdentificationService($renderingContextIdentificationServiceMock);
        $cacheViewHelper->initializeArguments();

        $arguments = [
            'identifiedBy' => ["argument1", 'argument2'],
            'lifetime' => 3600,
            'lifetimeSource' => [],
            'taggedBy' => [],
            'includeLanguage' => true,
            'includeUserGroups' => true,
            'includeBackendLogin' => true,
            'includeRootPage' => true,
        ];

        $cacheViewHelper->setArguments($arguments);
        $cacheViewHelper->render();
    }

    /**
     * @test
     * @return void
     */
    public function itIdentifiesRenderingContextIfIdentifiedByIsEmpty(): void
    {
        $cacheServiceMock = $this->getMockBuilder(CacheService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $renderingContextIdentificationServiceMock = $this->createMock(RenderingContextIdentificationService::class);
        $renderingContextIdentificationServiceMock->expects($this->once())
            ->method('identifyRenderingContext');

        $cacheViewHelper = new CacheViewHelper();
        $cacheViewHelper->injectCacheService($cacheServiceMock);
        $cacheViewHelper->injectRenderingContextIdentificationService($renderingContextIdentificationServiceMock);
        $cacheViewHelper->setRenderingContext($this->createMock(RenderingContextInterface::class));
        $cacheViewHelper->initializeArguments();

        $arguments = [
            'identifiedBy' => [""],
            'lifetime' => 3600,
            'lifetimeSource' => [],
            'taggedBy' => [],
            'includeLanguage' => true,
            'includeUserGroups' => true,
            'includeBackendLogin' => true,
            'includeRootPage' => true,
        ];

        $cacheViewHelper->setArguments($arguments);
        $cacheViewHelper->render();
    }
}